<template>
  <div class="community-container">
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
      <!-- Contenido dinámico basado en la ruta -->
      <div class="content-section">
        <!-- Mostrar la página "Todo" si la ruta es /comunidad -->
        <div v-if="$route.path === '/comunidad'" class="todo-content">
          <div class="todo-header">
            <div class="header-left">
              <h2>Todo</h2>
            </div>
          </div>

          <!-- Lista combinada de todo el contenido -->
          <div class="todo-section">
            <div v-if="loadingAllContent" class="loading-message">
              Cargando contenido...
            </div>
            <div v-else-if="errorMessageAllContent" class="error-message">
              {{ errorMessageAllContent }}
            </div>
            <div v-else-if="allContent.length === 0" class="no-content-placeholder">
              <p>No hay contenido disponible todavía.</p>
            </div>
            <div v-else class="content-list">
              <div class="content-card" v-for="item in allContent" :key="item.id + '-' + item.type"
                @click="handleContentClick(item)" :title="getContentTitle(item)">
                <!-- Renderizar según el tipo de contenido -->
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
                    <img :src="item.thumbnail_url || 'https://via.placeholder.com/280x150?text=Sin+miniatura'"
                      alt="Miniatura del video" @error="handleImageError" />
                    <div class="play-icon">▶</div>
                  </div>
                  <div class="video-meta">
                    <span>{{ item.title || 'Sin título' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Mostrar guías si la ruta es /comunidad/guias -->
        <div v-else-if="$route.path === '/comunidad/guias'" class="guides-content">
          <!-- Header Section -->
          <div class="guides-header">
            <div class="header-left">
              <h2>Guías</h2>
            </div>
            <div class="header-right">
              <button :class="{ 'filter-btn': true, active: sortByGuides === 'popular' }"
                @click="sortByGuides = 'popular'">
                Más Populares
              </button>
              <button :class="{ 'filter-btn': true, active: sortByGuides === 'recent' }"
                @click="sortByGuides = 'recent'">
                Más Recientes
              </button>
            </div>
            <router-link v-if="isAuthenticated" to="/comunidad/crear-guia" class="create-guide-btn">
              <i class="icon">✍️</i> Crear Nueva Guía
            </router-link>
          </div>

          <!-- Lista de guías o placeholder -->
          <div v-if="loadingGuides" class="loading-message">
            Cargando guías...
          </div>
          <div v-else-if="errorMessageGuides" class="error-message">
            {{ errorMessageGuides }}
          </div>
          <div v-else-if="guides.length === 0" class="no-guides-placeholder">
            <div class="placeholder-content">
              <i class="placeholder-icon">📖</i>
              <p>No hay guías disponibles todavía.</p>
              <p>¡Sé el primero en compartir tus conocimientos con la comunidad!</p>
            </div>
          </div>
          <div v-else class="guides-list">
            <div class="guide-card" v-for="guide in sortedGuides" :key="guide.id" @click="viewGuide(guide)">
              <div class="guide-card-image">
                <img v-if="guide.image" :src="guide.image" alt="Portada de la guía" @error="handleImageError" />
                <div v-else class="no-image-placeholder">Sin imagen</div>
              </div>
              <div class="guide-card-content">
                <h3 class="guide-title">{{ guide.title || 'Sin título' }}</h3>
                <p class="guide-description">{{ guide.description || 'Sin descripción' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Mostrar capturas si la ruta es /comunidad/capturas -->
        <div v-else-if="$route.path === '/comunidad/capturas'" class="screenshots-content">
          <!-- Header Section -->
          <div class="screenshots-header">
            <div class="header-left">
              <h2>Capturas</h2>
            </div>
            <div class="header-right">
              <button :class="{ 'filter-btn': true, active: sortByScreenshots === 'popular' }"
                @click="sortByScreenshots = 'popular'">
                Más Populares
              </button>
              <button :class="{ 'filter-btn': true, active: sortByScreenshots === 'recent' }"
                @click="sortByScreenshots = 'recent'">
                Más Recientes
              </button>
            </div>
            <button v-if="isAuthenticated" @click="showUploadForm = true" class="upload-screenshot-btn">
              <i class="icon">📸</i> Subir Captura
            </button>
          </div>

          <!-- Formulario para subir captura (modal) -->
          <div v-if="showUploadForm" class="upload-form-modal">
            <div class="upload-form">
              <h3>Subir Nueva Captura</h3>
              <form @submit.prevent="uploadScreenshot">
                <div class="form-group">
                  <label for="screenshot-title">Título de la Captura</label>
                  <input type="text" id="screenshot-title" v-model="newScreenshot.title"
                    placeholder="Título de tu captura..." required />
                </div>
                <div class="form-group">
                  <label for="screenshot-image">Seleccionar Imagen</label>
                  <input type="file" id="screenshot-image" accept="image/*" @change="handleFileChange" required />
                </div>

                <div class="form-actions">
                  <button type="submit" class="submit-btn">Subir</button>
                  <button type="button" class="cancel-btn" @click="showUploadForm = false">Cancelar</button>
                </div>
                <p v-if="uploadError" class="error-message">{{ uploadError }}</p>
              </form>
            </div>
          </div>

          <!-- Lista de capturas o placeholder -->
          <div v-if="loadingScreenshots" class="loading-message">
            Cargando capturas...
          </div>
          <div v-else-if="errorMessageScreenshots" class="error-message">
            {{ errorMessageScreenshots }}
          </div>
          <div v-else-if="screenshots.length === 0" class="no-screenshots-placeholder">
            <div class="placeholder-content">
              <i class="placeholder-icon">📸</i>
              <p>No hay capturas disponibles todavía.</p>
              <p>¡Sé el primero en compartir tus momentos en el juego!</p>
            </div>
          </div>
          <div v-else class="screenshots-list">
            <div class="screenshot-card" v-for="screenshot in sortedScreenshots" :key="screenshot.id"
              @click="openScreenshotModal(screenshot)">
              <div class="screenshot-image">
                <img :src="screenshot.image_url" alt="Captura del juego" @error="handleImageError" />
              </div>
              <div class="screenshot-meta">
                <span>{{ screenshot.title || 'Sin título' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Mostrar videos si la ruta es /videos -->
        <div v-else-if="$route.path === '/videos'" class="videos-content">
          <!-- Header Section -->
          <div class="videos-header">
            <div class="header-left">
              <h2>Videos</h2>
            </div>
            <div class="header-right">
              <button :class="{ 'filter-btn': true, active: sortByVideos === 'popular' }"
                @click="sortByVideos = 'popular'">
                Más Populares
              </button>
              <button :class="{ 'filter-btn': true, active: sortByVideos === 'recent' }"
                @click="sortByVideos = 'recent'">
                Más Recientes
              </button>
            </div>
            <button v-if="isAuthenticated" @click="showVideoUploadForm = true" class="upload-video-btn">
              <i class="icon">🎥</i> Subir Video
            </button>
          </div>

          <!-- Formulario para subir video (modal) -->
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
                  <input type="file" id="video-file" accept="video/*" @change="handleVideoFileChange" required />
                </div>
                <div class="form-group">
                  <label for="video-description">Descripción (Opcional)</label>
                  <textarea id="video-description" v-model="newVideo.description"
                    placeholder="Describe tu video..."></textarea>
                </div>
                <div class="form-actions">
                  <button type="submit" class="submit-btn">Subir</button>
                  <button type="button" class="cancel-btn" @click="showVideoUploadForm = false">Cancelar</button>
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
              <div class="video-thumbnail">
                <img :src="video.thumbnail_url || 'https://via.placeholder.com/280x150?text=Sin+miniatura'"
                  alt="Miniatura del video" @error="handleImageError" />
                <div class="play-icon">▶</div>
              </div>
              <div class="video-meta">
                <span>{{ video.title || 'Sin título' }}</span>
              </div>
            </div>
          </div>

          <!-- Modal para reproducir video -->
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
                    <button class="action-btn"><i class="icon">👍</i> Me gusta</button>
                    <button class="action-btn dislike-btn">
                      <span class="icon">👎</span> No me gusta
                    </button>
                  </div>
                </div>
                <div class="comments-section">
                  <h3>Comentarios</h3>
                  <div v-if="videoComments.length === 0" class="no-comments">
                    <p>No hay comentarios todavía. ¡Sé el primero en comentar!</p>
                  </div>
                  <div v-else class="comments-list">
                    <div v-for="comment in videoComments" :key="comment.id" class="comment">
                      <span class="comment-user">{{ comment.user?.name || 'Anónimo' }}:</span>
                      <p class="comment-text">{{ comment.text }}</p>
                    </div>
                  </div>
                  <div v-if="isAuthenticated" class="comment-form">
                    <textarea v-model="newVideoComment" placeholder="Añade un comentario..." rows="2"></textarea>
                    <button @click="submitVideoComment" class="submit-comment-btn">Comentar</button>
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

    <!-- Modal para mostrar la captura en tamaño completo con comentarios -->
    <div v-if="selectedScreenshot" class="screenshot-modal">
      <div class="modal-content">
        <button class="close-btn" @click="closeScreenshotModal">✖</button>
        <div class="modal-image">
          <img :src="selectedScreenshot.image_url" alt="Captura del juego en tamaño completo" />
        </div>
        <div class="modal-details">
          <div class="modal-meta">
            <span>{{ selectedScreenshot.title || 'Sin título' }}</span>
            <div class="modal-actions">
              <button class="action-btn" :class="{ 'liked': selectedScreenshot.isLikedByUser }"
                @click="toggleLike(selectedScreenshot)">
                <i class="icon">👍</i> Me gusta ({{ selectedScreenshot.likesCount || 0 }})
              </button>
              <button class="action-btn dislike-btn" :class="{ 'disliked': selectedScreenshot.isDislikedByUser }"
                @click="toggleDislike(selectedScreenshot)">
                <span class="icon">👎</span> No me gusta ({{ selectedScreenshot.dislikesCount || 0 }})
              </button>
            </div>
          </div>
          <div class="comments-section">
            <h3>Comentarios</h3>
            <div v-if="comments.length === 0" class="no-comments">
              <p>No hay comentarios todavía. ¡Sé el primero en comentar!</p>
            </div>
            <div v-else class="comments-list">
              <div v-for="comment in comments" :key="comment.id" class="comment">
                <span class="comment-user">{{ comment.user?.name || 'Anónimo' }}:</span>
                <p class="comment-text">{{ comment.text }}</p>
              </div>
            </div>
            <div v-if="isAuthenticated" class="comment-form">
              <textarea v-model="newComment" placeholder="Añade un comentario..." rows="2"></textarea>
              <button @click="submitComment" class="submit-comment-btn">Comentar</button>
            </div>
            <div class="comment-actions">
              <router-link :to="`/capturas/${selectedScreenshot.id}`" class="view-details-btn">
                Ver más detalles
              </router-link>
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
  name: 'CommunityView',
  data() {
    return {
      isAuthenticated: localStorage.getItem('auth_token') !== null,
      user: null,
      userTier: 'Tier 1',
      guides: [],
      loadingGuides: false,
      errorMessageGuides: '',
      sortByGuides: 'recent',
      screenshots: [],
      loadingScreenshots: false,
      errorMessageScreenshots: '',
      sortByScreenshots: 'recent',
      showUploadForm: false,
      newScreenshot: {
        title: '',
        image: null,
        description: '',
      },
      uploadError: '',
      selectedScreenshot: null,
      comments: [],
      newComment: '',
      videos: [],
      loadingVideos: false,
      errorMessageVideos: '',
      sortByVideos: 'recent',
      showVideoUploadForm: false,
      newVideo: {
        title: '',
        video: null,
        description: '',
      },
      videoUploadError: '',
      selectedVideo: null,
      videoComments: [],
      newVideoComment: '',
      allContent: [],
      loadingAllContent: false,
      errorMessageAllContent: '',
      tabs: [
        { name: 'Todo', path: '/comunidad' },
        { name: 'Capturas', path: '/comunidad/capturas' },
        { name: 'Guías', path: '/comunidad/guias' },
        { name: 'Videos', path: '/videos' },
      ],
    };
  },
  computed: {
    sortedGuides() {
      if (this.sortByGuides === 'popular') {
        const sorted = [...this.guides].sort((a, b) => (b.likes || 0) - (a.likes || 0)); // Use 'likes' if that's the field name
        console.log('Sorted guides by popularity:', sorted);
        return sorted;
      }
      const sorted = [...this.guides].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      console.log('Sorted guides by recent:', sorted);
      return sorted;
    },
    sortedScreenshots() {
      if (this.sortByScreenshots === 'popular') {
        return [...this.screenshots].sort((a, b) => (b.popularity || 0) - (a.popularity || 0));
      }
      return [...this.screenshots].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    },
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

    if (this.$route.path === '/comunidad') {
      this.fetchGuides();
      this.fetchScreenshots();
      this.fetchVideos();
    } else if (this.$route.path === '/comunidad/guias') {
      this.fetchGuides();
    } else if (this.$route.path === '/comunidad/capturas') {
      this.fetchScreenshots();
    } else if (this.$route.path === '/videos') {
      this.fetchVideos();
    }
  },
  watch: {
    '$route'(to) {
      console.log('Ruta actualizada a:', to.path);
      if (to.path === '/comunidad') {
        this.fetchGuides();
        this.fetchScreenshots();
        this.fetchVideos();
      } else if (to.path === '/comunidad/guias') {
        this.fetchGuides();
      } else if (to.path === '/comunidad/capturas') {
        this.fetchScreenshots();
      } else if (to.path === '/videos') {
        this.fetchVideos();
      }
    },
    sortByGuides() {
      this.fetchGuides();
    },
    sortByScreenshots() {
      this.fetchScreenshots();
    },
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
    updateAuthStatus() {
      this.isAuthenticated = localStorage.getItem('auth_token') !== null;
      if (this.isAuthenticated) {
        this.fetchUserData();
      } else {
        this.userTier = 'Tier 1';
      }
    },

    async fetchAllContent() {
      this.loadingAllContent = true;
      try {
        const response = await axios.get('/api/community-content?sort=' + this.sortBy);
        this.allContent = response.data.map(item => ({
          ...item,
          image_url: item.image_url ? `http://localhost:8000${item.image_url}` : item.image_url,
          thumbnail_url: item.thumbnail_url ? `http://localhost:8000${item.thumbnail_url}` : item.thumbnail_url,
          video_url: item.video_url ? `http://localhost:8000${item.video_url}` : item.video_url,
        }));
      } catch (error) {
        this.errorMessageAllContent = error.response?.data?.message || 'Error al cargar contenido.';
      } finally {
        this.loadingAllContent = false;
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
        this.errorMessageGuides = error.response ? error.response.data.message : error.message;
        this.errorMessageScreenshots = error.response ? error.response.data.message : error.message;
        this.errorMessageVideos = error.response ? error.response.data.message : error.message;
        localStorage.removeItem('auth_token');
        this.isAuthenticated = false;
        this.userTier = 'Tier 1';
      }
    },
    async fetchGuides() {
      this.loadingGuides = true;
      this.errorMessageGuides = '';
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const response = await axios.get(`/api/guides/all?sort=${this.sortByGuides}`);
        console.log('Raw API response for guides:', response.data); // Log raw data
        this.guides = response.data.map(guide => ({
          ...guide,
          type: 'guide',
        }));
        this.combineAllContent();
      } catch (error) {
        const errorMsg = error.response
          ? `${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        this.errorMessageGuides = errorMsg;
        this.guides = [];
      } finally {
        this.loadingGuides = false;
      }
    },
    viewGuide(guide) {
      this.$router.push(`/comunidad/guia/${guide.id}`);
    },
    async fetchScreenshots() {
      this.loadingScreenshots = true;
      this.errorMessageScreenshots = '';
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        const headers = token ? { Authorization: `Bearer ${token}` } : {};
        const response = await axios.get(`/api/screenshots?sort=${this.sortByScreenshots}`, { headers });
        this.screenshots = response.data.map(screenshot => ({
          ...screenshot,
          image_url: `http://localhost:8000${screenshot.image_url}`,
          type: 'screenshot',
          likesCount: screenshot.likes || screenshot.likes_count || 0,
          dislikesCount: screenshot.dislikes || screenshot.dislikes_count || 0,
          isLikedByUser: screenshot.user_liked || false,
          isDislikedByUser: screenshot.user_disliked || false,
        }));
        console.log('Screenshots obtenidos:', this.screenshots);
        this.combineAllContent();
      } catch (error) {
        const errorMsg = error.response
          ? `${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        this.errorMessageScreenshots = errorMsg;
        this.screenshots = [];
      } finally {
        this.loadingScreenshots = false;
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
          const thumbnailUrl = video.thumbnail_url ? `http://localhost:8000${video.thumbnail_url}` : 'https://via.placeholder.com/280x150?text=Sin+miniatura';
          console.log('Processing video:', video.title, 'Thumbnail URL:', thumbnailUrl);
          return {
            ...video,
            video_url: `http://localhost:8000${video.video_url}`,
            thumbnail_url: thumbnailUrl,
            type: 'video',
          };
        });
        console.log('Videos obtenidos:', this.videos);
        this.combineAllContent();
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
    combineAllContent() {
      this.loadingAllContent = true;
      this.errorMessageAllContent = '';
      try {
        this.allContent = [
          ...this.guides,
          ...this.screenshots,
          ...this.videos,
        ].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      } catch (error) {
        this.errorMessageAllContent = 'Error al combinar el contenido.';
        this.allContent = [];
      } finally {
        this.loadingAllContent = false;
      }
    },
    handleContentClick(item) {
      if (item.type === 'guide') {
        this.viewGuide(item);
      } else if (item.type === 'screenshot') {
        this.openScreenshotModal(item);
      } else if (item.type === 'video') {
        this.openVideoModal(item);
      }
    },
    getContentTitle(item) {
      if (item.type === 'guide') {
        return 'Guía: Información detallada creada por la comunidad';
      } else if (item.type === 'screenshot') {
        return 'Captura: Imagen subida por un usuario';
      } else if (item.type === 'video') {
        return 'Video: Video subido por un usuario';
      }
      return '';
    },
    handleImageError(event) {
      event.target.style.display = 'none';
      const placeholder = document.createElement('div');
      placeholder.className = 'no-image-placeholder';
      placeholder.textContent = 'Sin imagen';
      event.target.parentNode.appendChild(placeholder);
    },
    handleFileChange(event) {
      this.newScreenshot.image = event.target.files[0];
    },
    async uploadScreenshot() {
      if (!this.newScreenshot.image) {
        this.uploadError = 'Por favor, selecciona una imagen válida.';
        await Swal.fire({
          title: 'Error',
          text: this.uploadError,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
        return;
      }

      if (!this.newScreenshot.title.trim()) {
        this.uploadError = 'Por favor, proporciona un título para la captura.';
        await Swal.fire({
          title: 'Error',
          text: this.uploadError,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
        return;
      }

      const maxSize = 2048 * 1024;
      if (this.newScreenshot.image.size > maxSize) {
        this.uploadError = 'La imagen excede el tamaño máximo de 2MB.';
        await Swal.fire({
          title: 'Error',
          text: this.uploadError,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
        return;
      }

      if (!this.newScreenshot.image.type.startsWith('image/')) {
        this.uploadError = 'El archivo seleccionado no es una imagen válida.';
        await Swal.fire({
          title: 'Error',
          text: this.uploadError,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
        return;
      }

      const formData = new FormData();
      formData.append('title', this.newScreenshot.title);
      formData.append('image', this.newScreenshot.image);
      formData.append('description', this.newScreenshot.description || '');

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación. Por favor, inicia sesión nuevamente.');
        }
        await axios.post('/api/screenshots', formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data',
          },
        });
        this.showUploadForm = false;
        this.newScreenshot = { title: '', image: null, description: '' };
        this.uploadError = '';
        await this.fetchScreenshots();
        await Swal.fire({
          title: '¡Captura subida!',
          text: 'Tu captura ha sido subida exitosamente.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        let errorMessage = 'Error al subir la captura.';
        if (error.response) {
          errorMessage = error.response.data.message || `Error del servidor: ${error.response.status}`;
        } else if (error.request) {
          errorMessage = 'No se recibió respuesta del servidor. Verifica tu conexión.';
        } else {
          errorMessage = error.message;
        }
        this.uploadError = errorMessage;
        await Swal.fire({
          title: 'Error',
          text: this.uploadError,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
      }
    },
    handleVideoFileChange(event) {
      this.newVideo.video = event.target.files[0];
    },
    async uploadVideo() {
      if (!this.newVideo.video) {
        this.videoUploadError = 'Por favor, selecciona un video válido.';
        await Swal.fire({
          title: 'Error',
          text: this.videoUploadError,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
        return;
      }

      if (!this.newVideo.title.trim()) {
        this.videoUploadError = 'Por favor, proporciona un título para el video.';
        await Swal.fire({
          title: 'Error',
          text: this.videoUploadError,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
        return;
      }

      const maxSize = 10 * 1024 * 1024; // 10MB limit
      if (this.newVideo.video.size > maxSize) {
        this.videoUploadError = 'El video excede el tamaño máximo de 10MB.';
        await Swal.fire({
          title: 'Error',
          text: this.videoUploadError,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
        return;
      }

      if (!this.newVideo.video.type.startsWith('video/')) {
        this.videoUploadError = 'El archivo seleccionado no es un video válido.';
        await Swal.fire({
          title: 'Error',
          text: this.videoUploadError,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
        return;
      }

      const formData = new FormData();
      formData.append('title', this.newVideo.title);
      formData.append('video', this.newVideo.video);
      formData.append('description', this.newVideo.description || '');

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación. Por favor, inicia sesión nuevamente.');
        }
        await axios.post('/api/videos', formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data',
          },
        });
        this.showVideoUploadForm = false;
        this.newVideo = { title: '', video: null, description: '' };
        this.videoUploadError = '';
        await this.fetchVideos();
        await Swal.fire({
          title: '¡Video subido!',
          text: 'Tu video ha sido subido exitosamente.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        let errorMessage = 'Error al subir el video.';
        if (error.response) {
          errorMessage = error.response.data.message || `Error del servidor: ${error.response.status}`;
        } else if (error.request) {
          errorMessage = 'No se recibió respuesta del servidor. Verifica tu conexión.';
        } else {
          errorMessage = error.message;
        }
        this.videoUploadError = errorMessage;
        await Swal.fire({
          title: 'Error',
          text: this.videoUploadError,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
      }
    },
    async openScreenshotModal(screenshot) {
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        const headers = token ? { Authorization: `Bearer ${token}` } : {};
        const response = await axios.get(`/api/screenshots/${screenshot.id}`, { headers });
        this.selectedScreenshot = {
          ...response.data,
          image_url: `http://localhost:8000${response.data.image_url}`,
          type: 'screenshot',
          likesCount: response.data.likes_count || response.data.likes || 0,
          dislikesCount: response.data.dislikes_count || response.data.dislikes || 0,
          isLikedByUser: response.data.user_liked || false,
          isDislikedByUser: response.data.user_disliked || false,
        };
        console.log('selectedScreenshot al abrir modal:', this.selectedScreenshot);
        await this.fetchComments(screenshot.id);
      } catch (error) {
        console.error('Error al abrir el modal:', error);
        this.errorMessageScreenshots = 'No se pudo cargar la captura.';
      }
    },
    closeScreenshotModal() {
      this.selectedScreenshot = null;
      this.comments = [];
      this.newComment = '';
    },
    async fetchComments(screenshotId) {
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const response = await axios.get(`/api/screenshots/${screenshotId}/comments`);
        this.comments = response.data;
      } catch (error) {
        console.error('Error al cargar comentarios:', error);
        this.comments = [];
        this.errorMessageScreenshots = 'No se pudieron cargar los comentarios.';
      }
    },
    async submitComment() {
      if (!this.newComment.trim()) {
        await Swal.fire({
          title: 'Comentario vacío',
          text: 'Por favor, escribe un comentario antes de enviar.',
          icon: 'warning',
          confirmButtonColor: '#ff6f61',
        });
        return;
      }

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación.');
        }
        await axios.post(
          `/api/screenshots/${this.selectedScreenshot.id}/comments`,
          { text: this.newComment },
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        this.newComment = '';
        await this.fetchComments(this.selectedScreenshot.id);
        await Swal.fire({
          title: '¡Comentario enviado!',
          text: 'Tu comentario ha sido publicado exitosamente.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        console.error('Error al enviar comentario:', error);
        this.errorMessageScreenshots = error.response?.data?.message || 'Error al enviar el comentario.';
        await Swal.fire({
          title: 'Error',
          text: this.errorMessageScreenshots,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
      }
    },
    async toggleLike(screenshot) {
      if (!this.isAuthenticated) {
        await Swal.fire({
          title: 'Inicia sesión',
          text: 'Debes iniciar sesión para dar "Me gusta".',
          icon: 'warning',
          confirmButtonColor: '#ff6f61',
        });
        this.$router.push('/login');
        return;
      }

      // Prevenir múltiples clics mientras la solicitud está en curso
      if (this.isProcessing) return;
      this.isProcessing = true;

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación.');
        }

        const method = screenshot.isLikedByUser ? 'delete' : 'post';
        const response = await axios({
          method,
          url: `/api/screenshots/${screenshot.id}/like`,
          headers: { Authorization: `Bearer ${token}` },
        });

        // Actualizar directamente selectedScreenshot con la respuesta del backend
        this.selectedScreenshot = {
          ...this.selectedScreenshot,
          likesCount: response.data.likes,
          dislikesCount: response.data.dislikes,
          isLikedByUser: response.data.user_liked,
          isDislikedByUser: response.data.user_disliked,
        };

        console.log('Respuesta del backend:', response.data);
        console.log('selectedScreenshot actualizado:', this.selectedScreenshot);

        // Actualizar la lista de screenshots para mantener consistencia
        await this.fetchScreenshots();

        await Swal.fire({
          title: screenshot.isLikedByUser ? 'Me gusta eliminado' : '¡Me gusta añadido!',
          text: screenshot.isLikedByUser ? 'Has quitado tu "Me gusta".' : 'Has dado "Me gusta" a esta captura.',
          icon: 'success',
          timer: 1500,
          showConfirmButton: false,
        });
      } catch (error) {
        console.error('Error al dar Me gusta:', error);
        this.errorMessageScreenshots = error.response?.data?.message || 'Error al procesar tu Me gusta.';
        await Swal.fire({
          title: 'Error',
          text: this.errorMessageScreenshots,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
      } finally {
        this.isProcessing = false; // Restablecer la bandera
      }
    },

    async toggleDislike(screenshot) {
      if (!this.isAuthenticated) {
        await Swal.fire({
          title: 'Inicia sesión',
          text: 'Debes iniciar sesión para dar "No me gusta".',
          icon: 'warning',
          confirmButtonColor: '#ff6f61',
        });
        this.$router.push('/login');
        return;
      }

      if (this.isProcessing) return;
      this.isProcessing = true;

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación.');
        }

        const method = screenshot.isDislikedByUser ? 'delete' : 'post';
        const response = await axios({
          method,
          url: `/api/screenshots/${screenshot.id}/dislike`,
          headers: { Authorization: `Bearer ${token}` },
        });

        this.selectedScreenshot = {
          ...this.selectedScreenshot,
          likesCount: response.data.likes,
          dislikesCount: response.data.dislikes,
          isLikedByUser: response.data.user_liked,
          isDislikedByUser: response.data.user_disliked,
        };

        console.log('Respuesta del backend:', response.data);
        console.log('selectedScreenshot actualizado:', this.selectedScreenshot);

        await this.fetchScreenshots();

        await Swal.fire({
          title: screenshot.isDislikedByUser ? 'No me gusta eliminado' : '¡No me gusta añadido!',
          text: screenshot.isDislikedByUser ? 'Has quitado tu "No me gusta".' : 'Has dado "No me gusta" a esta captura.',
          icon: 'success',
          timer: 1500,
          showConfirmButton: false,
        });
      } catch (error) {
        console.error('Error al dar No me gusta:', error);
        this.errorMessageScreenshots = error.response?.data?.message || 'Error al procesar tu No me gusta.';
        await Swal.fire({
          title: 'Error',
          text: this.errorMessageScreenshots,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
      } finally {
        this.isProcessing = false;
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
    },
    async fetchVideoComments(videoId) {
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const response = await axios.get(`/api/videos/${videoId}/comments`);
        this.videoComments = response.data;
      } catch (error) {
        console.error('Error al cargar comentarios:', error);
        this.videoComments = [];
        this.errorMessageVideos = 'No se pudieron cargar los comentarios.';
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

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación.');
        }
        await axios.post(
          `/api/videos/${this.selectedVideo.id}/comments`,
          { text: this.newVideoComment },
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        this.newVideoComment = '';
        await this.fetchVideoComments(this.selectedVideo.id);
        await Swal.fire({
          title: '¡Comentario enviado!',
          text: 'Tu comentario ha sido publicado exitosamente.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        console.error('Error al enviar comentario:', error);
        this.errorMessageVideos = error.response?.data?.message || 'Error al enviar el comentario.';
        await Swal.fire({
          title: 'Error',
          text: this.errorMessageVideos,
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



  async deleteScreenshot(screenshotId) {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: '¿Quieres eliminar esta captura?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ff6f61',
      cancelButtonColor: '#2a2a3f',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
    });

    if (!result.isConfirmed) return;

    try {
      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      await axios.get('sanctum/csrf-cookie');
      const token = localStorage.getItem('auth_token');
      if (!token) {
        throw new Error('No se encontró el token de autenticación.');
      }
      await axios.delete(`/api/screenshots/${screenshotId}`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      this.screenshots = this.screenshots.filter(s => s.id !== screenshotId);
      this.errorMessageScreenshots = '';
      this.closeScreenshotModal();
      await Swal.fire({
        title: '¡Eliminado!',
        text: 'La captura ha sido eliminada.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
      });
      // Navigate to /comunidad/capturas after successful deletion
      if (this.$route.path !== '/comunidad/capturas') {
        this.$router.push('/comunidad/capturas');
      }
    } catch (error) {
      console.error('Error al eliminar captura:', error);
      this.errorMessageScreenshots = error.response
        ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
        : error.message;
      await Swal.fire({
        title: 'Error',
        text: `No se pudo eliminar la captura: ${this.errorMessageScreenshots}`,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
    }
  },
};
</script>

<style scoped>
/* [The styles remain unchanged from the previous version] */
.community-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #1e1e2f 0%, #2a2a3f 100%);
  color: #fff;
  font-family: 'Arial', sans-serif;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.community-container .content-section {
  border: 1px solid #3a3a4f;
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.community-container .content-section h2 {
  border-bottom: 2px solid #ff6f61;
  padding-bottom: 10px;
  margin-bottom: 20px;
  font-size: 1.8em;
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
  padding: 0 20px;
  margin: 0;
  display: flex;
  align-items: center;
}

.nav-container ul li {
  margin: 0 15px;
}

.nav-container ul li a,
.nav-container ul li button.logout-btn {
  text-decoration: none;
  color: #ff6f61;
  font-size: 1.2em;
  display: flex;
  align-items: center;
  transition: color 0.3s;
  background: none;
  border: none;
  cursor: pointer;
  position: relative;
}

.nav-container ul li a .icon,
.nav-container ul li button.logout-btn .icon {
  margin-right: 8px;
  font-size: 1.2em;
  vertical-align: middle;
  transition: none;
}

.nav-container ul li a:hover,
.nav-container ul li button.logout-btn:hover {
  color: #e63946;
}

.nav-container ul li a:hover .icon,
.nav-container ul li button.logout-btn:hover .icon {
  transform: none;
}

.nav-container ul li a:hover span,
.nav-container ul li button.logout-btn:hover span {
  transform: scale(1.1);
  display: inline-block;
}

.nav-container ul li a.active {
  color: #e63946;
  font-weight: bold;
  transform: none;
}

.nav-container ul li a.active .icon {
  transform: none;
}

.nav-container ul li a.active span {
  transform: scale(1.05);
  display: inline-block;
}

.coming-soon span {
  color: #999;
  font-style: italic;
  cursor: not-allowed;
}

.tabs-container {
  width: 85%;
  max-width: 900px;
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
  max-width: 900px;
  margin: 20px 0;
  flex-grow: 1;
}

.content-section {
  background: rgba(255, 255, 255, 0.95);
  padding: 20px;
  border-radius: 5px;
}

.content-section p {
  color: #666;
  font-size: 1em;
}

.todo-content {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.todo-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.todo-section {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.no-content-placeholder {
  text-align: center;
  padding: 20px;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 5px;
}

.no-content-placeholder p {
  color: #666;
  font-size: 1.1em;
  margin: 5px 0;
}

.content-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 15px;
  margin-top: 20px;
}

.content-card {
  background: rgba(255, 255, 255, 0.9);
  border-radius: 5px;
  overflow: hidden;
  transition: transform 0.3s;
  cursor: pointer;
}

.content-card:hover {
  transform: translateY(-5px);
}

.guides-header,
.screenshots-header,
.retransmissions-header,
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
  font-size: 1.8em;
}

.header-left p {
  color: #666;
  font-size: 1em;
  margin: 5px 0 0;
}

.header-right {
  display: flex;
  gap: 10px;
}

.filter-btn {
  background: #2a2a3f;
  color: #fff;
  padding: 8px 15px;
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

.create-guide-btn,
.upload-screenshot-btn,
.upload-video-btn {
  background: linear-gradient(90deg, #ff6f61, #ff9f1c);
  color: #fff;
  padding: 8px 15px;
  border: none;
  border-radius: 3px;
  cursor: pointer;
  font-size: 0.9em;
  display: flex;
  align-items: center;
  transition: background 0.3s;
  text-decoration: none;
}

.create-guide-btn .icon,
.upload-screenshot-btn .icon,
.upload-video-btn .icon {
  margin-right: 5px;
  font-size: 1.2em;
  vertical-align: middle;
  transition: none;
}

.create-guide-btn:hover,
.upload-screenshot-btn:hover,
.upload-video-btn:hover {
  background: linear-gradient(90deg, #e63946, #ff9f1c);
}

.no-guides-placeholder,
.no-screenshots-placeholder,
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
  font-size: 3em;
  color: #ff6f61;
  margin-bottom: 15px;
}

.no-guides-placeholder p,
.no-screenshots-placeholder p,
.no-videos-placeholder p {
  color: #666;
  font-size: 1.2em;
  margin: 5px 0;
}

.guides-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 15px;
  margin-top: 20px;
}

.guide-card {
  background: rgba(255, 255, 255, 0.9);
  border-radius: 5px;
  overflow: hidden;
  transition: transform 0.3s;
  cursor: pointer;
}

.guide-card:hover {
  transform: translateY(-5px);
}

.guide-card-image {
  width: 100%;
  height: 150px;
  background: #f0f0f0;
}

.guide-card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
  font-size: 1em;
  background: #f0f0f0;
}

.guide-card-content {
  padding: 10px;
}

.guide-card-content h3 {
  margin: 0 0 5px;
  color: #ff6f61;
  font-size: 1.3em;
}

.guide-description {
  color: #666;
  font-size: 0.9em;
  margin-bottom: 5px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.guide-meta {
  color: #2a9d8f;
  font-size: 0.8em;
  margin: 5px 0;
}

.screenshots-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 15px;
  margin-top: 20px;
}

.screenshot-card {
  background: rgba(255, 255, 255, 0.9);
  border-radius: 5px;
  overflow: hidden;
  transition: transform 0.3s;
  cursor: pointer;
}

.screenshot-card:hover {
  transform: translateY(-5px);
}

.screenshot-image {
  width: 100%;
  height: 150px;
  background: #f0f0f0;
}

.screenshot-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.screenshot-meta {
  margin: 11px 0 5px;
  color: #2a9d8f;
  font-size: 1.3em;
  text-align: center;
}

.videos-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
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

.video-thumbnail {
  width: 100%;
  height: 150px;
  background: #f0f0f0;
  position: relative;
}

.video-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.play-icon {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 2em;
  color: #ff6f61;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 50%;
  padding: 10px;
}

.video-meta {
  padding: 10px;
  color: #2a9d8f;
  font-size: 1.0em;
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

.screenshot-modal,
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
  max-width: 900px;
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

.modal-image,
.modal-video {
  flex: 1;
  padding: 20px;
}

.modal-image img {
  width: 100%;
  height: auto;
  max-height: 80vh;
  object-fit: contain;
  border-radius: 5px;
}

.video-player {
  width: 100%;
  max-height: 80vh;
  border-radius: 5px;
}

.modal-details {
  flex: 0.5;
  padding: 20px;
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

.modal-actions .action-btn.liked {
  background: #ff6f61;
  color: #fff;
}

.modal-actions .action-btn.liked:hover {
  background: #e63946;
}

.modal-actions .action-btn.dislike-btn.disliked {
  background: #d62828;
  color: #fff;
}

.modal-actions .action-btn.dislike-btn.disliked:hover {
  background: #e63946;
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

.submit-comment-btn:hover {
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

  .content-list,
  .guides-list,
  .screenshots-list,
  .videos-list {
    grid-template-columns: 1fr;
  }

  .modal-content {
    flex-direction: column;
    width: 95%;
  }

  .modal-image,
  .modal-video {
    padding: 10px;
  }

  .modal-details {
    border-radius: 0 0 10px 10px;
  }

  .modal-actions {
    flex-wrap: wrap;
  }

  .comments-list {
    max-height: 200px;
  }

  .todo-header {
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