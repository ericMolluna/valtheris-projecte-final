<template>
  <div class="menu-container">
    <!-- Barra de navegación integrada -->
    <nav class="nav-container">
      <div class="logo">
        <router-link to="/" class="logo-link">🎮 Valtheris</router-link>
      </div>
      <ul>
        <li v-if="userTier === 'tier3'">
          <router-link to="/juego">
            <i class="icon">🎮</i> Jugar
          </router-link>
        </li>
        <li v-else class="coming-soon">
          <span><i class="icon">🎮</i> Próximamente</span>
        </li>
        <li>
          <router-link to="/comunidad">
            <i class="icon">👥</i> Comunidad
          </router-link>
        </li>
        <li>
          <router-link :to="isAuthenticated ? '/perfil' : '/login'">
            <i class="icon">{{ isAuthenticated ? '👤' : '🔑' }}</i>
            {{ isAuthenticated ? 'Perfil' : 'Login' }}
          </router-link>
        </li>
      </ul>
    </nav>

    <!-- Título animado -->
    <div class="title-section">
      <h1 class="animated-title">Bienvenido a Valtheris</h1>
      <p class="subtitle">¡Tu aventura comienza aquí!</p>
    </div>

    <!-- Reseñas -->
    <div class="reviews-section">
      <h2>Reseñas de la comunidad</h2>
      <div class="reviews-grid">
        <div class="review-card">
          <p class="review-text">"¡Un juego increíble, no puedo parar de jugar!"</p>
          <span class="review-author">- Gamer123</span>
        </div>
        <div class="review-card">
          <p class="review-text">"Gráficos espectaculares y una historia envolvente."</p>
          <span class="review-author">- ProPlayer99</span>
        </div>
      </div>
    </div>

    <!-- Guías -->
    <div class="guides-section">
      <h2>Guías destacadas</h2>
      <div class="guides-grid">
        <button class="guide-button">Guía para principiantes</button>
        <button class="guide-button">Consejos avanzados</button>
        <button class="guide-button">Secretos del juego</button>
      </div>
    </div>

    <!-- Footer -->
    <footer class="footer-section">
      <div class="footer-content">
        <p>© 2025 GameHub - Todos los derechos reservados</p>
        <div class="social-links">
          <a href="#" class="social-icon">🐦</a>
          <a href="#" class="social-icon">📘</a>
          <a href="#" class="social-icon">📷</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
import { isAuthenticated } from '@/api/auth';
import axios from 'axios';

export default {
  name: 'MenuView',
  data() {
    return {
      isAuthenticated: localStorage.getItem('auth_token') !== null,
      userTier: null,
    };
  },
  created() {
    window.addEventListener('storage', this.updateAuthStatus);
    this.checkAuthStatus();
    this.fetchUserData();
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.updateAuthStatus);
  },
  methods: {
    async checkAuthStatus() {
      this.isAuthenticated = await isAuthenticated();
    },
    async fetchUserData() {
      try {
        const token = localStorage.getItem('auth_token');
        if (!token) return;

        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        const response = await axios.get('/api/user');
        this.userTier = response.data.tier || 'tier1';
      } catch (error) {
        console.error('Error al obtener datos del usuario:', error);
        this.userTier = 'tier1';
        localStorage.removeItem('auth_token');
        this.isAuthenticated = false;
      }
    },
    updateAuthStatus() {
      this.isAuthenticated = localStorage.getItem('auth_token') !== null;
      if (!this.isAuthenticated) {
        this.userTier = null;
      }
    },
  },
  mounted() {
    console.log('Vista de menú cargada correctamente');
  },
};
</script>

<style scoped>
.menu-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #1e1e2f 0%, #2a2a3f 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  font-family: 'Arial', sans-serif;
}

/* ----- NAVIGATION BAR: NO TOCAR ----- */
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

.coming-soon span {
  color: #999;
  font-style: italic;
  cursor: not-allowed;
}

/* ----- TÍTULO ----- */
.title-section {
  background: linear-gradient(90deg, #ff6f61, #ff9f1c);
  color: white;
  text-align: center;
  padding: 40px 20px;
  width: 85%;
  max-width: 900px;
  margin: 30px 0;
  border-radius: 10px;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
}

.animated-title {
  margin: 0;
  font-size: 2.5em;
  animation: fadeIn 1.5s ease-in-out;
  color: #1e1e2f;
  text-shadow: 1px 1px 4px rgba(255, 255, 255, 0.6);
}

.subtitle {
  margin: 10px 0 0;
  font-size: 1.2em;
  opacity: 0.9;
  color: #1e1e2f;
}

@keyframes fadeIn {
  0% {
    opacity: 0;
    transform: translateY(-20px);
  }

  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ----- RESEÑAS ----- */
.reviews-section {
  background-color: rgba(255, 255, 255, 0.95);
  text-align: center;
  padding: 30px;
  width: 85%;
  max-width: 900px;
  margin: 30px 0;
  border-radius: 10px;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
}

.reviews-section h2 {
  margin: 0 0 20px;
  font-size: 1.8em;
  color: #1e1e2f;
}

.reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.review-card {
  background: #f5f5f5;
  padding: 20px;
  border-radius: 8px;
  transition: transform 0.3s;
}

.review-card:hover {
  transform: translateY(-5px);
}

.review-text {
  margin: 0;
  font-style: italic;
  color: #333;
}

.review-author {
  display: block;
  margin-top: 10px;
  font-size: 0.9em;
  color: #666;
}

/* ----- GUÍAS ----- */
.guides-section {
  background: linear-gradient(90deg, #ff6f61, #ff9f1c);
  color: white;
  text-align: center;
  padding: 30px;
  width: 85%;
  max-width: 900px;
  margin: 30px 0;
  border-radius: 10px;
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
}

.guides-section h2 {
  margin: 0 0 20px;
  font-size: 1.8em;
}

.guides-grid {
  display: flex;
  justify-content: center;
  gap: 20px;
  flex-wrap: wrap;
}

.guide-button {
  background-color: #ffffff;
  color: #ff6f61;
  border: none;
  padding: 12px 24px;
  font-size: 1.1em;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.3s;
}

.guide-button:hover {
  background-color: #e63946;
  color: white;
  transform: scale(1.05);
}

/* ----- FOOTER ----- */
.footer-section {
  background: linear-gradient(90deg, #2a9d8f, #1e1e2f);
  color: white;
  text-align: center;
  padding: 20px;
  width: 100%;
  margin-top: auto;
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 900px;
  margin: 0 auto;
}

.social-links {
  display: flex;
  gap: 15px;
}

.social-icon {
  text-decoration: none;
  color: white;
  font-size: 1.5em;
  transition: color 0.3s;
}

.social-icon:hover {
  color: #ff6f61;
}

/* Responsive */
@media (max-width: 768px) {
  .nav-container ul {
    padding: 0 10px;
  }

  .nav-container ul li {
    margin: 0 10px;
  }

  .footer-content {
    flex-direction: column;
    gap: 10px;
  }
}
</style>
