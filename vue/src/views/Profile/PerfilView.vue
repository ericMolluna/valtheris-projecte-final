<template>
  <div class="profile-container">
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
        <li class="dropdown" data-testid="comunidad-item">
          <router-link to="/comunidad" class="dropdown-toggle">
            <i class="icon">👥</i> <span>Comunidad</span>
          </router-link>
        </li>
        <li>
          <router-link to="/perfil" class="active">
            <i class="icon">👤</i> <span>Perfil</span>
          </router-link>
        </li>
        <li v-if="isAuthenticated">
          <button @click="logout" class="logout-btn">
            <i class="icon">🚪</i> <span>Logout</span>
          </button>
        </li>
        <li v-else>
          <router-link to="/login">
            <i class="icon">🔑</i> <span>Login</span>
          </router-link>
        </li>
      </ul>
    </nav>

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
              <router-link to="/comunidad" class="create-screenshot-link" @click="checkScreenshotLimit">
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

export default {
  name: 'ProfileView',
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
        this.$router.push('/login');
      } catch (error) {
        console.error('Error during logout:', error);
        localStorage.removeItem('auth_token');
        this.isAuthenticated = false;
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

<style scoped>
.profile-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #1e1e2f 0%, #2a2a3f 100%);
  display: flex;
  flex-direction: column;
  font-family: 'Arial', sans-serif;
  width: 100%;
  box-sizing: border-box;
}

/* Barra de navegación */
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

/* Estilo para el mensaje "Próximamente" */
.coming-soon span {
  color: #999;
  font-style: italic;
  cursor: not-allowed;
}

/* User Header */
.user-header {
  width: 100%;
  max-width: 1200px;
  margin: 20px auto;
  background: rgba(255, 255, 255, 0.95);
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  box-sizing: border-box;
  padding-left: 20px;
  padding-right: 20px;
}

.user-avatar {
  position: relative;
  margin-right: 20px;
}

.avatar-img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #ff6f61;
}

.avatar-icon {
  font-size: 3em;
  color: #ff6f61;
}

