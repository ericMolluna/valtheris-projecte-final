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
                <label for="video-file">Seleccionar </label>
                <input type="file" id="video-file" accept="video/mp4,video/mov,video/avi"
                  @change="handleVideoFileChange" required />
              </div>
              <div class="form-group">
                <label for="thumbnail">Portada del Video (Obligatoria)</label>
                <input type="file" id="thumbnail" accept="image/jpeg,image/png,image/jpg" @change="handleThumbnailFileChange" required />
              </div>
              <div class="form-group">
                <label for="video-description">Descripción</label>
                <textarea id="video-description" v-model="newVideo.description" placeholder="Descripción del video..."></textarea>
              </div>
              <div class="form-actions">
                <button type="submit" class="submit-btn">Subir</button>
                <button type="button" class="cancel-btn" @click="cancelUpload">Cancelar</button>
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
              <img :src="video.thumbnail_url" alt="Portada del video" @error="handleImageError" />
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
import { isAuthenticated } from './api/auth';

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
      tabs: [
        { name: 'Todo', path: '/comunidad' },
        { name: 'Capturas', path: '/comunidad/capturas' },
        { name: 'Guías', path: '/comunidad/guias' },
        { name: 'Videos', path: '/videos/videos' },
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
  beforeDestroy() {
    window.removeEventListener('storage', this.updateAuthStatus);
  },
  methods: {
    async checkAuthStatus() {
      this.isAuthenticated = await isAuthenticated();
      if (!this.isAuthenticated) {
        localStorage.removeItem('auth_token');
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
          console.log(`Video ID: ${video.id}, Thumbnail URL: ${video.thumbnail_url}`);
          return {
            ...video,
            type: 'video',
          };
        });
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
    handleThumbnailFileChange(event) {
      this.newVideo.thumbnail = event.target.files[0];
    },
    async uploadVideo() {
  this.videoUploadError = '';
  if (!this.newVideo.title) {
    this.videoUploadError = 'El título es requerido';
    return;
  }
  if (!this.newVideo.video) {
    this.videoUploadError = 'Debes seleccionar un video';
    return;
  }
  if (!this.newVideo.thumbnail) {
    this.videoUploadError = 'Debes seleccionar una portada';
    return;
  }
  const validVideoTypes = ['video/mp4', 'video/mov', 'video/avi'];
  if (!validVideoTypes.includes(this.newVideo.video.type)) {
    this.videoUploadError = 'El video debe ser MP4, MOV o AVI';
    return;
  }
  if (this.newVideo.video.size > 10240 * 1024) {
    this.videoUploadError = 'El video no debe exceder 10MB';
    return;
  }
  const validThumbnailTypes = ['image/jpeg', 'image/png', 'image/jpg'];
  if (!validThumbnailTypes.includes(this.newVideo.thumbnail.type)) {
    this.videoUploadError = 'La portada debe ser JPEG, PNG o JPG';
    return;
  }
  if (this.newVideo.thumbnail.size > 2048 * 1024) {
    this.videoUploadError = 'La portada no debe exceder 2MB';
    return;
  }

  try {
    const formData = new FormData();
    formData.append('title', this.newVideo.title);
    formData.append('video', this.newVideo.video);
    formData.append('thumbnail', this.newVideo.thumbnail);
    formData.append('description', this.newVideo.description || '');

    console.log('Uploading video:', {
      title: this.newVideo.title,
      video: this.newVideo.video.name,
      videoSize: (this.newVideo.video.size / 1024 / 1024).toFixed(2) + ' MB',
      videoType: this.newVideo.video.type,
      thumbnail: this.newVideo.thumbnail?.name || 'none',
      thumbnailSize: this.newVideo.thumbnail ? (this.newVideo.thumbnail.size / 1024 / 1024).toFixed(2) + ' MB' : 'N/A',
      thumbnailType: this.newVideo.thumbnail?.type || 'N/A',
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
    this.newVideo = { title: '', video: null, thumbnail: null, description: '' };
    this.videoUploadError = '';
    await this.fetchVideos();
    Swal.fire({
      icon: 'success',
      title: '¡Video subido!',
      text: 'Tu video ha sido publicado exitosamente.',
      timer: 2000,
      showConfirmButton: false,
    });
  } catch (error) {
    console.error('Error uploading video:', error.response?.data || error.message);
    let errorMsg = 'Error al subir el video';
    if (error.response?.status === 422) {
      const errors = error.response.data.errors;
      errorMsg = Object.values(errors).flat().join('; ');
    } else if (error.response?.status === 401) {
      errorMsg = 'No autenticado. Por favor, inicia sesión.';
      localStorage.removeItem('auth_token');
      this.isAuthenticated = false;
      this.$router.push('/login');
    } else if (error.response?.data?.message) {
      errorMsg = error.response.data.message;
    }
    this.videoUploadError = errorMsg;
  }
},
    cancelUpload() {
      this.showVideoUploadForm = false;
      this.newVideo = { title: '', video: null, description: '', thumbnail: null };
      this.videoUploadError = '';
    },
    handleImageError(event) {
      console.warn('Thumbnail load error, using fallback');
      event.target.src = '/images/default-thumbnail.jpg';
    },
    openVideoModal(video) {
      this.selectedVideo = video;
    },
    closeVideoModal() {
      this.selectedVideo = null;
    },
    async likeVideo(videoId) {
      if (!this.isAuthenticated) {
        Swal.fire({
          icon: 'warning',
          title: 'No autenticado',
          text: 'Por favor, inicia sesión para dar me gusta.',
        });
        this.$router.push('/login');
        return;
      }
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        await axios.post(`/api/videos/${videoId}/like`, {}, {
          headers: { Authorization: `Bearer ${token}` },
        });
        await this.fetchVideos();
        Swal.fire({
          icon: 'success',
          title: '¡Me gusta registrado!',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Error al registrar me gusta.',
        });
      }
    },
    async dislikeVideo(videoId) {
      if (!this.isAuthenticated) {
        Swal.fire({
          icon: 'warning',
          title: 'No autenticado',
          text: 'Por favor, inicia sesión para dar no me gusta.',
        });
        this.$router.push('/login');
        return;
      }
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        await axios.post(`/api/videos/${videoId}/dislike`, {}, {
          headers: { Authorization: `Bearer ${token}` },
        });
        await this.fetchVideos();
        Swal.fire({
          icon: 'success',
          title: '¡No me gusta registrado!',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'Error al registrar no me gusta.',
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

<style src="@/assets/styles/Videos/VideoView.css" scoped></style>


