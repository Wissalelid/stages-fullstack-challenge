function StatsPanel({ stats }) {
  if (!stats) return null;

  return (
    <div className="card" style={{ backgroundColor: '#ecf0f1' }}>
      <h3>📊 Statistiques du blog</h3>
      <div style={{ 
        display: 'grid', 
        gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))',
        gap: '1rem',
        marginTop: '1rem'
      }}>
        <div style={{ textAlign: 'center' }}>
          <div style={{ fontSize: '2em', fontWeight: 'bold', color: '#3498db' }}>
            {stats.total_articles}
          </div>
          <div style={{ color: '#7f8c8d', fontSize: '0.9em' }}>Articles</div>
        </div>
        
        <div style={{ textAlign: 'center' }}>
          <div style={{ fontSize: '2em', fontWeight: 'bold', color: '#27ae60' }}>
            {stats.total_comments}
          </div>
          <div style={{ color: '#7f8c8d', fontSize: '0.9em' }}>Commentaires</div>
        </div>
        
        <div style={{ textAlign: 'center' }}>
          <div style={{ fontSize: '2em', fontWeight: 'bold', color: '#9b59b6' }}>
            {stats.total_users}
          </div>
          <div style={{ color: '#7f8c8d', fontSize: '0.9em' }}>Utilisateurs</div>
        </div>
      </div>

      <div style={{ 
        marginTop: '1rem', 
        fontSize: '0.85em', 
        color: '#7f8c8d',
        fontStyle: 'italic'
      }}>
        ℹ️ Stats mises à jour toutes les 5 secondes
      </div>
    </div>
  );
}

export default StatsPanel;

