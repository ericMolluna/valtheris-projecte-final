<template>
  <div class="screenshot-details-container">
    <!-- Barra de navegación superior -->
    <nav class="nav-container">
      <div class="logo">
        <router-link to="/" class="logo-link">🎮 Valtheris</router-link>
      </div>
      <ul>
        <li>
          <router-link to="/comunidad/capturas"><i class="icon">🎮</i> Volver a las capturas</router-link>
        </li>
        <li>
          <router-link to="/comunidad"><i class="icon">👥</i> Comunidad</router-link>
        </li>
        <li>
          <router-link :to="isAuthenticated ? '/perfil' : '/login'">
            <i class="icon">{{ isAuthenticated ? '👤' : '🔑' }}</i>
            {{ isAuthenticated ? 'Perfil' : 'Login' }}
          </router-link>
        </li>
      </ul>
    </nav>

    <div class="main-content">
      <div v-if="loading" class="loading-message">
        <i class="icon loading-icon">⏳</i> Cargando captura...
      </div>
      <div v-else-if="error" class="error-message">
        <p>{{ error }}</p>
        <router-link to="/comunidad/capturas">Volver a las capturas</router-link>
      </div>
      <div v-else-if="screenshot">
        <div class="screenshot-image-container">
          <img v-if="screenshot.image_url" :src="screenshot.image_url" alt="Captura del juego" class="screenshot-image"
            @error="handleImageError" />
          <div v-else class="no-image-placeholder"></div>
        </div>

        <div class="content-wrapper">
          <div class="left-column">
    
            <div class="comments-section">
              <h3 class="comments-title">Comentarios ({{ comments.length }})</h3>
              <div v-if="loadingComments" class="loading-message">
                <i class="icon loading-icon">⏳</i> Cargando comentarios...
              </div>
              <div v-else-if="comments.length === 0" class="no-comments">
                <p>No hay comentarios todavía. ¡Sé el primero en comentar!</p>
              </div>
              <div v-else class="comments-list">
                <div v-for="comment in comments" :key="comment.id" class="comment-card">
                  <div class="comment-header">
                    <div class="comment-user-info">
                      <span class="comment-avatar">{{ comment.user?.name ? comment.user.name.charAt(0) : 'A' }}</span>
                      <div class="comment-meta">
                        <span class="comment-user">{{ comment.user?.name || 'Anónimo' }}</span>
                        <span class="comment-timestamp">{{ formatTimestamp(comment.created_at) }}</span>
                      </div>
                    </div>
                    <button v-if="isAuthenticated && user && comment.user_id === user.id"
                      @click="deleteComment(comment.id)" class="delete-comment-btn">
                      <i class="icon">🗑️</i> Eliminar
                    </button>
                  </div>
                  <p class="comment-text">{{ comment.text }}</p>
                </div>
              </div>

              <div v-if="isAuthenticated" class="comment-form">
                <div class="comment-form-header">
                  <span class="comment-avatar">{{ user?.name ? user.name.charAt(0) : 'A' }}</span>
                  <span class="comment-user">{{ user?.name || 'Anónimo' }}</span>
                </div>
                <textarea v-model="newComment" placeholder="Añade un comentario..." rows="3"
                  class="comment-input"></textarea>
                <button @click="submitComment" class="submit-comment-btn">Comentar</button>
              </div>
              <div v-else class="login-to-comment">
                <p>
                  <router-link to="/login">Inicia sesión</router-link> para comentar.
                </p>
              </div>
            </div>
          </div>

          <div class="right-column">
            <div v-if="isAuthenticated && user && screenshot.user_id === user.id" class="owner-controls">
              <h3>Controles del propietario</h3>
              <button @click="deleteScreenshot(screenshot.id)" class="delete-btn">
                <i class="icon">🗑️</i> Eliminar
              </button>
            </div>

            <div class="screenshot-info">
              <h3>Información de la captura</h3>
              <p><strong>Título:</strong> {{ screenshot.title || 'Sin título' }}</p>
              <p><strong>Subido por:</strong> {{ screenshot.username || 'Anónimo' }}</p>
              <p><strong>Publicado el:</strong> {{ screenshotDate }}</p>
              <p><strong>Dimensiones:</strong> {{ screenshotDimensions }}</p>
            </div>
          </div>
        </div>
      </div>
      <!-- Fallback  no hay captura -->
      <div v-else class="error-message">
        <p>No se encontró la captura.</p>
        <router-link to="/comunidad/capturas">Volver a las capturas</router-link>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import { isAuthenticated } from '@/api/auth';

