<template>
  <div class="video-details-container">
    <nav class="nav-container">
      <div class="logo">
        <router-link to="/" class="logo-link">🎮 Valtheris</router-link>
      </div>
      <ul>
        <li>
          <router-link to="/videos">
            <i class="icon">🎥</i> <span>Volver a los videos</span>
          </router-link>
        </li>
        <li>
          <router-link to="/comunidad">
            <i class="icon">👥</i> <span>Comunidad</span>
          </router-link>
        </li>
        <li>
          <router-link :to="isAuthenticated ? '/perfil' : '/login'">
            <i class="icon">{{ isAuthenticated ? '👤' : '🔑' }}</i>
            <span>{{ isAuthenticated ? 'Perfil' : 'Login' }}</span>
          </router-link>
        </li>
      </ul>
    </nav>

    <div class="main-content">
      <div v-if="loadingVideo" class="loading-message">
        <i class="icon loading-icon">⏳</i> Cargando video...
      </div>
      <div v-else-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>
      <div v-else class="video-details">
        <div class="video-player-container">
          <video controls :src="video.video_url" class="video-player" @error="handleVideoError">
            Tu navegador no soporta el elemento de video.
          </video>
        </div>

        <div class="content-wrapper">
          <div class="left-column"></div>

          <div class="right-column">
            <div v-if="isAuthenticated && user && video && video.user_id === user.id" class="owner-controls">
              <h3>Controles del propietario</h3>
              <button @click="deleteVideo(video.id)" class="delete-btn">
                <i class="icon">🗑️</i> Eliminar
              </button>
            </div>

            <!-- Información del video -->
            <div class="video-info">
              <h3>Información del video</h3>
              <p><strong>Título:</strong> {{ video.title || 'Sin título' }}</p>
              <p><strong>Subido por:</strong> {{ video.username || 'Anónimo' }}</p>
              <p><strong>Fecha de subida:</strong> {{ videoDate }}</p>
              <p v-if="video.duration"><strong>Duración:</strong> {{ video.duration }} minutos</p>
              <p v-if="video.description"><strong>Descripción:</strong> {{ video.description }}</p>
            </div>

            
          </div>
        </div>
      </div>

      
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import { isAuthenticated } from '@/api/auth';

