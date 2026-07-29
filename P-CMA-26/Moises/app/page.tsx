'use client';
//hola

import { useState, useEffect } from 'react';
import { Business, BusinessInput } from '@/lib/services/types';

export default function Home() {
  const [allBusinesses, setAllBusinesses] = useState<Business[]>([]);
  const [filteredBusinesses, setFilteredBusinesses] = useState<Business[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [category, setCategory] = useState('');
  
  // Modal State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [formData, setFormData] = useState<BusinessInput>({
    name: '',
    description: '',
    category: '',
    address: '',
    phone: '',
    imageUrl: ''
  });

  const fetchBusinesses = async () => {
    setLoading(true);
    try {
      const query = `
        query GetBusinesses {
          businesses {
            id
            name
            description
            category
            address
            phone
            imageUrl
            mapsUrl
            source
          }
        }
      `;
      
      const res = await fetch('/api/graphql', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ query })
      });
      
      const { data } = await res.json();
      if (data?.businesses) {
        setAllBusinesses(data.businesses);
      }
    } catch (error) {
      console.error('Error fetching businesses:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchBusinesses();
  }, []);

  useEffect(() => {
    const filtered = allBusinesses.filter(b => {
      const matchesSearch = b.name.toLowerCase().includes(search.toLowerCase()) || 
                            (b.description?.toLowerCase().includes(search.toLowerCase()) || false);
      const matchesCategory = category ? b.category === category : true;
      return matchesSearch && matchesCategory;
    });
    setFilteredBusinesses(filtered);
  }, [search, category, allBusinesses]);

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      if (editingId) {
        // Update
        const query = `
          mutation UpdateBusiness($id: ID!, $input: BusinessInput!) {
            updateBusiness(id: $id, input: $input) { id }
          }
        `;
        await fetch('/api/graphql', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ query, variables: { id: editingId, input: formData } })
        });
      } else {
        // Create
        const query = `
          mutation CreateBusiness($input: BusinessInput!) {
            createBusiness(input: $input) { id }
          }
        `;
        await fetch('/api/graphql', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ query, variables: { input: formData } })
        });
      }
      setIsModalOpen(false);
      resetForm();
      fetchBusinesses();
    } catch (error) {
      console.error('Error saving business:', error);
      alert('Hubo un error al guardar el negocio.');
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm('¿Estás seguro de que deseas eliminar este negocio?')) return;
    try {
      const query = `
        mutation DeleteBusiness($id: ID!) {
          deleteBusiness(id: $id)
        }
      `;
      await fetch('/api/graphql', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ query, variables: { id } })
      });
      fetchBusinesses();
    } catch (error) {
      console.error('Error deleting business:', error);
    }
  };

  const openEditModal = (b: Business) => {
    setFormData({
      name: b.name,
      description: b.description || '',
      category: b.category,
      address: b.address,
      phone: b.phone,
      imageUrl: b.imageUrl || ''
    });
    setEditingId(b.id);
    setIsModalOpen(true);
  };

  const resetForm = () => {
    setFormData({ name: '', description: '', category: '', address: '', phone: '', imageUrl: '' });
    setEditingId(null);
  };

  return (
    <main className="container">
      <header className="header">
        <div>
          <h1 className="title">Directorio Villahermosa</h1>
          <p>Explora negocios locales y añade los tuyos (Web as Services)</p>
        </div>
        <button 
          className="btn"
          onClick={() => { resetForm(); setIsModalOpen(true); }}
        >
          + Nuevo Negocio
        </button>
      </header>

      <div className="filters">
        <input 
          type="text" 
          placeholder="Buscar negocios..." 
          className="input search-bar"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
        />
        <select 
          className="input"
          value={category}
          onChange={(e) => setCategory(e.target.value)}
        >
          <option value="">Todas las Categorías</option>
          {Array.from(new Set(allBusinesses.map(b => b.category))).sort().map(cat => (
            <option key={cat} value={cat}>{cat}</option>
          ))}
        </select>
      </div>

      {loading ? (
        <div className="loading">Cargando negocios...</div>
      ) : (
        <div className="grid">
          {filteredBusinesses.map((b) => (
            <div key={b.id} className="card" style={{ position: 'relative' }}>
              {b.source === 'Google' && b.mapsUrl && (
                <a
                  href={b.mapsUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  aria-label={`Ver ${b.name} en Google Maps`}
                  style={{
                    position: 'absolute', inset: 0, zIndex: 1,
                    borderRadius: '1rem',
                  }}
                />
              )}
              {b.imageUrl ? (
                <img src={b.imageUrl} alt={b.name} className="card-img" />
              ) : (
                <div className="card-img" style={{ background: 'linear-gradient(45deg, var(--primary), var(--secondary))' }} />
              )}
              
              <div className="badges">
                <span className={`badge ${b.source === 'Google' ? 'google' : 'local'}`}>
                  {b.source === 'Google' ? 'Público (Google)' : 'Local (GitHub)'}
                </span>
                <span className="badge">{b.category}</span>
              </div>

              <h3>{b.name}</h3>
              {b.description && <p style={{ fontSize: '0.9rem', color: '#cbd5e1' }}>{b.description}</p>}
              
              <div style={{ fontSize: '0.85rem', color: '#94a3b8', marginTop: 'auto' }}>
                <p>📍 {b.address}</p>
                <p>📞 {b.phone}</p>
              </div>

              {b.source === 'Local' && (
                <div style={{ display: 'flex', gap: '0.5rem', marginTop: '1rem' }}>
                  <button className="btn secondary" style={{ flex: 1 }} onClick={() => openEditModal(b)}>Editar</button>
                  <button className="btn danger" style={{ flex: 1 }} onClick={() => handleDelete(b.id)}>Eliminar</button>
                </div>
              )}
            </div>
          ))}
          {filteredBusinesses.length === 0 && (
            <div style={{ gridColumn: '1 / -1', textAlign: 'center', padding: '3rem', color: '#94a3b8' }}>
              No se encontraron negocios. ¡Agrega uno nuevo!
            </div>
          )}
        </div>
      )}

      {isModalOpen && (
        <div className="modal-overlay">
          <div className="modal">
            <div className="modal-header">
              <h2>{editingId ? 'Editar Negocio' : 'Nuevo Negocio Local'}</h2>
              <button 
                onClick={() => setIsModalOpen(false)}
                style={{ background: 'transparent', border: 'none', color: 'white', cursor: 'pointer', fontSize: '1.5rem' }}
              >
                &times;
              </button>
            </div>
            <form onSubmit={handleSave}>
              <div className="input-group">
                <label>Nombre del Negocio</label>
                <input required className="input" value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} />
              </div>
              <div className="input-group">
                <label>Descripción</label>
                <textarea className="input" value={formData.description} onChange={e => setFormData({...formData, description: e.target.value})} />
              </div>
              <div className="input-group">
                <label>Categoría</label>
                <input required className="input" placeholder="Ej. Restaurante, Spa..." value={formData.category} onChange={e => setFormData({...formData, category: e.target.value})} />
              </div>
              <div className="input-group">
                <label>Dirección</label>
                <input required className="input" value={formData.address} onChange={e => setFormData({...formData, address: e.target.value})} />
              </div>
              <div className="input-group">
                <label>Teléfono</label>
                <input required className="input" value={formData.phone} onChange={e => setFormData({...formData, phone: e.target.value})} />
              </div>
              <div className="input-group">
                <label>URL de Imagen (Opcional)</label>
                <input type="url" className="input" placeholder="https://..." value={formData.imageUrl} onChange={e => setFormData({...formData, imageUrl: e.target.value})} />
              </div>
              <div style={{ display: 'flex', gap: '1rem', marginTop: '2rem' }}>
                <button type="button" className="btn secondary" style={{ flex: 1 }} onClick={() => setIsModalOpen(false)}>Cancelar</button>
                <button type="submit" className="btn" style={{ flex: 1 }}>{editingId ? 'Actualizar' : 'Guardar'}</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </main>
  );
}
