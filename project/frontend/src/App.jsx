import { useState, useEffect } from 'react'
import ArticleList from './components/ArticleList'
import SearchBar from './components/SearchBar'
import StatsPanel from './components/StatsPanel'
import ImageUpload from './components/ImageUpload'
import { getStats } from './services/api'

function App() {
  const [stats, setStats] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');

  useEffect(() => {
    fetchStats();

    const interval = setInterval(() => {
      fetchStats();
    }, 5000);
  }, []);

  const fetchStats = async () => {
    try {
      const response = await getStats();
      setStats(response.data);
    } catch (error) {
      console.error('Failed to fetch stats:', error);
    }
  };

  return (
    <div className="container">
      <header style={{ marginBottom: '2rem' }}>
        <h1>📝 Blog Platform</h1>
        <p style={{ color: '#7f8c8d' }}>
          Plateforme de gestion de blog - Challenge technique
        </p>
      </header>

      {stats && <StatsPanel stats={stats} />}

      <div style={{ marginTop: '2rem', marginBottom: '2rem' }}>
        <SearchBar onSearch={setSearchQuery} />
      </div>

      <div style={{ marginBottom: '2rem' }}>
        <ImageUpload />
      </div>

      <ArticleList searchQuery={searchQuery} />
    </div>
  )
}

export default App

