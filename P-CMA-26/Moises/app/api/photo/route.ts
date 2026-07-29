import { NextRequest, NextResponse } from 'next/server';

export async function GET(request: NextRequest) {
  const { searchParams } = new URL(request.url);
  const photoName = searchParams.get('name'); // e.g. "places/xxx/photos/yyy"

  if (!photoName) {
    return new NextResponse('Missing photo name', { status: 400 });
  }

  const apiKey = process.env.GOOGLE_PLACES_API_KEY;
  if (!apiKey) {
    return new NextResponse('API key not configured', { status: 500 });
  }

  const googleUrl = `https://places.googleapis.com/v1/${photoName}/media?maxHeightPx=500&maxWidthPx=500&key=${apiKey}&skipHttpRedirect=true`;

  try {
    const response = await fetch(googleUrl, {
      headers: { 'Accept': 'image/*' },
    });

    if (!response.ok) {
      return new NextResponse('Failed to fetch photo', { status: response.status });
    }

    const data = await response.json();
    const photoUri = data.photoUri;

    if (!photoUri) {
      return new NextResponse('No photo URI returned', { status: 404 });
    }

    // Fetch the actual image
    const imageResponse = await fetch(photoUri);
    if (!imageResponse.ok) {
      return new NextResponse('Failed to fetch image', { status: imageResponse.status });
    }

    const imageBuffer = await imageResponse.arrayBuffer();
    const contentType = imageResponse.headers.get('content-type') || 'image/jpeg';

    return new NextResponse(imageBuffer, {
      headers: {
        'Content-Type': contentType,
        'Cache-Control': 'public, max-age=86400', // Cache for 24h
      },
    });
  } catch (error) {
    console.error('[Photo Proxy] Error:', error);
    return new NextResponse('Internal error', { status: 500 });
  }
}
