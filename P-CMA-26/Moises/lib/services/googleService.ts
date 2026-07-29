import { Business } from './types';

export async function getGoogleBusinesses(search?: string, category?: string): Promise<Business[]> {
  const apiKey = process.env.GOOGLE_PLACES_API_KEY;

  if (!apiKey) {
    console.error('[Google Service] GOOGLE_PLACES_API_KEY no está configurada.');
    return [];
  }

  console.log('[Google Service] Consultando Google Places API (New)');

  try {
    let queries = [];

    if (category && search) {
      queries = [`${category} ${search} en Villahermosa, Tabasco`];
    } else if (category) {
      queries = [`${category} en Villahermosa, Tabasco`];
    } else if (search) {
      queries = [`${search} en Villahermosa, Tabasco`];
    } else {
      // Default: fetch a mix of categories
      queries = [
        'restaurantes en Villahermosa, Tabasco',
        'hoteles en Villahermosa, Tabasco',
        'plazas comerciales en Villahermosa, Tabasco',
      ];
    }

    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'X-Goog-Api-Key': apiKey,
      'X-Goog-FieldMask': 'places.id,places.displayName,places.formattedAddress,places.primaryTypeDisplayName,places.types,places.photos,places.internationalPhoneNumber,places.rating,places.googleMapsUri',
      'Accept-Language': 'es',
    };

    const mapPlace = (place: any) => {
      let imageUrl = '';
      if (place.photos && place.photos.length > 0) {
        const photoName = place.photos[0].name;
        imageUrl = `/api/photo?name=${encodeURIComponent(photoName)}`;
      }
      const categoryLabel =
        place.primaryTypeDisplayName?.text ||
        (place.types && place.types.length > 0 ? place.types[0].replace(/_/g, ' ') : 'Negocio');
      return {
        id: `google-${place.id}`,
        name: place.displayName?.text || 'Sin nombre',
        description: place.types ? place.types.slice(0, 3).join(', ').replace(/_/g, ' ') : 'Lugar local',
        category: category || categoryLabel,
        address: place.formattedAddress || 'Villahermosa, Tabasco',
        phone: place.internationalPhoneNumber || 'No disponible',
        imageUrl,
        mapsUrl: place.googleMapsUri || `https://www.google.com/maps/place/?q=place_id:${place.id}`,
        source: 'Google',
      };
    };

    const fetchQuery = async (textQuery: string, limit: number) => {
      const response = await fetch('https://places.googleapis.com/v1/places:searchText', {
        method: 'POST',
        headers,
        body: JSON.stringify({
          textQuery,
          languageCode: 'es',
          maxResultCount: limit,
          locationBias: {
            circle: {
              center: { latitude: 17.9869, longitude: -92.9303 },
              radius: 15000.0,
            },
          },
        }),
      });

      if (!response.ok) return [];
      const data = await response.json();
      return data.places || [];
    };

    let allPlaces: any[] = [];

    if (queries.length === 1) {
      // Single specific query, use pagination to get up to 40
      const query = queries[0];
      const requestBody = {
        textQuery: query,
        languageCode: 'es',
        maxResultCount: 20,
        locationBias: {
          circle: {
            center: { latitude: 17.9869, longitude: -92.9303 },
            radius: 15000.0,
          },
        },
      };

      const response1 = await fetch('https://places.googleapis.com/v1/places:searchText', {
        method: 'POST',
        headers: { ...headers, 'X-Goog-FieldMask': headers['X-Goog-FieldMask'] + ',nextPageToken' },
        body: JSON.stringify(requestBody),
      });

      if (response1.ok) {
        const data1 = await response1.json();
        if (data1.places) allPlaces = [...data1.places];

        if (data1.nextPageToken) {
          const response2 = await fetch('https://places.googleapis.com/v1/places:searchText', {
            method: 'POST',
            headers: { ...headers, 'X-Goog-FieldMask': headers['X-Goog-FieldMask'] + ',nextPageToken' },
            body: JSON.stringify({ ...requestBody, pageToken: data1.nextPageToken }),
          });
          if (response2.ok) {
            const data2 = await response2.json();
            if (data2.places) allPlaces = [...allPlaces, ...data2.places];
          }
        }
      }
    } else {
      // Multiple queries (default mix)
      const limitPerQuery = 14; // 14 * 3 = 42 results
      const results = await Promise.all(queries.map(q => fetchQuery(q, limitPerQuery)));
      allPlaces = results.flat();
      
      // Shuffle the results so it's a good mix
      allPlaces = allPlaces.sort(() => Math.random() - 0.5);
    }

    const withPhotos = allPlaces.filter(p => p.photos && p.photos.length > 0);
    if (withPhotos.length > 0) {
      console.log('[Google Debug] Ejemplo con fotos:', JSON.stringify(withPhotos[0].photos, null, 2));
    } else {
      console.log('[Google Debug] NINGUN lugar tiene fotos en la respuesta');
    }

    console.log(`[Google Service] ✅ ${allPlaces.length} lugares encontrados en Villahermosa`);

    return allPlaces.map(mapPlace);
  } catch (error) {
    console.error('[Google Service] Excepción al consultar Google:', error);
    return [];
  }
}

