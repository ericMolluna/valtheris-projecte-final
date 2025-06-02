
<template>
  <div class="videos-container">
    <!-- Barra de navegación superior -->
    <nav class="nav-container">
      <div class="logo">
        <router-link to="/" class="logo-link">🎮 Valtheris</router-link>
      </div>
      <ul>
        <li v-if="userTier === 'Tier 3'">
          <router-link to="/juego">
            <i class="icon">🎮</i> <span>Jugar</span>
          </router-link>
        </li>
        <li v-else class="coming-soon">
          <span><i class="icon">🎮</i> <span>Próximamente</span></span>
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

    <!-- Barra de pestañas -->
    <div class="tabs-container">
      <router-link v-for="tab in tabs" :key="tab.path" :to="tab.path" class="tab"
        :class="{ active: $route.path === tab.path }">
        {{ tab.name }}
      </router-link>
    </div>

    <!-- Contenedor principal -->
    <div class="main-content">
      <div class="videos-content">
        <!-- Header -->
        <div class="videos-header">
          <div class="header-left">
            <h2>Videos</h2>
          </div>
          <div class="header-right">
            <button :class="{ 'filter-btn': true, active: sortByVideos === 'popular' }"
              @click="sortByVideos = 'popular'">
              Más Populares
            </button>
            <button :class="{ 'filter-btn': true, active: sortByVideos === 'recent' }" @click="sortByVideos = 'recent'">
              Más Recientes
            </button>
          </div>
          <button v-if="isAuthenticated" @click="showVideoUploadForm = true" class="upload-video-btn">
            <i class="icon">🎥</i> Subir Video
          </button>
        </div>

        <!-- Formulario para subir video -->
        <div v-if="showVideoUploadForm" class="upload-form-modal">
          <div class="upload-form">
            <h3>Subir Nuevo Video</h3>
            <form @submit.prevent="uploadVideo">
              <div class="form-group">
                <label for="video-title">Título del Video</label>
                <input type="text" id="video-title" v-model="newVideo.title" placeholder="Título de tu video..."
                  required />
              </div>
              <div class="form-group">
                <label for="video-file">Seleccionar Video</label>
                <input type="file" id="video-file" accept="video/mp4,video/quicktime,video/x-msvideo"
                  @change="handleVideoFileChange" required />
              </div>
              <div class="form-group">
                <label for="thumbnail">Miniatura del video</label>
                <input type="file" id="thumbnail" accept="image/*" @change="handleThumbnailChange" />
              </div>
              <div class="form-actions">
                <button type="submit" class="submit-btn">Subir</button>
                <button type="button" class="cancel-btn" @click="showVideoUploadForm = false">
                  Cancelar
                </button>
              </div>
              <p v-if="videoUploadError" class="error-message">{{ videoUploadError }}</p>
            </form>
          </div>
        </div>

        <!-- Lista de videos o placeholder -->
        <div v-if="loadingVideos" class="loading-message">
          Cargando videos...
        </div>
        <div v-else-if="errorMessageVideos" class="error-message">
          {{ errorMessageVideos }}
        </div>
        <div v-else-if="videos.length === 0" class="no-videos-placeholder">
          <div class="placeholder-content">
            <i class="placeholder-icon">🎥</i>
            <p>No hay videos disponibles todavía.</p>
            <p>¡Sé el primero en compartir tus videos con la comunidad!</p>
          </div>
        </div>
        <div v-else class="videos-list">
          <div class="video-card" v-for="video in sortedVideos" :key="video.id" @click="openVideoModal(video)">
  <div class="video-placeholder">
    <img :src="video.thumbnail_url" alt="Miniatura del video" @error="handleImageError" />
    <div class="play-icon">▶</div>
  </div>
  <div class="video-meta">
    <span>{{ video.title || 'Sin título' }}</span>
  </div>
