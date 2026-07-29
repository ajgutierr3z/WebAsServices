import { Business, BusinessInput } from './types';
import fs from 'fs';
import path from 'path';
import { v4 as uuidv4 } from 'uuid';

const DATA_FILE_PATH = path.join(process.cwd(), 'data', 'negocios.json');

// Ensure the data directory exists
function ensureDataFile() {
  const dir = path.dirname(DATA_FILE_PATH);
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }
  if (!fs.existsSync(DATA_FILE_PATH)) {
    fs.writeFileSync(DATA_FILE_PATH, JSON.stringify([]));
  }
}

async function getLocalData(): Promise<Business[]> {
  ensureDataFile();
  const raw = fs.readFileSync(DATA_FILE_PATH, 'utf-8');
  return JSON.parse(raw);
}

async function saveLocalData(data: Business[]): Promise<void> {
  ensureDataFile();
  fs.writeFileSync(DATA_FILE_PATH, JSON.stringify(data, null, 2));
}

// GitHub implementation using REST API (fetch)
async function getGithubData(token: string, owner: string, repo: string): Promise<Business[]> {
  const url = `https://api.github.com/repos/${owner}/${repo}/contents/data/negocios.json`;
  try {
    const res = await fetch(url, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/vnd.github.v3+json'
      },
      // Avoid caching to get the latest commit data
      cache: 'no-store'
    });

    if (res.status === 404) {
      return []; // File doesn't exist yet
    }

    if (!res.ok) {
      console.warn('[GitHub Service] Error obteniendo datos', res.statusText);
      return [];
    }

    const json = await res.json();
    const content = Buffer.from(json.content, 'base64').toString('utf-8');
    return JSON.parse(content);
  } catch (e) {
    console.error('[GitHub Service] Fetch error:', e);
    return [];
  }
}

async function saveGithubData(token: string, owner: string, repo: string, data: Business[]): Promise<void> {
  const url = `https://api.github.com/repos/${owner}/${repo}/contents/data/negocios.json`;
  
  // Need to get the current file's SHA to update it
  let sha = undefined;
  try {
    const getRes = await fetch(url, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/vnd.github.v3+json'
      }
    });
    if (getRes.ok) {
      const getJson = await getRes.json();
      sha = getJson.sha;
    }
  } catch (e) {
    console.warn('[GitHub Service] Could not fetch SHA, assuming new file.');
  }

  const content = Buffer.from(JSON.stringify(data, null, 2)).toString('base64');
  
  const body = {
    message: 'Update negocios.json from Villahermosa Business Directory',
    content,
    ...(sha && { sha })
  };

  const res = await fetch(url, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/vnd.github.v3+json',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(body)
  });

  if (!res.ok) {
    throw new Error(`[GitHub Service] Failed to save data: ${res.statusText}`);
  }
}

class GithubBusinessService {
  private useGithub: boolean;
  private token: string;
  private owner: string;
  private repo: string;

  constructor() {
    this.token = process.env.GITHUB_TOKEN || '';
    this.owner = process.env.GITHUB_OWNER || '';
    this.repo = process.env.GITHUB_REPO || '';
    
    // Only use GitHub if all variables are set
    this.useGithub = Boolean(this.token && this.owner && this.repo);
  }

  async getAll(): Promise<Business[]> {
    if (this.useGithub) {
      console.log('[GitHub Service] Fetching from GitHub Repo');
      return getGithubData(this.token, this.owner, this.repo);
    } else {
      console.log('[GitHub Service] Fetching from Local FS');
      return getLocalData();
    }
  }

  async saveAll(businesses: Business[]): Promise<void> {
    if (this.useGithub) {
      console.log('[GitHub Service] Saving to GitHub Repo');
      return saveGithubData(this.token, this.owner, this.repo, businesses);
    } else {
      console.log('[GitHub Service] Saving to Local FS');
      return saveLocalData(businesses);
    }
  }

  async create(input: BusinessInput): Promise<Business> {
    const businesses = await this.getAll();
    const newBusiness: Business = {
      ...input,
      id: `local-${uuidv4()}`,
      source: 'Local'
    };
    businesses.push(newBusiness);
    await this.saveAll(businesses);
    return newBusiness;
  }

  async update(id: string, input: BusinessInput): Promise<Business> {
    const businesses = await this.getAll();
    const index = businesses.findIndex(b => b.id === id);
    if (index === -1) throw new Error('Business not found');
    
    businesses[index] = {
      ...businesses[index],
      ...input
    };
    
    await this.saveAll(businesses);
    return businesses[index];
  }

  async delete(id: string): Promise<string> {
    const businesses = await this.getAll();
    const filtered = businesses.filter(b => b.id !== id);
    if (filtered.length === businesses.length) {
      throw new Error('Business not found');
    }
    await this.saveAll(filtered);
    return id;
  }
}

export const githubService = new GithubBusinessService();
