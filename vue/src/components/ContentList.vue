<template>
    <div class="content-list">
      <div v-if="loading" class="loading-message">Cargando contenido...</div>
      <div v-else-if="errorMessage" class="error-message">{{ errorMessage }}</div>
      <div v-else-if="items.length === 0" class="no-content-placeholder">
        <p>No hay contenido disponible todavía.</p>
      </div>
      <div v-else class="items-grid">
        <div v-for="item in items" :key="item.id + '-' + item.type" class="content-card" @click="$emit('item-click', item)" :title="getContentTitle(item)">
          <div v-if="item.type === 'guide'" class="guide-card">
            <div class="guide-card-image">
              <img v-if="item.image" :src="item.image" alt="Portada de la guía" @error="handleImageError" />
              <div v-else class="no-image-placeholder">Sin imagen</div>
            </div>
            <div class="guide-card-content">
              <h3 class="guide-title">{{ item.title || 'Sin título' }}</h3>
              <p class="guide-description">{{ item.description || 'Sin descripción' }}</p>
            </div>
          </div>
          <div v-else-if="item.type === 'screenshot'" class="screenshot-card">
            <div class="screenshot-image">
              <img :src="item.image_url" alt="Captura del juego" @error="handleImageError" />
            </div>
            <div class="screenshot-meta">
              <span>{{ item.title || 'Sin título' }}</span>
            </div>
          </div>
          <div v-else-if="item.type === 'video'" class="video-card">
            <div class="video-thumbnail">
              <img :src="item.thumbnail_url || 'https://via.placeholder.com/280x150?text=Sin+miniatura'" alt="Miniatura del video" @error="handleImageError" />
              <div class="play-icon">▶</div>
            </div>
            <div class="video-meta">
              <span>{{ item.title || 'Sin título' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  export default {
    name: 'ContentList',
    props: {
      items: Array,
      loading: Boolean,
      errorMessage: String,
    },
    emits: ['item-click'],
    methods: {
      getContentTitle(item) {
        if (item.type === 'guide') return 'Guía: Información detallada creada por la comunidad';
        if (item.type === 'screenshot') return 'Captura: Imagen subida por un usuario';
        if (item.type === 'video') return 'Video: Video subido por un usuario';
        return '';
      },
      handleImageError(event) {
        event.target.style.display = 'none';
        const placeholder = document.createElement('div');
        placeholder.className = 'no-image-placeholder';
        placeholder.textContent = 'Sin imagen';
        event.target.parentNode.appendChild(placeholder);
      },
    },
  };
  </script>

  <style scoped>
  .content-list {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
  }

  .items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
  }

  .content-card {
    cursor: pointer;
    border: 2px solid #00ffcc;
    border-radius: 6px;
    overflow: hidden;
    transition: transform 0.3s;
  }

  .content-card:hover {
    transform: scale(1.05);
  }

  .guide-card, .screenshot-card, .video-card {
    display: flex;
    flex-direction: column;
  }

  .guide-card-image, .screenshot-image, .video-thumbnail {
    height: 150px;
    overflow: hidden;
  }

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .no-image-placeholder {
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #2a2a2a;
    color: #ffffff;
  }

  .guide-card-content, .screenshot-meta, .video-meta {
    padding: 10px;
    background: #1a1a1a;
    color: #ffffff;
  }

  .guide-title {
    font-size: 1.1em;
    margin: 0;
  }

  .guide-description {
    font-size: 0.9em;
    margin: 5px 0 0;
  }

  .play-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 2em;
    color: #ffcc00;
  }
  </style>