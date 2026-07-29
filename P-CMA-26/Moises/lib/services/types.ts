export interface Business {
  id: string;
  name: string;
  description?: string;
  category: string;
  address: string;
  phone: string;
  imageUrl?: string;
  mapsUrl?: string;
  source: 'Google' | 'Local';
}

export type BusinessInput = Omit<Business, 'id' | 'source'>;
