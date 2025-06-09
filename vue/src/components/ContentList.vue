<template>
    <div class="content-list">
        <div v-if="loading" class="loading-message">Cargando contenido...</div>
        <div v-else-if="errorMessage" class="error-message">{{ errorMessage }}</div>
        <div v-else-if="items.length === 0" class="no-content-placeholder">
            No hay contenido disponible.
        </div>
        <div v-else class="items-grid">
            <div v-for="item in items" :key="item.id + '-' + item.type" class="content-card" @click="$emit('item-click', item)" :title="getContentTitle(item)">
                <div v-if="item.type === 'guide'" class="guide-card">
                    <div class="guide-card-image">
                        <img :src="item.image || '/images/placeholder.jpg'" alt="Portada de la guía" @error="handleImageError" />
                    </div>
                    <div class="guide-card-content">
                        <h3 class="guide-title">{{ item.title || 'Sin título' }}</h3>
                        <p class="guide-description">{{ item.description || 'Sin descripción' }}</p>
                    </div>
                </div>
                <div v-else-if="item.type === 'screenshot'" class="screenshot-card">
                    <div class="screenshot-image">
                        <img :src="item.image_url || '/images/placeholder.jpg'" alt="Captura del juego" @error="handleImageError" />
                    </div>
                    <div class="screenshot-meta">
                        <span>{{ item.title || 'Sin título' }}</span>
                    </div>
                </div>
                <div v-else-if="item.type === 'video'" class="video-card">
                    <div class="video-thumbnail">
                        <img :src="item.thumbnail_url || '/images/placeholder.jpg'" alt="Miniatura del video" @error="handleImageError" />
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
            event.target.src = '/images/placeholder.jpg';
            event.target.style.display = '';
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
  transition: transform 0.3s, box-shadow 0.3s;
  background: #1a1a1a;
}

.content-card:hover {
  transform: scale(1.02);
  box-shadow: 0 0 15px rgba(0, 255, 204, 0.5);
}

.guide-card, .screenshot-card, .video-card {
  display: flex;
  flex-direction: column;
}

.guide-card-image, .screenshot-image, .video-thumbnail {
  height: 150px;
  overflow: hidden;
  position: relative;
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
  font-family: 'Press Start 2P', monospace;
  text-transform: uppercase;
  text-shadow: 1px 1px #000;
}

.guide-card-content, .screenshot-meta, .video-meta {
  padding: 10px;
  background: #1a1a1a;
  color: #ffffff;
  font-family: 'Press Start 2P', monospace;
  text-transform: uppercase;
  text-shadow: 1px 1px #000;
}

.guide-title {
  font-size: 1.1em;
  margin: 0;
  color: #ff0066;
  text-shadow: 2px 2px #000, -1px -1px #00ffcc;
}

.guide-description {
  font-size: 0.9em;
  margin: 5px 0 0;
  color: #e0e0e0;
}

.play-icon {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 2em;
  color: #ff0066;
  text-shadow: 2px 2px #000, -1px -1px #00ffcc;
}

@media (max-width: 768px) {
  .items-grid {
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 15px;
  }

  .guide-card-image, .screenshot-image, .video-thumbnail,
  .no-image-placeholder {
    height: 120px;
  }

  .guide-card-content, .screenshot-meta, .video-meta {
    padding: 8px;
  }

  .guide-title {
    font-size: 1em;
  }

  .guide-description {
    font-size: 0.85em;
  }
}

@media (max-width: 480px) {
  .items-grid {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
  }

  .guide-card-image, .screenshot-image, .video-thumbnail,
  .no-image-placeholder {
    height: 100px;
  }

  .guide-card-content, .screenshot-meta, .video-meta {
    padding: 6px;
  }

  .guide-title {
    font-size: 0.9em;
  }

  .guide-description {
    font-size: 0.8em;
  }

  .play-icon {
    font-size: 1.5em;
  }
}
  </style>