</div>
</div>

        <!-- Modal de reproducción -->
        <div v-if="selectedVideo" class="video-modal">
          <div class="modal-content">
            <button class="close-btn" @click="closeVideoModal">✖</button>
            <div class="modal-video">
              <video controls :src="selectedVideo.video_url" class="video-player" @error="handleVideoError">
                Tu navegador no soporta el elemento de video.
              </video>
            </div>
            <div class="modal-details">
              <div class="modal-meta">
                <span>{{ selectedVideo.title || 'Sin título' }}</span>
                <div class="modal-actions">
                  <button class="action-btn" @click="likeVideo(selectedVideo.id)">
                    <i class="icon">👍</i> Me gusta ({{ selectedVideo.likes || 0 }})
                  </button>
                  <button class="action-btn dislike-btn" @click="dislikeVideo(selectedVideo.id)">
                    <span class="icon">👎</span> No me gusta ({{ selectedVideo.dislikes || 0 }})
                  </button>
                </div>
              </div>
              <div class="comments-section">
                <div v-if="errorMessageVideos" class="error-message">
                  {{ errorMessageVideos }}
                </div>
                <div class="comment-actions">
                  <router-link :to="`/videos/${selectedVideo.id}`" class="view-details-btn">
                    Ver más detalles
                  </router-link>
                </div>
              </div>
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
  name: 'VideoView',
  data() {
    return {
      isAuthenticated: localStorage.getItem('auth_token') !== null,
      user: null,
      userTier: 'Tier 1',
      videos: [],
      loadingVideos: false,
      errorMessageVideos: '',
      sortByVideos: 'recent',
      showVideoUploadForm: false,
      newVideo: {
        title: '',
        video: null,
        description: '',
        thumbnail: null,
      },
      videoUploadError: '',
      selectedVideo: null,
      videoComments: [],
      newVideoComment: '',
      loadingComments: false,
      submittingComment: false,
      tabs: [
        { name: 'Todo', path: '/comunidad' },
        { name: 'Capturas', path: '/comunidad/capturas' },
        { name: 'Guías', path: '/comunidad/guias' },
        { name: 'Videos', path: '/videos' },
      ],
    };
  },

  computed: {
    sortedVideos() {
      if (this.sortByVideos === 'popular') {
        return [...this.videos].sort((a, b) => (b.popularity || 0) - (a.popularity || 0));
      }
      return [...this.videos].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    },
  },
  created() {
    window.addEventListener('storage', this.updateAuthStatus);
    this.checkAuthStatus();
    this.fetchUserData();
    this.fetchVideos();
  },
  watch: {
    sortByVideos() {
      this.fetchVideos();
    },
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

    handleThumbnailChange(event) {
      this.newVideo.thumbnail = event.target.files[0];
    },

    handleImageError(event) {
  event.target.src = 'https://picsum.photos/150/150?random=1'; // Reliable placeholder
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
        localStorage.removeItem('auth_token');
        this.isAuthenticated = false;
        this.userTier = 'Tier 1';
      }
    },
    async fetchVideos() {
  this.loadingVideos = true;
  this.errorMessageVideos = '';
  try {
    axios.defaults.baseURL = 'http://localhost:8000';
    axios.defaults.withCredentials = true;
    await axios.get('sanctum/csrf-cookie');
    const response = await axios.get(`/api/videos?sort=${this.sortByVideos}`);
    this.videos = response.data.map(video => {
      const thumbnailUrl = video.thumbnail_url
        ? `http://localhost:8000${video.thumbnail_url}`
        : 'https://picsum.photos/150/150?random=1'; // Updated placeholder
      console.log('Processing video:', video.title, 'Thumbnail URL:', thumbnailUrl);
      return {
        ...video,
        video_url: `http://localhost:8000${video.video_url}`,
        thumbnail_url: thumbnailUrl,
        type: 'video',
      };
    });
    console.log("Videos recibidos:", this.videos);
  } catch (error) {
    const errorMsg = error.response
      ? `${error.response.status}: ${error.response.data.message || error.response.statusText}`
      : error.message;
    this.errorMessageVideos = errorMsg;
    this.videos = [];
  } finally {
    this.loadingVideos = false;
  }
},
    handleVideoFileChange(event) {
      this.newVideo.video = event.target.files[0];
    },
    async uploadVideo() {
      // Validaciones
      if (!this.newVideo.title) {
        this.videoUploadError = 'El título es requerido';
        return;
      }
      if (!this.newVideo.video) {
        this.videoUploadError = 'Debes seleccionar un video';
        return;
      }
      const validVideoTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
      if (!validVideoTypes.includes(this.newVideo.video.type)) {
        this.videoUploadError = 'El video debe ser MP4, MOV o AVI';
        return;
      }
      if (this.newVideo.video.size > 10240 * 1024) {
        this.videoUploadError = 'El video no debe exceder 10MB';
        return;
      }
      if (this.newVideo.thumbnail && !['image/jpeg', 'image/png', 'image/jpg'].includes(this.newVideo.thumbnail.type)) {
        this.videoUploadError = 'La miniatura debe ser una imagen JPEG, PNG o JPG';
        return;
      }

      try {
        const formData = new FormData();
        formData.append('title', this.newVideo.title);
        formData.append('video', this.newVideo.video);
        formData.append('description', this.newVideo.description || '');
        if (this.newVideo.thumbnail) {
          formData.append('thumbnail', this.newVideo.thumbnail);
        }

        console.log('Uploading video:', {
          title: this.newVideo.title,
          video: this.newVideo.video.name,
          videoSize: (this.newVideo.video.size / 1024 / 1024).toFixed(2) + ' MB',
          videoType: this.newVideo.video.type,
          thumbnail: this.newVideo.thumbnail ? this.newVideo.thumbnail.name : 'No thumbnail',
        });

        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No authentication token found');
        }
        const response = await axios.post('/api/videos', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            Authorization: `Bearer ${token}`,
          },
        });
        console.log('Video uploaded:', response.data);
        this.showVideoUploadForm = false;
        this.videoUploadError = '';
        this.newVideo = { title: '', video: null, description: '', thumbnail: null };
        await this.fetchVideos();
        await Swal.fire({
          title: '¡Video subido!',
          text: 'Tu video ha sido publicado exitosamente.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        console.error('Error al subir video:', error.response?.data || error.message);
        let errorMsg = 'Error al subir el video';
        if (error.response?.status === 401) {
          errorMsg = 'No autenticado. Por favor, inicia sesión de nuevo.';
          localStorage.removeItem('auth_token');
          this.isAuthenticated = false;
          this.$router.push('/login');
        } else if (error.response?.status === 422) {
          const errors = error.response.data.errors;
          errorMsg = 'Validación fallida: ' + Object.values(errors).flat().join('; ');
        } else if (error.response?.data?.message) {
          errorMsg = error.response.data.message;
        }
        this.videoUploadError = errorMsg;
      }
    },
    openVideoModal(video) {
      this.selectedVideo = video;
      this.fetchVideoComments(video.id);
    },
    closeVideoModal() {
      this.selectedVideo = null;
      this.videoComments = [];
      this.newVideoComment = '';
      this.errorMessageVideos = '';
    },
    async fetchVideoComments(videoId) {
      this.loadingComments = true;
      this.errorMessageVideos = '';
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación.');
        }
        const response = await axios.get(`/api/videos/${videoId}/comments`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        this.videoComments = response.data;
      } catch (error) {
        console.error('Error al cargar comentarios:', error);
        this.videoComments = [];
        let errorMessage = 'No se pudieron cargar los comentarios.';
        if (error.response) {
          errorMessage = error.response.data.message || `Error del servidor: ${error.response.status}`;
        } else if (error.request) {
          errorMessage = 'No se recibió respuesta del servidor.';
        } else {
          errorMessage = error.message;
        }
        this.errorMessageVideos = errorMessage;
      } finally {
        this.loadingComments = false;
      }
    },
    async submitVideoComment() {
      if (!this.newVideoComment.trim()) {
        await Swal.fire({
          title: 'Comentario vacío',
          text: 'Por favor, escribe un comentario antes de enviar.',
          icon: 'warning',
          confirmButtonColor: '#ff6f61',
        });
        return;
      }

      if (this.newVideoComment.length > 1000) {
        await Swal.fire({
          title: 'Comentario demasiado largo',
          text: 'El comentario no puede exceder los 1000 caracteres.',
          icon: 'warning',
          confirmButtonColor: '#ff6f61',
        });
        return;
      }

      this.submittingComment = true;
      this.errorMessageVideos = '';

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación.');
        }

        const response = await axios.post(`/api/videos/${this.selectedVideo.id}/comments`, {
          text: this.newVideoComment,
        }, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        this.newVideoComment = '';
        await this.fetchVideoComments(this.selectedVideo.id);
        await Swal.fire({
          title: '¡Comentario enviado!',
          text: response.data.message,
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        console.error('Error al enviar comentario:', error);
        this.errorMessageVideos = error.response?.data?.message || 'Error al enviar el comentario. Por favor, intenta de nuevo.';
        await Swal.fire({
          title: 'Error',
          text: this.errorMessageVideos,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
      } finally {
        this.submittingComment = false;
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
        await this.fetchVideos();
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
        await this.fetchVideos();
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
      this.errorMessageVideos = 'No se pudo cargar el video.';
    },
  },
};
</script>

<style>
.videos-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #1e1e2f 0%, #2a2a3f 100%);
  color: #fff;
  font-family: 'Arial', sans-serif;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.nav-container {
  width: 100%;
  padding: 15px 20px;
  background-color: rgba(255, 255, 255, 0.95);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  position: sticky;
  top: 0;
  z-index: 10;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-sizing: border-box;
}

.logo-link {
  text-decoration: none;
  font-size: 1.3em; /* Adjusted as per previous updates */
  font-weight: bold;
  color: #ff6f61;
  transition: color 0.3s;
}

.logo-link:hover {
  color: #e63946;
}

.nav-container ul {
  list-style: none;
  padding: 0 20px;
  margin: 0;
  display: flex;
  align-items: center;
}

.nav-container ul li {
  margin: 0 15px;
}

.nav-container ul li a {
  text-decoration: none;
  color: #ff6f61;
  font-size: 1em; /* Adjusted as per previous updates */
  display: flex;
  align-items: center;
  transition: color 0.3s;
}

.nav-container ul li a .icon {
  margin-right: 8px;
  font-size: 1.2em;
  vertical-align: middle;
  transition: none;
}

.nav-container ul li a:hover {
  color: #e63946;
}

.coming-soon span {
  color: #999;
  font-style: italic;
  cursor: not-allowed;
}

.tabs-container {
  width: 85%;
  max-width: 800px; /* Adjusted as per previous updates */
  background: #2a2a3f;
  padding: 10px 0;
  display: flex;
  justify-content: flex-start;
  border-bottom: 1px solid #3a3a4f;
}

.tab {
  padding: 10px 20px;
  color: #fff;
  text-decoration: none;
  font-size: 1em;
  text-transform: uppercase;
  transition: background 0.3s, color 0.3s;
}

.tab:hover {
  background: #3a3a4f;
  color: #ff6f61;
}

.tab.active {
  background: #ff6f61;
  color: #fff;
  font-weight: bold;
}

.main-content {
  width: 85%;
  max-width: 800px; /* Adjusted as per previous updates */
  margin: 20px 0;
  flex-grow: 1;
}

.videos-content {
  background: rgba(255, 255, 255, 0.95);
  padding: 15px; /* Adjusted as per previous updates */
  border-radius: 5px;
}

.videos-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.header-left h2 {
  margin: 0;
  color: #ff6f61;
  font-size: 1.5em; /* Adjusted as per previous updates */
}

.header-right {
  display: flex;
  gap: 10px;
}

.filter-btn {
  background: #2a2a3f;
  color: #fff;
  padding: 6px 12px; /* Adjusted as per previous updates */
  border: none;
  border-radius: 3px;
  cursor: pointer;
  font-size: 0.9em;
  text-transform: uppercase;
  transition: background 0.3s, color 0.3s;
}

.filter-btn:hover {
  background: #ff6f61;
  color: #fff;
}

.filter-btn.active {
  background: #ff6f61;
  color: #fff;
}

.upload-video-btn {
  background: linear-gradient(90deg, #ff6f61, #ff9f1c);
  color: #fff;
  padding: 6px 12px; /* Adjusted as per previous updates */
  border: none;
  border-radius: 3px;
  cursor: pointer;
  font-size: 0.9em;
  display: flex;
  align-items: center;
  transition: background 0.3s;
}

.upload-video-btn .icon {
  margin-right: 5px;
  font-size: 1.2em;
  vertical-align: middle;
}

.upload-video-btn:hover {
  background: linear-gradient(90deg, #e63946, #ff9f1c);
}

.no-videos-placeholder {
  text-align: center;
  padding: 40px;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 5px;
}

.placeholder-content {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.placeholder-icon {
  font-size: 2.5em; /* Adjusted as per previous updates */
  color: #ff6f61;
  margin-bottom: 15px;
}

.no-videos-placeholder p {
  color: #666;
  font-size: 1.2em;
  margin: 5px 0;
}

.videos-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); /* Adjusted as per previous updates */
  gap: 15px;
  margin-top: 20px;
}

.video-card {
  background: rgba(255, 255, 255, 0.9);
  border-radius: 5px;
  overflow: hidden;
  transition: transform 0.3s;
  cursor: pointer;
  position: relative;
}

.video-card:hover {
  transform: translateY(-5px);
}

.video-placeholder {
  width: 100%;
  height: 120px; /* Adjusted as per previous updates */
  background: #f0f0f0;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.video-placeholder img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  position: absolute;
  top: 0;
  left: 0;
}

.play-icon {
  font-size: 2em;
  color: #ff6f61;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 50%;
  padding: 10px;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.video-meta {
  margin: 11px 0 5px;
  color: #2a9d8f;
  font-size: 1.1em; /* Adjusted as per previous updates */
  text-align: center;
}

.upload-form-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 100;
}

.upload-form {
  background: #2a2a3f;
  padding: 20px;
  border-radius: 5px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.upload-form h3 {
  color: #ff6f61;
  margin-top: 0;
  font-size: 1.3em;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  color: #ff6f61;
  margin-bottom: 5px;
}

.form-group input[type="text"],
.form-group input[type="file"],
.form-group textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #2a2a3f;
  border-radius: 3px;
  background: #1e1e2f;
  color: #fff;
  box-sizing: border-box;
}

.form-group textarea {
  resize: vertical;
}

.form-actions {
  display: flex;
  gap: 10px;
}

.submit-btn,
.cancel-btn {
  padding: 8px 15px;
  border: none;
  border-radius: 3px;
  cursor: pointer;
  color: white;
  font-size: 0.9em;
  transition: background 0.3s;
}

.submit-btn {
  background: linear-gradient(90deg, #ff6f61, #ff9f1c);
}

.submit-btn:hover {
  background: linear-gradient(90deg, #e63946, #ff9f1c);
}

.cancel-btn {
  background: #2a2a3f;
}

.cancel-btn:hover {
  background: #e63946;
}

.video-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 200;
  overflow-y: auto;
}

.modal-content {
  background: #2a2a3f;
  border-radius: 10px;
  width: 90%;
  max-width: 800px; /* Adjusted as per previous updates */
  display: flex;
  flex-direction: row;
  position: relative;
  margin: 20px 0;
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: none;
  border: none;
  color: #fff;
  font-size: 1.5em;
  cursor: pointer;
  transition: color 0.3s;
}

.close-btn:hover {
  color: #ff6f61;
}

.modal-video {
  flex: 2; /* Adjusted as per previous updates */
  padding: 15px; /* Adjusted as per previous updates */
}

.video-player {
  width: 100%;
  max-height: 60vh; /* Adjusted as per previous updates */
  border-radius: 5px;
}

.modal-details {
  flex: 1; /* Adjusted as per previous updates */
  padding: 15px; /* Adjusted as per previous updates */
  background: #1e1e2f;
  border-radius: 0 10px 10px 0;
  color: #fff;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.modal-meta {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.modal-meta span {
  font-size: 1em;
  color: #2a9d8f;
}

.modal-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-start;
  align-items: center;
}

.modal-actions .action-btn {
  background: #2a2a3f;
  padding: 5px 10px;
  border-radius: 3px;
  color: #fff;
  font-size: 0.9em;
  display: flex;
  align-items: center;
  gap: 5px;
  border: none;
  cursor: pointer;
  transition: background 0.3s;
}

.modal-actions .action-btn:hover {
  background: #ff6f61;
}

.comments-section {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.comments-section h3 {
  margin: 0;
  color: #ff6f61;
  font-size: 1.3em;
}

.no-comments {
  text-align: center;
  color: #666;
}

.no-comments p {
  margin: 0;
  font-size: 1em;
}

.comments-list {
  max-height: 300px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.comment {
  background: #2a2a3f;
  padding: 10px;
  border-radius: 5px;
}

.comment-user {
  font-weight: bold;
  color: #2a9d8f;
  margin-right: 5px;
}

.comment-text {
  margin: 5px 0 0;
  color: #fff;
  font-size: 0.9em;
}

.comment-form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.comment-form textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #2a2a3f;
  border-radius: 3px;
  background: #1e1e2f;
  color: #fff;
  resize: vertical;
}

.comment-form textarea:disabled {
  background: #333;
  opacity: 0.7;
}

.submit-comment-btn {
  background: linear-gradient(90deg, #ff6f61, #ff9f1c);
  color: white;
  padding: 8px 15px;
  border: none;
  border-radius: 3px;
  cursor: pointer;
  font-size: 0.9em;
  transition: background 0.3s;
}

.submit-comment-btn:disabled {
  background: #666;
  cursor: not-allowed;
}

.submit-comment-btn:hover:not(:disabled) {
  background: linear-gradient(90deg, #e63946, #ff9f1c);
}

.comment-actions {
  margin-top: 10px;
}

.view-details-btn {
  background: #2a2a3f;
  color: #ff6f61;
  padding: 8px 15px;
  border-radius: 3px;
  text-decoration: none;
  font-size: 0.9em;
  transition: background 0.3s, color 0.3s;
}

.view-details-btn:hover {
  background: #ff6f61;
  color: #fff;
}

.loading-message {
  text-align: center;
  color: #ff6f61;
  font-size: 1.2em;
}

.error-message {
  text-align: center;
  color: #e63946;
  font-size: 1.2em;
  font-weight: bold;
}

@media (max-width: 768px) {
  .main-content {
    width: 95%;
  }

  .tabs-container {
    width: 95%;
    flex-wrap: wrap;
  }

  .tab {
    padding: 8px 15px;
    font-size: 0.9em;
  }

  .videos-list {
    grid-template-columns: 1fr;
  }

  .video-placeholder {
    height: 100px; /* Adjusted as per previous updates */
  }

  .video-placeholder img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
  }

  .modal-content {
    flex-direction: column;
    width: 95%;
  }

  .modal-video {
    padding: 10px;
  }

  .modal-details {
    border-radius: 0 0 10px 10px;
  }

  .modal-actions {
    flex-direction: row;
    justify-content: center;
  }

  .comments-list {
    max-height: 200px;
  }

  .videos-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .nav-container ul {
    padding: 0 10px;
  }

  .nav-container ul li {
    margin: 0 10px;
  }
}
</style>