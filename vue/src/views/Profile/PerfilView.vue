<template>
  <div class="profile-container">
    <!-- Barra de navegación superior -->
    <NavBar />

    <!-- User Header -->
    <div class="user-header">
      <div class="user-avatar">
        <img v-if="user?.avatar" :src="user.avatar" alt="User Avatar" class="avatar-img" @error="handleAvatarError" />
        <i v-else class="avatar-icon">👤</i>
      </div>
      <div class="user-info">
        <div class="user-details">
          <h1 class="username">
            {{ user?.name || 'Usuario' }}
            <span :class="tierBadgeClass">{{ userTier }}</span>
          </h1>
        </div>
        <button class="edit-profile-btn" @click="editProfile">
          Editar Perfil
        </button>
      </div>
    </div>

    <!-- Main Content with Statistics -->
    <div class="profile-content">
      <!-- Main Content -->
      <div class="main-content">
        <!-- Navigation Tabs -->
        <div class="tabs">
          <button :class="{ 'active-tab': activeTab === 'guides' }" @click="activeTab = 'guides'">
            <i class="tab-icon">📖</i> <span>Guías</span>
          </button>
          <button :class="{ 'active-tab': activeTab === 'screenshots' }" @click="activeTab = 'screenshots'">
            <i class="tab-icon">📸</i> <span>Capturas</span>
          </button>
          <button :class="{ 'active-tab': activeTab === 'videos' }" @click="activeTab = 'videos'">
            <i class="tab-icon">🎥</i> <span>Videos</span>
          </button>
          <button :class="{ 'active-tab': activeTab === 'subscription' }" @click="activeTab = 'subscription'">
            <i class="tab-icon">💎</i> <span>Suscripción</span>
          </button>
        </div>

        <!-- Content Sections -->
        <div class="content-section">
          <!-- Sección de Guías -->
          <div class="guides-section" v-if="activeTab === 'guides'">
            <h2>Mis Guías</h2>
            <div v-if="loadingGuides" class="loading-message">
              Cargando guías...
            </div>
            <div v-else-if="errorMessage" class="error-message">
              {{ errorMessage }}
            </div>
            <div v-else-if="userGuides.length === 0" class="no-guides-placeholder">
              <p>No has creado ninguna guía todavía.</p>
              <router-link to="/comunidad" class="create-guide-link" @click="checkGuideLimit">
                Crear una Guía
              </router-link>
            </div>
            <div v-else class="guides-grid">
              <div class="guide-card" v-for="guide in userGuides" :key="guide.id" @click="viewGuide(guide)">
                <div class="guide-image">
                  <img :src="guide.image" alt="Portada de la guía" @error="handleImageError" />
                </div>
                <div class="guide-content">
                  <h3>{{ guide.title }}</h3>
                  <p class="guide-description">Descripción: {{ truncateDescription(guide.description, 100) }}</p>
                  <p class="guide-meta"><strong>Categoría:</strong> {{ guide.category || 'Sin categoría' }}</p>
                </div>
              </div>
            </div>
            <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
          </div>

          <!-- Sección de Capturas -->
          <div class="screenshots-section" v-if="activeTab === 'screenshots'">
            <h2>Mis Capturas</h2>
            <div v-if="loadingScreenshots" class="loading-message">Cargando capturas...</div>
            <div v-else-if="errorMessage" class="error-message">{{ errorMessage }}</div>
            <div v-else-if="userScreenshots.length === 0" class="no-screenshots-placeholder">
              <p>No has subido ninguna captura todavía.</p>
              <router-link to="/comunidad/capturas" class="create-screenshot-link" @click="checkScreenshotLimit">
                Subir una Captura
              </router-link>
            </div>
            <div v-else class="screenshots-grid">
              <div class="screenshot-card" v-for="screenshot in userScreenshots" :key="screenshot.id"
                @click="viewScreenshot(screenshot)">
                <div class="screenshot-image">
                  <img :src="screenshot.image_url" alt="Captura" @error="handleImageError" />
                </div>
                <div class="screenshot-content">
                  <h3>{{ screenshot.title || 'Sin título' }}</h3>
                  <p class="screenshot-meta"><strong>Fecha:</strong> {{ formatDate(screenshot.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sección de Videos -->
          <div class="videos-section" v-if="activeTab === 'videos'">
            <h2>Mis Videos</h2>
            <div v-if="loadingVideos" class="loading-message">Cargando videos...</div>
            <div v-else-if="errorMessage" class="error-message">{{ errorMessage }}</div>
            <div v-else-if="userVideos.length === 0" class="no-videos-placeholder">
              <p>No has subido ningún video todavía.</p>
              <router-link to="/videos" class="upload-video-link">
                Subir un Video
              </router-link>
            </div>
            <div v-else class="videos-grid">
              <div class="video-card" v-for="video in userVideos" :key="video.id" @click="viewVideo(video)">
                <div class="video-image">
                  <img :src="video.thumbnail_url" alt="Miniatura del video" @error="handleImageError" />
                </div>
                <div class="video-content">
                  <h3>{{ video.title || 'Sin título' }}</h3>
                  <p class="video-meta"><strong>Fecha:</strong> {{ formatDate(video.created_at) }}</p>
                  <p class="video-meta"><strong>Likes:</strong> {{ video.likes || 0 }}</p>
                  <p class="video-meta"><strong>Dislikes:</strong> {{ video.dislikes || 0 }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sección de Suscripción -->
          <div class="subscription-section" v-if="activeTab === 'subscription'">
            <h2>Gestión de Suscripción</h2>
            <p>Tu suscripción actual: <span :class="tierBadgeClass">{{ userTier }}</span></p>
            <div class="tier-options">
              <div class="tier-card" :class="{ 'selected-tier': userTier === 'Tier 1' }">
                <h3>Tier 1 (Básico)</h3>
                <p>Gratuito</p>
                <ul>
                  <li>Acceso básico a la comunidad</li>
                </ul>
                <button @click="changeTier('Tier 1')" :disabled="userTier === 'Tier 1'">Seleccionar</button>
              </div>
              <div class="tier-card" :class="{ 'selected-tier': userTier === 'Tier 2' }">
                <h3>Tier 2 (Avanzado)</h3>
                <p>$5/mes</p>
                <ul>
                  <li>Distintivo plateado</li>
                  <li>Acceso prioritario a contenido</li>
                </ul>
                <button @click="changeTier('Tier 2')" :disabled="userTier === 'Tier 2'">Seleccionar</button>
              </div>
              <div class="tier-card" :class="{ 'selected-tier': userTier === 'Tier 3' }">
                <h3>Tier 3 (Premium)</h3>
                <p>$10/mes</p>
                <ul>
                  <li>Distintivo dorado</li>
                  <li>Soporte prioritario</li>
                  <li>Acceso prioritario a contenido</li>
                </ul>
                <button @click="changeTier('Tier 3')" :disabled="userTier === 'Tier 3'">Seleccionar</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistics Section -->
      <div class="statistics-section">
        <h3>Estadísticas</h3>
        <p class="status">{{ user?.status || 'En línea' }}</p>
        <div class="stats" v-if="user">
          <p><strong>Capturas:</strong> {{ userScreenshots.length }}</p>
          <p><strong>Videos:</strong> {{ userVideos.length }}</p>
          <p><strong>Guías:</strong> {{ userGuides.length }}</p>
          <p><strong>Suscripción:</strong> <span :class="tierBadgeClass">{{ userTier }}</span></p>
        </div>
        <div class="stats" v-else>
          <p>Cargando estadísticas...</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { isAuthenticated } from '@/api/auth';
import NavBar from '@/components/NavBar.vue'; // Importa el componente NavBar

export default {
  name: 'ProfileView',
  components: {
    NavBar, // Registra el componente NavBar
  },
  data() {
    return {
      isAuthenticated: localStorage.getItem('auth_token') !== null,
      activeTab: 'guides',
      user: null,
      userGuides: [],
      userScreenshots: [],
      userVideos: [],
      loadingGuides: false,
      loadingScreenshots: false,
      loadingVideos: false,
      errorMessage: '',
      successMessage: '',
      userTier: 'Tier 1',
      tierLimits: {
        'Tier 1': { guides: 5, screenshots: 5 },
        'Tier 2': { guides: 20, screenshots: 20 },
        'Tier 3': { guides: Infinity, screenshots: Infinity },
      },
    };
  },
  computed: {
    tierBadgeClass() {
      return {
        'tier-badge': true,
        'tier-1': this.userTier === 'Tier 1',
        'tier-2': this.userTier === 'Tier 2',
        'tier-3': this.userTier === 'Tier 3',
      };
    },
  },
  async created() {
    window.addEventListener('storage', this.updateAuthStatus);
    const isAuth = await this.checkAuthStatus();
    if (isAuth) {
      const userLoaded = await this.fetchUserData();
      if (userLoaded) {
        await Promise.all([this.fetchGuides(), this.fetchScreenshots(), this.fetchVideos()]);
      } else {
        this.$router.push('/login');
      }
    } else {
      this.$router.push('/login');
    }
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
      return this.isAuthenticated;
    },
    updateAuthStatus() {
      this.isAuthenticated = localStorage.getItem('auth_token') !== null;
      // Emitir evento para sincronizar con NavBar
      window.dispatchEvent(new Event('authChange'));
    },
    async fetchUserData() {
      try {
        const token = localStorage.getItem('auth_token');
        if (!token) {
          console.log('No hay token de autenticación');
          return false;
        }

        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

        await axios.get('sanctum/csrf-cookie');
        const response = await axios.get('/api/user');

        this.user = {
          name: response.data.name,
          email: response.data.email,
          avatar: response.data.avatar ? `http://localhost:8000${response.data.avatar}` : null,
          screenshots: response.data.screenshots || 0,
          inventory: response.data.inventory || 0,
          workshopItems: response.data.workshopItems || 0,
          tier: response.data.tier || 'Tier 1',
        };
        this.userTier = this.user.tier;
        return true;
      } catch (error) {
        console.error('Error en fetchUserData:', error);
        if (error.response) {
          this.errorMessage = `Error al cargar datos del usuario: ${error.response.data.message || error.response.statusText}`;
          if (error.response.status === 401) {
            localStorage.removeItem('auth_token');
            this.isAuthenticated = false;
            return false;
          }
        } else {
          this.errorMessage = 'Error al conectar con el servidor. Por favor, intenta de nuevo.';
        }
        return false;
      }
    },
    async fetchGuides() {
      this.loadingGuides = true;
      this.errorMessage = '';
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        const response = await axios.get('/api/guides', {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        this.userGuides = response.data;
      } catch (error) {
        const errorMsg = error.response
          ? `${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        this.errorMessage = errorMsg;
        this.userGuides = [];
      } finally {
        this.loadingGuides = false;
      }
    },
    async fetchScreenshots() {
      this.loadingScreenshots = true;
      this.errorMessage = '';
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        const response = await axios.get('/api/screenshots?user=me', {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        this.userScreenshots = response.data.map(screenshot => ({
          ...screenshot,
          image_url: screenshot.image_url.startsWith('http')
            ? screenshot.image_url
            : `http://localhost:8000${screenshot.image_url}`,
        }));
      } catch (error) {
        const errorMsg = error.response
          ? `${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        this.errorMessage = errorMsg;
        this.userScreenshots = [];
      } finally {
        this.loadingScreenshots = false;
      }
    },
    async fetchVideos() {
      this.loadingVideos = true;
      this.errorMessage = '';
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        const response = await axios.get('/api/videos?user=me', {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        console.log('Raw API response for videos:', response.data); // Debug
        this.userVideos = response.data.map(video => ({
          ...video,
          video_url: video.video_url.startsWith('http')
            ? video.video_url
            : `http://localhost:8000${video.video_url}`,
          thumbnail_url: video.thumbnail_url
            ? (video.thumbnail_url.startsWith('http')
              ? video.thumbnail_url
              : `http://localhost:8000${video.thumbnail_url}`)
            : null,
        }));
        console.log('Processed user videos:', this.userVideos); // Debug
        this.user.videos = this.userVideos.length; // Update statistics
      } catch (error) {
        const errorMsg = error.response
          ? `${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        this.errorMessage = errorMsg;
        this.userVideos = [];
      } finally {
        this.loadingVideos = false;
      }
    },
    handleAvatarError(event) {
      event.target.style.display = 'none';
      const placeholder = document.createElement('i');
      placeholder.className = 'avatar-icon';
      placeholder.textContent = '👤';
      event.target.parentNode.appendChild(placeholder);
    },
    viewGuide(guide) {
      this.$router.push(`/comunidad/guia/${guide.id}`);
    },
    viewScreenshot(screenshot) {
      this.$router.push(`/capturas/${screenshot.id}`);
    },
    viewVideo(video) {
      this.$router.push(`/videos/${video.id}`);
    },
    handleImageError(event) {
      event.target.style.display = 'none';
      const placeholder = document.createElement('div');
      placeholder.className = 'no-image-placeholder';
      placeholder.innerHTML = '<i class="screenshot-icon">📸</i> Imagen no disponible';
      event.target.parentNode.appendChild(placeholder);
    },
    truncateDescription(description, maxLength) {
      if (!description) return 'Sin descripción';
      return description.length > maxLength
        ? description.substring(0, maxLength) + '...'
        : description;
    },
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
    },
    editProfile() {
      this.$router.push('/perfil/editar');
    },
    async logout() {
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          localStorage.removeItem('auth_token');
          this.isAuthenticated = false;
          this.$router.push('/login');
          return;
        }
        await axios.post('/logout');
        localStorage.removeItem('auth_token');
        this.isAuthenticated = false;
        window.dispatchEvent(new Event('authChange')); // Notificar a NavBar
        this.$router.push('/login');
      } catch (error) {
        console.error('Error during logout:', error);
        localStorage.removeItem('auth_token');
        this.isAuthenticated = false;
        window.dispatchEvent(new Event('authChange')); // Notificar a NavBar
        this.$router.push('/login');
      }
    },
    checkGuideLimit(event) {
      const maxGuides = this.tierLimits[this.userTier].guides;
      if (this.userGuides.length >= maxGuides) {
        event.preventDefault();
        this.errorMessage = `Has alcanzado el límite de guías (${maxGuides}) para tu suscripción (${this.userTier}). ¡Actualiza tu suscripción para crear más!`;
      }
    },
    checkScreenshotLimit(event) {
      const maxScreenshots = this.tierLimits[this.userTier].screenshots;
      if (this.userScreenshots.length >= maxScreenshots) {
        event.preventDefault();
        this.errorMessage = `Has alcanzado el límite de capturas (${maxScreenshots}) para tu suscripción (${this.userTier}). ¡Actualiza tu suscripción para subir más!`;
      }
    },
    changeTier(tier) {
      this.userTier = tier;
      this.user.tier = tier;
      this.successMessage = `¡Suscripción actualizada a ${tier}!`;
      this.errorMessage = '';
    },
  },
};
</script>
<style src="@/assets/styles/Profile/PerfilView.css" scoped></style>