.user-info {
  flex-grow: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.user-details {
  text-align: center;
}

.username {
  margin: 0;
  color: #ff6f61;
  font-size: 2em;
  display: flex;
  align-items: center;
  gap: 10px;
}

.tier-badge {
  padding: 5px 10px;
  border-radius: 15px;
  font-size: 0.6em;
  font-weight: bold;
  text-transform: uppercase;
}

.tier-1 {
  background: #666;
  color: #fff;
}

.tier-2 {
  background: #c0c0c0;
  color: #333;
}

.tier-3 {
  background: #ffd700;
  color: #333;
}

.edit-profile-btn {
  background: linear-gradient(90deg, #ff6f61, #ff9f1c);
  color: white;
  padding: 8px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1em;
  transition: transform 0.3s, background 0.3s;
}

.edit-profile-btn:hover {
  background: linear-gradient(90deg, #e63946, #ff9f1c);
  transform: scale(1.05);
}

/* Profile Content */
.profile-content {
  width: 100%;
  max-width: 1200px;
  margin: 20px auto;
  display: flex;
  gap: 20px;
  flex-direction: row-reverse;
  box-sizing: border-box;
  padding: 0 20px;
}

/* Statistics Section */
.statistics-section {
  width: 250px;
  background: rgba(255, 255, 255, 0.95);
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  align-self: flex-start;
  flex-shrink: 0;
}

.statistics-section h3 {
  color: #ff6f61;
  font-size: 1.5em;
  margin-top: 0;
}

.statistics-section .status {
  color: #2a9d8f;
  font-size: 1.1em;
  margin: 10px 0;
}

.stats p {
  color: #666;
  font-size: 1em;
  margin: 5px 0;
}

.stats p strong {
  color: #ff6f61;
}

/* Main Content */
.main-content {
  flex-grow: 1;
  box-sizing: border-box;
}

/* Navigation Tabs */
.tabs {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 20px;
  border-bottom: 2px solid rgba(255, 255, 255, 0.1);
}

.tabs button {
  background: #ffffff;
  color: #ff6f61;
  border: none;
  padding: 12px 20px;
  margin-right: 8px;
  border-radius: 8px 8px 0 0;
  cursor: pointer;
  font-size: 1.1em;
  font-weight: 500;
  transition: background 0.3s, color 0.3s, box-shadow 0.3s;
  position: relative;
  display: flex;
  align-items: center;
}

.tab-icon {
  margin-right: 8px;
  font-size: 1.2em;
  vertical-align: middle;
  transition: none;
}

.tabs button.active-tab {
  background: #ff6f61;
  color: #ffffff;
  font-weight: 600;
  box-shadow: 0 -2px 0 #ff6f61 inset;
}

.tabs button:hover {
  background: #ff9f1c;
  color: #ffffff;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

.tabs button:hover .tab-icon {
  transform: none;
}

.tabs button:hover span {
  transform: translateY(-2px);
  display: inline-block;
}

.tabs button.active-tab:hover {
  background: #e63946;
}

/* Content Sections */
.content-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.guides-section,
.screenshots-section,
.videos-section,
.subscription-section {
  background: rgba(255, 255, 255, 0.95);
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  box-sizing: border-box;
}

.guides-section h2,
.screenshots-section h2,
.videos-section h2,
.subscription-section h2 {
  margin-top: 0;
  color: #ff6f61;
  font-size: 1.8em;
}

/* Subscription Section */
.subscription-section p {
  color: #666;
  font-size: 1.1em;
}

.tier-options {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.tier-card {
  background: #f9f9f9;
  border-radius: 10px;
  padding: 15px;
  flex: 1;
  min-width: 200px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.tier-card h3 {
  color: #ff6f61;
  font-size: 1.3em;
  margin: 0 0 10px;
}

.tier-card p {
  color: #2a9d8f;
  font-size: 1em;
  margin: 5px 0;
}

.tier-card ul {
  list-style: none;
  padding: 0;
  margin: 10px 0;
}

.tier-card ul li {
  color: #666;
  font-size: 0.95em;
  margin: 5px 0;
}

.tier-card button {
  background: linear-gradient(90deg, #ff6f61, #ff9f1c);
  color: white;
  padding: 8px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1em;
  transition: transform 0.3s, background 0.3s;
  width: 100%;
}

.tier-card button:hover {
  background: linear-gradient(90deg, #e63946, #ff9f1c);
  transform: scale(1.05);
}

.tier-card button:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
}

.selected-tier {
  border: 2px solid #ff6f61;
  box-shadow: 0 4px 12px rgba(255, 111, 97, 0.3);
}

/* Placeholder for No Content */
.no-guides-placeholder,
.no-screenshots-placeholder,
.no-videos-placeholder {
  text-align: center;
  padding: 15px;
}

.no-guides-placeholder p,
.no-screenshots-placeholder p,
.no-videos-placeholder p {
  color: #666;
  font-size: 1.1em;
  margin: 5px 0;
}

.create-guide-link,
.create-screenshot-link,
.upload-video-link {
  display: inline-block;
  margin-top: 10px;
  background: linear-gradient(90deg, #ff6f61, #ff9f1c);
  color: white;
  padding: 8px 15px;
  border-radius: 5px;
  text-decoration: none;
  transition: transform 0.3s, background 0.3s;
}

.create-guide-link:hover,
.create-screenshot-link:hover,
.upload-video-link:hover {
  background: linear-gradient(90deg, #e63946, #ff9f1c);
  transform: scale(1.05);
}

/* Grid Layouts */
.guides-grid,
.screenshots-grid,
.videos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 15px;
}

.guide-card,
.screenshot-card,
.video-card {
  background: #f9f9f9;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s, box-shadow 0.3s;
  cursor: pointer;
}

.guide-card:hover,
.screenshot-card:hover,
.video-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
}

.guide-image,
.screenshot-image,
.video-image {
  width: 100%;
  height: 150px;
  overflow: hidden;
  position: relative;
  background: #000;
}

.guide-image img,
.screenshot-image img,
.video-image img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
}

.no-image-placeholder {
  width: 100%;
  height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
  font-size: 1em;
  background: #000;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
}

.screenshot-icon {
  font-size: 1.5em;
  margin-right: 5px;
}

.guide-content,
.screenshot-content,
.video-content {
  padding: 15px;
}

.guide-content h3,
.screenshot-content h3,
.video-content h3 {
  margin: 0 0 8px;
  color: #ff6f61;
  font-size: 1.3em;
}

.guide-meta,
.screenshot-meta,
.video-meta {
  color: #2a9d8f;
  font-size: 0.9em;
  margin: 0 0 8px;
}

.guide-description,
.screenshot-description,
.video-description {
  color: #666;
  font-size: 0.95em;
  margin: 0;
  line-height: 1.4;
}

.loading-message {
  text-align: center;
  color: #ff6f61;
  font-size: 1.1em;
}

.error-message {
  text-align: center;
  color: #e63946;
  font-size: 1.1em;
  font-weight: bold;
}

.success-message {
  text-align: center;
  color: #2a9d8f;
  font-size: 1.1em;
  font-weight: bold;
  margin-top: 10px;
}

/* Media Queries */
@media (max-width: 768px) {
  .user-header {
    flex-direction: column;
    align-items: flex-start;
    padding-left: 10px;
    padding-right: 10px;
  }

  .avatar-img {
    width: 80px;
    height: 80px;
  }

  .user-avatar {
    margin: 0 auto 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .user-info {
    flex-direction: column;
    align-items: center;
  }

  .edit-profile-btn {
    margin-top: 15px;
  }

  .profile-content {
    flex-direction: column;
    padding: 0 10px;
  }

  .statistics-section {
    width: 100%;
    align-self: center;
  }

  .tabs {
    flex-wrap: wrap;
  }

  .tabs button {
    padding: 8px 12px;
    font-size: 1em;
  }

  .guides-grid,
  .screenshots-grid,
  .videos-grid {
    grid-template-columns: 1fr;
  }

  .nav-container ul {
    padding: 0 10px;
  }

  .nav-container ul li {
    margin: 0 10px;
  }
}
</style>