import { useState } from 'react';

function SearchBar({ onSearch }) {
  const [query, setQuery] = useState('');

  const handleSubmit = (e) => {
    e.preventDefault();
    onSearch(query);
  };

  const handleClear = () => {
    setQuery('');
    onSearch('');
  };

  return (
    <div className="card">
      <h3>🔍 Rechercher des articles</h3>
      <form onSubmit={handleSubmit} style={{ display: 'flex', gap: '0.5rem', marginTop: '1rem' }}>
        <input
          type="text"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="Rechercher par titre ou contenu..."
          style={{ flex: 1 }}
        />
        <button type="submit">
          Rechercher
        </button>
        {query && (
          <button 
            type="button" 
            onClick={handleClear}
            style={{ backgroundColor: '#95a5a6' }}
          >
            Effacer
          </button>
        )}
      </form>
      <div style={{ marginTop: '0.8rem', fontSize: '0.9em', color: '#7f8c8d' }}>
        💡 Astuce : Essayez de rechercher "café", "été", "élève" pour tester la recherche avec accents
      </div>
    </div>
  );
}

export default SearchBar;