export default {
  name: 'VideoDetailsView',
  props: {
    id: {
      type: [String, Number],
      required: true,
    },
  },
  data() {
    return {
      isAuthenticated: localStorage.getItem('auth_token') !== null,
      user: null,
      userTier: 'Tier 1',
      video: {},
      loadingVideo: false,
      errorMessage: '',
    };
  },
  computed: {
    videoDate() {
      if (!this.video || !this.video.created_at) return 'N/A';
      const date = new Date(this.video.created_at);
      return date.toLocaleDateString('es-ES', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    },
  },
  created() {
    window.addEventListener('storage', this.updateAuthStatus);
    this.checkAuthStatus();
    this.fetchUserData();
    this.fetchVideo();
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.updateAuthStatus);
  },
  methods: {
    async checkAuthStatus() {
      this.isAuthenticated = await isAuthenticated();
      if (!this.isAuthenticated) {
        localStorage.removeItem('auth_token');
      }
    },
    updateAuthStatus() {
      this.isAuthenticated = localStorage.getItem('auth_token') !== null;
      if (this.isAuthenticated) {
        this.fetchUserData();
      } else {
        this.userTier = 'Tier 1';
      }
    },
    async fetchUserData() {
      try {
        const token = localStorage.getItem('auth_token');
        if (!token) return;

        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        await axios.get('sanctum/csrf-cookie');
        const response = await axios.get('/api/user');
        this.user = {
          id: response.data.id,
          name: response.data.name,
          email: response.data.email,
        };
        this.userTier = response.data.tier || 'Tier 1';
      } catch (error) {
        console.error('Error en fetchUserData:', error);
        this.errorMessage = error.response ? error.response.data.message : error.message;
        localStorage.removeItem('auth_token');
        this.isAuthenticated = false;
        this.userTier = 'Tier 1';
      }
    },
    async fetchVideo() {
  this.loadingVideo = true;
  this.errorMessage = '';
  try {
    axios.defaults.baseURL = 'http://localhost:8000';
    axios.defaults.withCredentials = true;
    await axios.get('sanctum/csrf-cookie');
    const response = await axios.get(`/api/videos/${this.id}`);
    this.video = {
      ...response.data,
      video_url: response.data.video_url.startsWith('/storage')
        ? `http://localhost:8000${response.data.video_url}`
        : response.data.video_url,
      thumbnail_url: response.data.thumbnail_url ? `http://localhost:8000${response.data.thumbnail_url}` : null,
      duration: response.data.duration,
      description: response.data.description,
      likes: response.data.likes || 0,
      dislikes: response.data.dislikes || 0,
      username: response.data.username || 'Anónimo', // Use response.data.username directly
    };
    console.log('Fetched video data:', this.video); // Debug log
  } catch (error) {
    const errorMsg = error.response
      ? error.response.status === 404
        ? 'El video no se encuentra disponible.'
        : `${error.response.status}: ${error.response.data.message || error.response.statusText}`
      : error.message;
    this.errorMessage = errorMsg;
    this.video = {};
  } finally {
    this.loadingVideo = false;
  }
},
    async deleteVideo(videoId) {
      if (!confirm('¿Estás seguro de que quieres eliminar este video?')) return;

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación.');
        }
        await axios.delete(`/api/videos/${videoId}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        this.$router.push('/videos');
      } catch (error) {
        console.error('Error al eliminar video:', error);
        this.errorMessage = error.response?.data?.message || 'Error al eliminar el video.';
      }
    },
    async likeVideo(videoId) {
      if (!this.isAuthenticated) {
        await Swal.fire({
          title: 'No autenticado',
          text: 'Por favor, inicia sesión para dar me gusta.',
          icon: 'warning',
          confirmButtonColor: '#ff6f61',
        });
        this.$router.push('/login');
        return;
      }

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        const response = await axios.post(`/api/videos/${videoId}/like`, {}, {
          headers: { Authorization: `Bearer ${token}` },
        });
        await this.fetchVideo();
        await Swal.fire({
          title: '¡Me gusta registrado!',
          text: response.data.message,
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        console.error('Error al dar me gusta:', error);
        const errorMessage = error.response?.data?.message || 'Error al registrar me gusta.';
        await Swal.fire({
          title: 'Error',
          text: errorMessage,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
      }
    },
    async dislikeVideo(videoId) {
      if (!this.isAuthenticated) {
        await Swal.fire({
          title: 'No autenticado',
          text: 'Por favor, inicia sesión para dar no me gusta.',
          icon: 'warning',
          confirmButtonColor: '#ff6f61',
        });
        this.$router.push('/login');
        return;
      }

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        const response = await axios.post(`/api/videos/${videoId}/dislike`, {}, {
          headers: { Authorization: `Bearer ${token}` },
        });
        await this.fetchVideo();
        await Swal.fire({
          title: '¡No me gusta registrado!',
          text: response.data.message,
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        console.error('Error al dar no me gusta:', error);
        const errorMessage = error.response?.data?.message || 'Error al registrar no me gusta.';
        await Swal.fire({
          title: 'Error',
          text: errorMessage,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
      }
    },
    handleVideoError(event) {
      console.error('Error al cargar el video:', event);
      if (event.target.error) {
        console.error('Código de error del media:', event.target.error.code);
        console.error('Mensaje de error del media:', event.target.error.message);
      }
      this.errorMessage = 'No se pudo cargar el video. Verifica que el archivo exista y sea accesible.';
    },
    formatTimestamp(timestamp) {
      if (!timestamp) return 'N/A';
      const date = new Date(timestamp);
      return date.toLocaleString('es-ES', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    },
  },
};
</script>

<style scoped>
.video-details-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #1e1e2f 0%, #2a2a3f 100%);
  color: #fff;
  font-family: 'Arial', sans-serif;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Barra de navegación */
.nav-container {
  width: 100%;
  padding: 15px 20px;
  background-color: rgba(255, 255, 255, 0.95);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
  position: sticky;
  top: 0;
  z-index: 10;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo-link {
  text-decoration: none;
  font-size: 1.5em;
  font-weight: bold;
  color: #ff6f61;
  transition: color 0.3s;
}

.logo-link:hover {
  color: #e63946;
}

.nav-container ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  align-items: center;
}

.nav-container ul li {
  margin: 0 20px;
}

.nav-container ul li a {
  text-decoration: none;
  color: #ff6f61;
  font-size: 1.2em;
  display: flex;
  align-items: center;
  transition: color 0.3s;
}

.nav-container ul li a .icon {
  margin-right: 8px;
}

.nav-container ul li a:hover {
  color: #e63946;
}

/* Contenido principal */
.main-content {
  width: 85%;
  max-width: 900px;
  margin: 20px 0;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Contenedor de video mejorado */
.video-player-container {
  width: 100%;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.video-player {
  width: 100%;
  height: auto;
  max-height: 500px;
  object-fit: contain;
  display: block;
}

/* Contenedor de dos columnas */
.content-wrapper {
  display: flex;
  flex-direction: row;
  gap: 20px;
  position: relative;
  z-index: 1;
}

/* Columna izquierda: Espacio reservado (vacío) */
.left-column {
  flex: 2;
  min-height: 200px; /* Placeholder height to match two-column design */
  background: rgba(30, 30, 47, 0.5); /* Light overlay to indicate reserved space */
  border-radius: 8px;
}

/* Columna derecha (Controles e información) */
.right-column {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

/* Controles del propietario */
.owner-controls {
  margin-top: 6px;
  background: #1e1e2f;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #3a3a5f;
}

.owner-controls h3 {
  color: #ff6f61;
  margin: 0 0 10px;
  font-size: 1.2em;
}

.delete-btn {
  background: #e63946;
  padding: 8px 15px;
  border-radius: 5px;
  color: #fff;
  font-size: 0.9em;
  display: flex;
  align-items: center;
  gap: 5px;
  border: none;
  cursor: pointer;
  transition: background 0.3s;
}

.delete-btn:hover {
  background: #d62828;
}

/* Información del video */
.video-info {
  background: #1e1e2f;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #3a3a5f;
}

.video-info h3 {
  color: #ff6f61;
  margin: 0 0 10px;
  font-size: 1.2em;
}

.video-info p {
  color: #fff;
  margin: 5px 0;
  font-size: 0.9em;
}

/* Acciones del video */
.video-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-start;
  align-items: center;
}

.action-btn {
  background: #2a2a3f;
  padding: 8px 15px;
  border-radius: 5px;
  color: #fff;
  font-size: 0.9em;
  display: flex;
  align-items: center;
  gap: 5px;
  border: none;
  cursor: pointer;
  transition: background 0.3s, transform 0.2s;
}

.action-btn:hover {
  background: #ff6f61;
  transform: scale(1.05);
}

.dislike-btn {
  background: #2a2a3f;
}

.dislike-btn:hover {
  background: #e63946;
}

/* Botón para volver */
.back-btn {
  background: linear-gradient(90deg, #ff6f61, #ff9f1c);
  color: #fff;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1em;
  transition: background 0.3s;
  margin: 15px 0;
  display: block;
  width: fit-content;
  align-self: center;
}

.back-btn:hover {
  background: linear-gradient(90deg, #e63946, #ff9f1c);
}

/* Loading and Error Messages */
.loading-message {
  color: #ff6f61;
  font-size: 1em;
  text-align: center;
  padding: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.loading-icon {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  text-align: center;
  color: #e63946;
  font-size: 1.2em;
  font-weight: bold;
  padding: 20px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .main-content {
    width: 95%;
  }

  .content-wrapper {
    flex-direction: column;
  }

  .left-column {
    min-height: 100px; /* Reduced height for mobile */
  }

  .right-column {
    flex: 1;
  }

  .video-player {
    max-height: 300px;
  }

  .video-actions {
    flex-direction: column;
  }

  .action-btn {
    width: 100%;
    margin-bottom: 10px;
  }
}
</style>