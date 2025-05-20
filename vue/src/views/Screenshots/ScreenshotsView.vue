<template>
  <div class="screenshots-container">
    <!-- Barra de navegación superior -->
    <nav class="nav-container">
      <div class="logo">
        <router-link to="/" class="logo-link">🎮 Valtheris</router-link>
      </div>
      <ul>
        <li>
          <router-link to="/juego"><i class="icon">🎮</i> Jugar</router-link>
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

    <!-- Contenedor principal -->
    <div class="main-content">
      <div class="content-section">
        <!-- Header Section -->
        <div class="screenshots-header">
          <div class="header-left">
            <h2>Capturas</h2>
            <p>Explora capturas del juego compartidas por la comunidad.</p>
          </div>
          <div class="header-right">
            <button :class="{ 'filter-btn': true, 'active': sortFilter === 'popular' }"
              @click="setSortFilter('popular')">
              Más Populares
            </button>
            <button :class="{ 'filter-btn': true, 'active': sortFilter === 'recent' }" @click="setSortFilter('recent')">
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
        <div v-else-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
        <div v-else-if="screenshots.length === 0" class="no-screenshots-placeholder">
          <div class="placeholder-content">
            <i class="placeholder-icon">📸</i>
            <p>No hay capturas disponibles todavía.</p>
            <p>¡Sé el primero en compartir tus momentos en el juego!</p>
          </div>
        </div>
        <div v-else class="screenshots-list">
          <div class="screenshot-card" v-for="screenshot in screenshots" :key="screenshot.id"
            @click="openScreenshotModal(screenshot)">
            <div class="screenshot-image">
              <img :src="screenshot.image_url" alt="Captura del juego" @error="handleImageError" />
            </div>
            <div class="screenshot-meta">
              <span>Subido por: {{ screenshot.user?.name || 'Anónimo' }}</span>
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
            <span>Subido por: {{ selectedScreenshot.user?.name || 'Anónimo' }}</span>
            <div class="modal-actions">
              <button class="action-btn"
                :disabled="!isAuthenticated || (selectedScreenshot.user_liked || selectedScreenshot.user_disliked)"
                @click="likeScreenshot(selectedScreenshot.id)">
                <i class="icon">👍</i> Me Tonto ({{ selectedScreenshot.likes || 0 }})
              </button>
              <button class="action-btn"
                :disabled="!isAuthenticated || (selectedScreenshot.user_liked || selectedScreenshot.user_disliked)"
                @click="dislikeScreenshot(selectedScreenshot.id)">
                <i class="icon">👎</i> No me gusta ({{ selectedScreenshot.dislikes || 0 }})
              </button>
              <button class="action-btn"><i class="icon">🏆</i> Premiar</button>
              <button v-if="isAuthenticated && user && selectedScreenshot.user_id === user.id"
                @click="deleteScreenshot(selectedScreenshot.id)" class="delete-btn">
                <i class="icon">🗑️</i> Eliminar
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
                <button v-if="isAuthenticated && user && comment.user_id === user.id" @click="deleteComment(comment.id)"
                  class="delete-comment-btn">
                  <i class="icon">🗑️</i> Eliminar
                </button>
              </div>
            </div>
            <div v-if="isAuthenticated" class="comment-form">
              <textarea v-model="newComment" placeholder="Añade un comentario..." rows="2"></textarea>
              <button @click="submitComment" class="submit-comment-btn">Comentar</button>
            </div>
            <div class="comment-actions">
              <router-link :to="`/captura/${selectedScreenshot.id}`" class="view-details-btn">
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
import { isAuthenticated } from '@/api/auth';
import '@/assets/styles/Screenshots/ScreenshotsView.css';

