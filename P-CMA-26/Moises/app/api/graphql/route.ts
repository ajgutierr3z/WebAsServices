import { createSchema, createYoga } from 'graphql-yoga';
import { getGoogleBusinesses } from '@/lib/services/googleService';
import { githubService } from '@/lib/services/githubService';

const typeDefs = /* GraphQL */ `
  type Business {
    id: ID!
    name: String!
    description: String
    category: String!
    address: String!
    phone: String!
    imageUrl: String
    mapsUrl: String
    source: String!
  }

  input BusinessInput {
    name: String!
    description: String
    category: String!
    address: String!
    phone: String!
    imageUrl: String
  }

  type Query {
    businesses(search: String, category: String, source: String): [Business!]!
    business(id: ID!): Business
  }

  type Mutation {
    createBusiness(input: BusinessInput!): Business!
    updateBusiness(id: ID!, input: BusinessInput!): Business!
    deleteBusiness(id: ID!): ID!
  }
`;

const resolvers = {
  Query: {
    businesses: async (_: any, args: { search?: string; category?: string; source?: string }) => {
      let results: any[] = [];
      
      // Fetch from Google
      if (!args.source || args.source === 'Google') {
        const googleData = await getGoogleBusinesses(args.search, args.category);
        results = [...results, ...googleData];
      }
      
      // Fetch from GitHub/Local
      if (!args.source || args.source === 'Local') {
        const localData = await githubService.getAll();
        
        // Apply filters
        const filteredLocal = localData.filter(b => {
          let match = true;
          if (args.search) {
            match = match && b.name.toLowerCase().includes(args.search.toLowerCase());
          }
          if (args.category) {
            match = match && b.category.toLowerCase().includes(args.category.toLowerCase());
          }
          return match;
        });
        
        results = [...results, ...filteredLocal];
      }
      
      return results;
    },
    business: async (_: any, { id }: { id: string }) => {
      if (id.startsWith('google-')) {
        const googleData = await getGoogleBusinesses();
        return googleData.find(b => b.id === id);
      } else {
        const localData = await githubService.getAll();
        return localData.find(b => b.id === id);
      }
    }
  },
  Mutation: {
    createBusiness: async (_: any, { input }: any) => {
      return await githubService.create(input);
    },
    updateBusiness: async (_: any, { id, input }: any) => {
      return await githubService.update(id, input);
    },
    deleteBusiness: async (_: any, { id }: any) => {
      return await githubService.delete(id);
    }
  }
};

import { NextRequest } from 'next/server';

const { handleRequest } = createYoga({
  schema: createSchema({
    typeDefs,
    resolvers,
  }),
  // We need to specify the GraphQL endpoint so Yoga knows where it's mounted
  graphqlEndpoint: '/api/graphql',
  // Yoga needs to know we're in a Fetch API environment
  fetchAPI: { Response }
});

export async function GET(request: NextRequest, context: { params: Promise<any> }) {
  return handleRequest(request, context as any);
}

export async function POST(request: NextRequest, context: { params: Promise<any> }) {
  return handleRequest(request, context as any);
}

export async function OPTIONS(request: NextRequest, context: { params: Promise<any> }) {
  return handleRequest(request, context as any);
}
