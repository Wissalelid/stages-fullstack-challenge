import { useState } from 'react';
import { uploadImage } from '../services/api';

function ImageUpload() {
  const [selectedFile, setSelectedFile] = useState(null);
  const [uploading, setUploading] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [uploadedImage, setUploadedImage] = useState(null);

  // Limite max côté frontend (10MB)
  const MAX_FILE_SIZE_MB = 10;

  const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const sizeMB = file.size / (1024 * 1024);

    if (sizeMB > MAX_FILE_SIZE_MB) {
      setError(`❌ Fichier trop volumineux ! Maximum autorisé : ${MAX_FILE_SIZE_MB}MB`);
      setSelectedFile(null);
      setMessage('');
      return;
    }

    setSelectedFile(file);
    setMessage(`Fichier sélectionné : ${file.name} (${sizeMB.toFixed(2)} MB)`);
    setError('');
  };

  const handleUpload = async () => {
    if (!selectedFile) {
      setError('Veuillez sélectionner une image');
      return;
    }

    setUploading(true);
    setError('');
    setMessage('');

    const formData = new FormData();
    formData.append('image', selectedFile);

    try {
      const response = await uploadImage(formData);
      setMessage(`✅ Image uploadée avec succès ! (${(response.data.size / 1024).toFixed(0)} KB)`);
      setUploadedImage(response.data);
      setSelectedFile(null);
    } catch (err) {
      if (err.response?.status === 413) {
        setError(`❌ Erreur 413 : Image trop volumineuse ! La limite côté serveur est dépassée`);
      } else {
        setError(`❌ Erreur lors de l'upload : ${err.message}`);
      }
      console.error('Upload error:', err);
    } finally {
      setUploading(false);
    }
  };

  return (
    <div className="card">
      <h3>📸 Upload d'Image</h3>
      <p style={{ color: '#7f8c8d', fontSize: '0.9em', marginBottom: '1rem' }}>
        Testez l'upload d'images (limite : {MAX_FILE_SIZE_MB}MB)
      </p>

      <input type="file" accept="image/*" onChange={handleFileSelect} style={{ marginBottom: '1rem' }} />

      {message && !error && (
        <div style={{ padding: '0.8rem', backgroundColor: '#d4edda', color: '#155724', borderRadius: '4px', marginBottom: '1rem', fontSize: '0.9em' }}>
          {message}
        </div>
      )}

      {error && (
        <div className="error" style={{ marginBottom: '1rem', fontSize: '0.9em', color: '#721c24', backgroundColor: '#f8d7da', padding: '0.8rem', borderRadius: '4px' }}>
          {error}
        </div>
      )}

      {uploadedImage && (
        <div style={{ padding: '0.8rem', backgroundColor: '#f8f9fa', borderRadius: '4px', marginBottom: '1rem', fontSize: '0.85em' }}>
          <strong>Détails :</strong>
          <div>Path: {uploadedImage.path}</div>
          <div>Size: {(uploadedImage.size / 1024).toFixed(2)} KB</div>
        </div>
      )}

      <button onClick={handleUpload} disabled={!selectedFile || uploading} style={{ marginRight: '0.5rem' }}>
        {uploading ? '⏳ Upload en cours...' : '📤 Uploader'}
      </button>

      {selectedFile && (
        <button onClick={() => { setSelectedFile(null); setMessage(''); setError(''); }} style={{ backgroundColor: '#95a5a6' }}>
          Annuler
        </button>
      )}
    </div>
  );
}

export default ImageUpload;