export default {
  name: 'ScreenshotsDetailsView',
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
      screenshot: null,
      comments: [],
      newComment: '',
      loading: false,
      loadingComments: false,
      error: null,
      uniqueVisitors: 1,
    };
  },
  computed: {
    screenshotSize() {
      return this.screenshot && this.screenshot.size ? this.screenshot.size : 'N/A';
    },
    screenshotDate() {
      if (!this.screenshot || !this.screenshot.created_at) return 'N/A';
      const date = new Date(this.screenshot.created_at);
      return date.toLocaleDateString('es-ES', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    },
    screenshotDimensions() {
      return this.screenshot && this.screenshot.width && this.screenshot.height
        ? `${this.screenshot.width} x ${this.screenshot.height}`
        : 'N/A';
    },
  },
  created() {
    this.checkAuthStatus();
    this.fetchUserData();
    this.fetchScreenshotDetails();
    this.fetchComments();
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
        const response = await axios.get('/api/user');
        this.user = {
          id: response.data.id,
          name: response.data.name,
          email: response.data.email,
        };
      } catch (error) {
        console.error('Error al obtener datos del usuario:', error);
        this.error = error.response ? error.response.data.message : error.message;
        localStorage.removeItem('auth_token');
        this.isAuthenticated = false;
      }
    },
    async fetchScreenshotDetails() {
      this.loading = true;
      try {
        const response = await axios.get(`/api/screenshots/${this.id}`);
        console.log('API Response:', response.data);
        if (!response.data.user || !response.data.user.name) {
          console.warn('User data missing or incomplete:', response.data.user);
        }
        const imageUrl = response.data.image_url
          ? (response.data.image_url.startsWith('http')
            ? response.data.image_url
            : `http://localhost:8000${response.data.image_url}`)
          : null;

        this.screenshot = {
          ...response.data,
          image_url: imageUrl,
        };

        if (this.screenshot.image_url) {
          const img = new Image();
          img.src = this.screenshot.image_url;
          await new Promise((resolve) => {
            img.onload = () => {
              this.screenshot.width = img.width;
              this.screenshot.height = img.height;
              resolve();
            };
            img.onerror = () => {
              console.warn('Error al cargar la imagen para dimensiones');
              this.screenshot.width = 'N/A';
              this.screenshot.height = 'N/A';
              resolve();
            };
          });

          try {
            const imageResponse = await axios.get(this.screenshot.image_url, { responseType: 'blob' });
            const blob = imageResponse.data;
            this.screenshot.size = (blob.size / 1024 / 1024).toFixed(3);
          } catch (imageError) {
            console.warn('No se pudo obtener el tamaño de la imagen:', imageError);
            this.screenshot.size = 'N/A';
          }
        } else {
          this.screenshot.width = 'N/A';
          this.screenshot.height = 'N/A';
          this.screenshot.size = 'N/A';
        }
      } catch (error) {
        console.error('Error al cargar detalles de la captura:', error);
        this.error = error.response?.data?.message || 'No se pudo cargar la captura.';
        this.screenshot = null;
      } finally {
        this.loading = false;
      }
    },
    async fetchComments() {
      this.loadingComments = true;
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        const response = await axios.get(`/api/screenshots/${this.id}/comments`);
        this.comments = response.data;
      } catch (error) {
        console.error('Error al cargar comentarios:', error);
        this.comments = [];
        this.error = 'No se pudieron cargar los comentarios.';
      } finally {
        this.loadingComments = false;
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
          `/api/screenshots/${this.id}/comments`,
          { text: this.newComment },
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );
        this.newComment = '';
        this.fetchComments();
        await Swal.fire({
          title: '¡Comentario enviado!',
          text: 'Tu comentario ha sido publicado exitosamente.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        console.error('Error al enviar comentario:', error);
        this.error = error.response?.data?.message || 'Error al enviar el comentario.';
        await Swal.fire({
          title: 'Error',
          text: this.error,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
      }
    },
    async deleteComment(commentId) {
      const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Quieres eliminar este comentario?',
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
        await axios.delete(`/api/screenshots/${this.id}/comments/${commentId}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        this.comments = this.comments.filter((comment) => comment.id !== commentId);
        await Swal.fire({
          title: '¡Eliminado!',
          text: 'El comentario ha sido eliminado.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        console.error('Error al eliminar comentario:', error);
        this.error = error.response?.data?.message || 'Error al eliminar el comentario.';
        await Swal.fire({
          title: 'Error',
          text: this.error,
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
        await Swal.fire({
          title: '¡Eliminado!',
          text: 'La captura ha sido eliminada.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
        this.$router.push('/comunidad/capturas');
      } catch (error) {
        console.error('Error al eliminar captura:', error);
        this.error = error.response?.data?.message || 'Error al eliminar la captura.';
        await Swal.fire({
          title: 'Error',
          text: this.error,
          icon: 'error',
          confirmButtonColor: '#ff6f61',
        });
      }
    },
    handleImageError(event) {
      event.target.style.display = 'none';
      const placeholder = document.createElement('div');
      placeholder.className = 'no-image-placeholder';
      event.target.parentNode.appendChild(placeholder);
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

<style src="@/assets/styles/Screenshots/ScreenshotDetails.css" scoped></style>

