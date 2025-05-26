<template>
  <div class="community-container">
    <!-- Navbar -->
    <NavBar :items="navItems" :isAuthenticated="isAuthenticated" :userTier="userTier" />

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

    <!-- Screenshot Modal -->
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

    <!-- Footer -->
    <FooterSection />
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import { isAuthenticated } from '@/api/auth';
import NavBar from '@/components/NavBar.vue';
import FooterSection from '@/components/FooterSection.vue';

export default {
  name: 'CommunityView',
  components: {
    NavBar,
    FooterSection,
  },
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
      newScreenshot: { title: '', image: null, description: '' },
      uploadError: '',
      selectedScreenshot: null,
      comments: [],
      newComment: '',
      videos: [],
      loadingVideos: false,
      errorMessageVideos: '',
      sortByVideos: 'recent',
      showVideoUploadForm: false,
      newVideo: { title: '', video: null, description: '' },
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
      isProcessing: false,
    };
  },
  computed: {
    navItems() {
      const baseItems = [
        { label: 'Inicio', route: '/', icon: '🏠' },
        { label: 'Comunidad', route: '/comunidad', icon: '👥' },
      ];
      const authItem = this.isAuthenticated
        ? { label: 'Perfil', route: '/perfil', icon: '👤' }
        : { label: 'Login', route: '/login', icon: '🔑' };
      return [...baseItems, authItem];
    },
    sortedGuides() {
      if (this.sortByGuides === 'popular') {
        return [...this.guides].sort((a, b) => (b.likes || 0) - (a.likes || 0));
      }
      return [...this.guides].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
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
    isAuthenticated() {
      // Force re-render of navItems when authentication status changes
      this.$forceUpdate();
    },
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.updateAuthStatus);
  },
  methods: {
    async handleLogout() {
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (token) {
          await axios.post('/api/logout', {}, {
            headers: { Authorization: `Bearer ${token}` },
          });
        }
        localStorage.removeItem('auth_token');
        this.isAuthenticated = false;
        this.user = null;
        this.userTier = 'Tier 1';
        this.$router.push('/login');
        await Swal.fire({
          title: 'Sesión cerrada',
          text: 'Has cerrado sesión exitosamente.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        console.error('Error en logout:', error);
        await Swal.fire({
          title: 'Error',
          text: 'No se pudo cerrar la sesión.',
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
      }
    },
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
          return {
            ...video,
            video_url: `http://localhost:8000${video.video_url}`,
            thumbnail_url: thumbnailUrl,
            type: 'video',
          };
        });
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
          throw new Error('No se encontró el token de autenticación.');
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
          errorMessage = 'No se recibió respuesta del servidor.';
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
      const maxSize = 10 * 1024 * 1024;
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
          throw new Error('No se encontró el token de autenticación.');
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
          errorMessage = 'No se recibió respuesta del servidor.';
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
        await this.fetchComments(screenshot.id);
      } catch (error) {
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
            headers: { Authorization: `Bearer ${token}` },
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
        this.selectedScreenshot = {
          ...this.selectedScreenshot,
          likesCount: response.data.likes,
          dislikesCount: response.data.dislikes,
          isLikedByUser: response.data.user_liked,
          isDislikedByUser: response.data.user_disliked,
        };
        await this.fetchScreenshots();
        await Swal.fire({
          title: screenshot.isLikedByUser ? 'Me gusta eliminado' : '¡Me gusta añadido!',
          text: screenshot.isLikedByUser ? 'Has quitado tu "Me gusta".' : 'Has dado "Me gusta" a esta captura.',
          icon: 'success',
          timer: 1500,
          showConfirmButton: false,
        });
      } catch (error) {
        this.errorMessageScreenshots = error.response?.data?.message || 'Error al procesar tu Me gusta.';
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
        await this.fetchScreenshots();
        await Swal.fire({
          title: screenshot.isDislikedByUser ? 'No me gusta eliminado' : '¡No me gusta añadido!',
          text: screenshot.isDislikedByUser ? 'Has quitado tu "No me gusta".' : 'Has dado "No me gusta" a esta captura.',
          icon: 'success',
          timer: 1500,
          showConfirmButton: false,
        });
      } catch (error) {
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
            headers: { Authorization: `Bearer ${token}` },
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
        this.errorMessageVideos = error.response?.data?.message || 'Error al enviar el comentario.';
        await Swal.fire({
          title: 'Error',
          text: this.errorMessageVideos,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
      }
    },
    async deleteScreenshot(screenshotId) {
      const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Quieres eliminar esta captura?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff6f61',
        cancelButtonColor: '#2a3f2a',
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
          headers: { Authorization: `Bearer ${token}` },
        });
        this.screenshots = this.screenshots.filter(s => s.id !== screenshotId);
        this.closeScreenshotModal();
        await Swal.fire({
          title: '¡Eliminado!',
          text: 'La captura ha sido eliminada.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
        if (this.$route.path !== '/comunidad/capturas') {
          this.$router.push('/comunidad/capturas');
        }
      } catch (error) {
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
  },
};
</script>

<style src="@/assets/styles/Guides/CommunityView.css" scoped></style>