export default {
  name: 'ScreenshotsView',
  data() {
    return {
      isAuthenticated: localStorage.getItem('auth_token') !== null,
      screenshots: [],
      loadingScreenshots: false,
      errorMessage: '',
      showUploadForm: false,
      newScreenshot: {
        image: null,
      },
      uploadError: '',
      user: null,
      selectedScreenshot: null,
      comments: [],
      newComment: '',
      sortFilter: 'recent', // Filtro por defecto: Más Recientes
    };
  },
  created() {
    window.addEventListener('storage', this.updateAuthStatus);
    this.checkAuthStatus();
    this.fetchUserData();
    this.fetchScreenshots();
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
      }
    },
    async fetchUserData() {
      try {
        const token = localStorage.getItem('auth_token');
        if (!token) return;

        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        const response = await axios.get('/api/user');
        this.user = {
          id: response.data.id,
          name: response.data.name,
          email: response.data.email,
        };
      } catch (error) {
        console.error('Error al obtener datos del usuario:', error);
        this.errorMessage = error.response ? error.response.data.message : error.message;
        localStorage.removeItem('auth_token');
        this.isAuthenticated = false;
      }
    },
    async fetchScreenshots() {
      this.loadingScreenshots = true;
      this.errorMessage = '';
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const response = await axios.get('/api/screenshots', {
          params: {
            sort: this.sortFilter,
          },
        });
        this.screenshots = response.data.map(screenshot => ({
          ...screenshot,
          image_url: `http://localhost:8000${screenshot.image_url}`,
          likes: screenshot.likes || 0,
          dislikes: screenshot.dislikes || 0,
          user_liked: screenshot.user_liked || false,
          user_disliked: screenshot.user_disliked || false,
        }));
      } catch (error) {
        const errorMsg = error.response
          ? `${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        this.errorMessage = errorMsg;
        this.screenshots = [];
      } finally {
        this.loadingScreenshots = false;
      }
    },
    setSortFilter(filter) {
      this.sortFilter = filter;
      this.fetchScreenshots();
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
        return;
      }

      const maxSize = 2048 * 1024;
      if (this.newScreenshot.image.size > maxSize) {
        this.uploadError = 'La imagen excede el tamaño máximo de 2MB.';
        return;
      }
      if (!this.newScreenshot.image.type.startsWith('image/')) {
        this.uploadError = 'El archivo seleccionado no es una imagen válida.';
        return;
      }

      const formData = new FormData();
      formData.append('image', this.newScreenshot.image);

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación. Por favor, inicia sesión nuevamente.');
        }
        const response = await axios.post('/api/screenshots', formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data',
          },
        });
        this.showUploadForm = false;
        this.newScreenshot = { image: null, description: '' };
        this.uploadError = '';
        await this.fetchScreenshots();
      } catch (error) {
        console.error('Error al subir captura:', error);
        if (error.response) {
          const status = error.response.status;
          const message = error.response.data.message || 'Error desconocido';
          if (status === 422) {
            this.uploadError = `Validación fallida: ${message}`;
            if (error.response.data.errors) {
              const errors = Object.values(error.response.data.errors).flat().join(', ');
              this.uploadError += ` (${errors})`;
            }
          } else if (status === 401) {
            this.uploadError = 'No estás autenticado. Por favor, inicia sesión.';
          } else if (status === 500) {
            this.uploadError = 'Error interno del servidor. Por favor, intenta de nuevo más tarde.';
          } else {
            this.uploadError = `Error ${status}: ${message}`;
          }
        } else if (error.request) {
          this.uploadError = 'No se pudo conectar al servidor. Verifica tu conexión.';
        } else {
          this.uploadError = `Error: ${error.message}`;
        }
      }
    },
    async deleteScreenshot(screenshotId) {
      if (!confirm('¿Estás seguro de que quieres eliminar esta captura?')) return;

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
        this.errorMessage = '';
        this.closeScreenshotModal();
      } catch (error) {
        console.error('Error al eliminar captura:', error);
        this.errorMessage = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
      }
    },
    async likeScreenshot(screenshotId) {
      if (!this.isAuthenticated) {
        alert('Debes iniciar sesión para dar "Me gusta".');
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
        console.log(`Enviando petición a /api/screenshots/${screenshotId}/like con token: ${token}`);
        const response = await axios.post(`/api/screenshots/${screenshotId}/like`, {}, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        console.log('Respuesta del servidor:', response.data);
        const updatedScreenshot = response.data;
        this.selectedScreenshot.likes = updatedScreenshot.likes;
        this.selectedScreenshot.dislikes = updatedScreenshot.dislikes;
        this.selectedScreenshot.user_liked = updatedScreenshot.user_liked;
        this.selectedScreenshot.user_disliked = updatedScreenshot.user_disliked;
        this.screenshots = this.screenshots.map(s =>
          s.id === screenshotId ? { ...s, ...updatedScreenshot } : s
        );
      } catch (error) {
        console.error('Error al dar "Me gusta":', error.response ? error.response.data : error.message);
        const errorMsg = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        alert(`No se pudo dar "Me gusta": ${errorMsg}`);
      }
    },

    async dislikeScreenshot(screenshotId) {
      if (!this.isAuthenticated) {
        alert('Debes iniciar sesión para dar "No me gusta".');
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
        console.log(`Enviando petición a /api/screenshots/${screenshotId}/dislike con token: ${token}`);
        const response = await axios.post(`/api/screenshots/${screenshotId}/dislike`, {}, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        console.log('Respuesta del servidor:', response.data);
        const updatedScreenshot = response.data;
        this.selectedScreenshot.likes = updatedScreenshot.likes;
        this.selectedScreenshot.dislikes = updatedScreenshot.dislikes;
        this.selectedScreenshot.user_liked = updatedScreenshot.user_liked;
        this.selectedScreenshot.user_disliked = updatedScreenshot.user_disliked;
        this.screenshots = this.screenshots.map(s =>
          s.id === screenshotId ? { ...s, ...updatedScreenshot } : s
        );
      } catch (error) {
        console.error('Error al dar "No me gusta":', error.response ? error.response.data : error.message);
        const errorMsg = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        alert(`No se pudo dar "No me gusta": ${errorMsg}`);
      }
    },
    async deleteComment(commentId) {
      if (!confirm('¿Estás seguro de que quieres eliminar este comentario?')) return;

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación.');
        }
        await axios.delete(`/api/screenshots/${this.selectedScreenshot.id}/comments/${commentId}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        this.comments = this.comments.filter(comment => comment.id !== commentId);
      } catch (error) {
        console.error('Error al eliminar comentario:', error);
        const errorMsg = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        alert(`No se pudo eliminar el comentario: ${errorMsg}`);
      }
    },
    openScreenshotModal(screenshot) {
      this.selectedScreenshot = screenshot;
      this.fetchComments(screenshot.id);
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
        alert('No se pudieron cargar los comentarios. Por favor, intenta de nuevo.');
      }
    },
    async submitComment() {
      if (!this.newComment.trim()) {
        alert('Por favor, escribe un comentario antes de enviar.');
        return;
      }

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) {
          throw new Error('No se encontró el token de autenticación. Por favor, inicia sesión nuevamente.');
        }
        const response = await axios.post(
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
      } catch (error) {
        console.error('Error al enviar comentario:', error);
        const errorMsg = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        alert(`Hubo un error al enviar el comentario: ${errorMsg}. Por favor, intenta de nuevo.`);
      }
    },
  },
};
</script>