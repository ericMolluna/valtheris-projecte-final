<template>
  <div class="guide-detail-container">
    <!-- Barra de navegación superior -->
    <nav class="nav-container">
      <div class="logo">
        <router-link to="/" class="logo-link">🎮 Valtheris</router-link>
      </div>
      <ul>
        <li data-testid="volver-item">
          <router-link to="/comunidad/guias">
            <i class="icon">📖</i> Volver a las guías
          </router-link>
        </li>
        <li class="dropdown" data-testid="comunidad-item">
          <router-link to="/comunidad" class="dropdown-toggle">
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

    <!-- Main Content -->
    <div class="guide-content-wrapper">
      <div v-if="loading" class="loading-message">Cargando guía...</div>
      <div v-else-if="errorMessage" class="error-message">{{ errorMessage }}</div>
      <div v-else-if="guide" class="guide-content">
        <div class="guide-header">
          <div class="guide-image-container">
            <img v-if="guide.image" :src="guide.image" alt="Portada de la guía" @error="handleImageError" />
            <div v-else class="no-image-placeholder">Sin imagen</div>
          </div>
          <div class="guide-title-container">
            <div class="title-and-actions">
              <h1 class="guide-title">{{ guide.title }}</h1>
              <!-- Botones de Editar y Eliminar -->
              <div class="header-actions" v-if="isAuthenticated && guide.createdBy === user?.name">
                <button class="action-btn edit-btn" @click="editGuide">
                  <span class="icon">✏️</span> Editar
                </button>
                <button class="action-btn delete-btn" @click="deleteGuide">
                  <span class="icon">🗑️</span> Eliminar
                </button>
              </div>
            </div>
            <div class="guide-rating">
              <span class="stars">{{ displayStars(averageRating) }}</span>
              <span class="rating-text">
                {{ averageRating > 0 ? `${averageRating.toFixed(1)}/5 (${ratings.length} valoraciones)` : 'No hay suficientes valoraciones' }}
              </span>
            </div>
            <p class="guide-author">Por {{ guide.createdBy || 'Anónimo' }}</p>
          </div>
        </div>

        <div class="guide-actions">
          <button 
            class="action-btn like-btn"
            :disabled="!isAuthenticated || guide.user_liked"
            @click="likeGuide"  
          >
            <span class="icon">👍</span> Me gusta ({{ guide.likes || 0 }})
          </button>
          <button 
            class="action-btn dislike-btn"
            :disabled="!isAuthenticated || guide.user_disliked"
            @click="dislikeGuide"
          >
            <span class="icon">👎</span> No me gusta ({{ guide.dislikes || 0 }})
          </button>
        </div>

        <!-- Contenido principal y metadatos -->
        <div class="guide-main-content">
          <div class="guide-body">
            <p class="guide-content-text">{{ guide.content }}</p>
          </div>
          <div class="guide-meta">
            <h3>Detalles</h3>
            <div class="meta-item">
              <span class="meta-label">Creado por:</span>
              <span class="meta-value">{{ guide.createdBy || 'Anónimo' }}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">Categoría:</span>
              <span class="meta-value">{{ guide.category || 'Sin categoría' }}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">Idioma:</span>
              <span class="meta-value">Español</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">Publicado:</span>
              <span class="meta-value">{{ formatDate(guide.created_at) }}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">Actualizado:</span>
              <span class="meta-value">{{ formatDate(guide.updated_at || guide.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { isAuthenticated } from '@/api/auth';
import '@/assets/styles/Guides/GuideView.css'; 

export default {
  data() {
    return {
      guide: null,
      loading: false,
      errorMessage: '',
      isAuthenticated: localStorage.getItem('auth_token') !== null,
      isFavorited: false,
      comments: [],
      newComment: '',
      ratings: [],
      userRating: 0,
      userReview: '',
      averageRating: 0,
      user: null,
    };
  },
  created() {
    window.addEventListener('storage', this.updateAuthStatus);
    this.checkAuthStatus();
    this.fetchGuide();
    this.loadComments();
    this.loadRatings();
    this.fetchUserData();
  },
  mounted() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
      toggle.addEventListener('click', this.toggleDropdown);
    });
    document.addEventListener('click', this.closeDropdown);
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.updateAuthStatus);
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
      toggle.removeEventListener('click', this.toggleDropdown);
    });
    document.removeEventListener('click', this.closeDropdown);
  },
  methods: {
    async checkAuthStatus() {
      this.isAuthenticated = await isAuthenticated();
      console.log('Estado de autenticación:', this.isAuthenticated);
    },
    updateAuthStatus() {
      this.isAuthenticated = localStorage.getItem('auth_token') !== null;
      console.log('Estado de autenticación actualizado:', this.isAuthenticated);
    },
    async fetchUserData() {
      try {
        const token = localStorage.getItem('auth_token');
        if (!token) {
          console.log('No hay token de autenticación, usuario no autenticado');
          return;
        }

        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        const response = await axios.get('/api/user');
        this.user = {
          name: response.data.name,
          email: response.data.email,
        };
        console.log('Datos del usuario cargados:', this.user);
      } catch (error) {
        this.errorMessage = error.response ? error.response.data.message : error.message;
        console.error('Error al cargar datos del usuario:', error);
      }
    },
    async fetchGuide() {
      this.loading = true;
      this.errorMessage = '';
      try {
        const guideId = this.$route.params.id;
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const response = await axios.get(`/api/guides/${guideId}`);
        this.guide = {
          ...response.data,
          likes: response.data.likes || 0,
          dislikes: response.data.dislikes || 0,
          user_liked: response.data.user_liked || false,
          user_disliked: response.data.user_disliked || false,
        };

        if (this.guide.user) {
          this.guide.createdBy =
            this.guide.user.name ||
            this.guide.user.username ||
            this.guide.user.email ||
            'Anónimo';
        } else {
          console.warn('No user data found for this guide:', this.guide);
          this.guide.createdBy = 'Anónimo';
        }
        console.log('Datos de la guía:', this.guide);
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Error al cargar la guía';
        console.error('Error al cargar la guía:', error);
      } finally {
        this.loading = false;
      }
    },
    async likeGuide() {
      if (!this.isAuthenticated) {
        alert('Debes iniciar sesión para dar "Me gusta".');
        return;
      }

      try {
        const guideId = this.$route.params.id;
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación.');
        }
        const response = await axios.post(`/api/guides/${guideId}/like`, {}, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        // Actualizar estado basado en la respuesta del backend
        this.guide.likes = response.data.likes;
        this.guide.dislikes = response.data.dislikes;
        this.guide.user_liked = response.data.user_liked;
        this.guide.user_disliked = response.data.user_disliked;
      } catch (error) {
        console.error('Error al dar "Me gusta":', error);
        const errorMsg = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        alert(`No se pudo dar "Me gusta": ${errorMsg}`);
      }
    },
    async dislikeGuide() {
      if (!this.isAuthenticated) {
        alert('Debes iniciar sesión para dar "No me gusta".');
        return;
      }

      try {
        const guideId = this.$route.params.id;
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación.');
        }
        const response = await axios.post(`/api/guides/${guideId}/dislike`, {}, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        // Actualizar estado basado en la respuesta del backend
        this.guide.likes = response.data.likes;
        this.guide.dislikes = response.data.dislikes;
        this.guide.user_liked = response.data.user_liked;
        this.guide.user_disliked = response.data.user_disliked;
      } catch (error) {
        console.error('Error al dar "No me gusta":', error);
        const errorMsg = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        alert(`No se pudo dar "No me gusta": ${errorMsg}`);
      }
    },
    handleImageError(event) {
      event.target.style.display = 'none';
      const placeholder = document.createElement('div');
      placeholder.className = 'no-image-placeholder';
      placeholder.textContent = 'Sin imagen disponible';
      event.target.parentNode.appendChild(placeholder);
    },
    goBackToGuides() {
      this.$router.push('/comunidad');
    },
    formatDate(dateString) {
      if (!dateString) return 'Fecha desconocida';
      const date = new Date(dateString);
      return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
    },
    displayStars(rating) {
      const fullStars = Math.floor(rating);
      const emptyStars = 5 - fullStars;
      return '★'.repeat(fullStars) + '☆'.repeat(emptyStars);
    },
    async loadRatings() {
      try {
        const guideId = this.$route.params.id;
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const response = await axios.get(`/api/guides/${guideId}/ratings`);
        this.ratings = response.data;
        this.calculateAverageRating();
      } catch (error) {
        console.error('Error al cargar las valoraciones:', error);
      }
    },
    calculateAverageRating() {
      if (this.ratings.length === 0) {
        this.averageRating = 0;
        return;
      }
      const total = this.ratings.reduce((sum, rating) => sum + rating.rating, 0);
      this.averageRating = total / this.ratings.length;
    },
    setUserRating(star) {
      this.userRating = star;
    },
    async submitRating() {
      if (this.userRating === 0 || !this.userReview.trim()) return;
      try {
        const guideId = this.$route.params.id;
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const response = await axios.post(`/api/guides/${guideId}/ratings`, {
          rating: this.userRating,
          text: this.userReview,
        }, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
          },
        });
        this.ratings.push({
          id: response.data.id,
          author: this.guide.createdBy || 'Usuario',
          rating: this.userRating,
          text: this.userReview,
          created_at: new Date().toISOString(),
        });
        this.userRating = 0;
        this.userReview = '';
        this.calculateAverageRating();
        console.log('Valoración enviada:', response.data);
      } catch (error) {
        console.error('Error al enviar la valoración:', error);
        alert('Error al enviar la valoración. Por favor, intenta de nuevo.');
      }
    },
    toggleFavorite() {
      this.isFavorited = !this.isFavorited;
      console.log(`Guía ${this.isFavorited ? 'agregada a' : 'eliminada de'} favoritos`);
    },
    shareGuide() {
      const url = window.location.href;
      navigator.clipboard.writeText(url).then(() => {
        alert('Enlace copiado al portapapeles: ' + url);
      }).catch(err => {
        console.error('Error al copiar el enlace:', err);
        alert('No se pudo copiar el enlace. Por favor, copia manualmente: ' + url);
      });
    },
    loadComments() {
      // Simulación de carga de comentarios (puedes reemplazar con una llamada al backend)
    },
    submitComment() {
      if (!this.newComment.trim()) return;
      const newComment = {
        id: this.comments.length + 1,
        author: this.isAuthenticated ? (this.guide?.createdBy || 'Usuario') : 'Anónimo',
        text: this.newComment,
        created_at: new Date().toISOString(),
      };
      this.comments.push(newComment);
      this.newComment = '';
      console.log('Comentario enviado:', newComment);
    },
    editGuide() {
      this.$router.push(`/comunidad/crear-guia?editGuide=${this.guide.id}`);
    },
    async deleteGuide() {
      if (confirm('¿Estás seguro de que quieres eliminar esta guía?')) {
        try {
          const guideId = this.$route.params.id;
          axios.defaults.baseURL = 'http://localhost:8000';
          axios.defaults.withCredentials = true;
          await axios.get('sanctum/csrf-cookie');
          const response = await axios.delete(`/api/guides/${guideId}`, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
            },
          });
          console.log('Guía eliminada:', response.data);
          this.$router.push('/comunidad/guias');
        } catch (error) {
          this.errorMessage = error.response ? error.response.data.message : 'Error al eliminar la guía';
          console.error('Error al eliminar la guía:', error);
        }
      }
    },
    toggleDropdown(event) {
      const dropdown = event.currentTarget.parentElement.querySelector('.dropdown-menu');
      if (dropdown) {
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
      }
    },
    closeDropdown(event) {
      const dropdowns = document.querySelectorAll('.dropdown-menu');
      dropdowns.forEach(dropdown => {
        if (!event.target.closest('.dropdown')) {
          dropdown.style.display = 'none';
        }
      });
    },
  },
};
</script>