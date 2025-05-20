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

    <!-- Contenedor principal -->
    <div class="main-content">
      <!-- Estado de carga -->
      <div v-if="loading" class="loading-message">
        <i class="icon loading-icon">⏳</i> Cargando captura...
      </div>
      <!-- Estado de error -->
      <div v-else-if="error" class="error-message">
        <p>{{ error }}</p>
        <router-link to="/comunidad/capturas">Volver a las capturas</router-link>
      </div>
      <!-- Contenido cuando los datos están cargados -->
      <div v-else-if="screenshot">
        <!-- Contenedor de imagen mejorado -->
        <div class="screenshot-image-container">
          <img v-if="screenshot.image_url" :src="screenshot.image_url" alt="Captura del juego" class="screenshot-image"
            @error="handleImageError" />
          <div v-else class="no-image-placeholder"></div>
        </div>

        <!-- Contenedor de dos columnas -->
        <div class="content-wrapper">
          <!-- Columna izquierda: Comentarios -->
          <div class="left-column">
            <!-- Sección de comentarios -->
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

              <!-- Formulario para añadir comentario -->
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

          <!-- Columna derecha: Controles e información -->
          <div class="right-column">
            <!-- Controles del propietario -->
            <div v-if="isAuthenticated && user && screenshot.user_id === user.id" class="owner-controls">
              <h3>Controles del propietario</h3>
              <button @click="deleteScreenshot(screenshot.id)" class="delete-btn">
                <i class="icon">🗑️</i> Eliminar
              </button>
            </div>

            <!-- Información de la captura -->
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
      <!-- Fallback si no hay captura -->
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

<style scoped>
.screenshot-details-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #1e1e2f 0%, #2a2a3f 100%);
  color: #fff;
  font-family: 'Arial', sans-serif;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Barra de navegación */
.nav-container {
  width: 100%;
  padding: 15px 20px;
  background-color: rgba(255, 255, 255, 0.95);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
  position: sticky;
  top: 0;
  z-index: 10;
  display: flex;
  justify-content: space-between;
  align-items: center;
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
  padding: 0;
  margin: 0;
  display: flex;
  align-items: center;
}

.nav-container ul li {
  margin: 0 20px;
}

.nav-container ul li a {
  text-decoration: none;
  color: #ff6f61;
  font-size: 1.2em;
  display: flex;
  align-items: center;
  transition: color 0.3s;
}

.nav-container ul li a .icon {
  margin-right: 8px;
}

.nav-container ul li a:hover {
  color: #e63946;
}

/* Contenido principal */
.main-content {
  width: 85%;
  max-width: 900px;
  margin: 20px 0;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Contenedor de imagen mejorado */
.screenshot-image-container {
  width: 100%;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.screenshot-image {
  width: 100%;
  height: auto;
  max-height: 500px;
  object-fit: contain;
  display: block;
}

.no-image-placeholder {
  width: 100%;
  height: 200px;
  background: #000;
  border-radius: 5px;
}

/* Contenedor de dos columnas */
.content-wrapper {
  display: flex;
  flex-direction: row;
  gap: 20px;
  position: relative;
  z-index: 1;
}

/* Columna izquierda (Comentarios) */
.left-column {
  flex: 2;
}

/* Columna derecha (Controles e información) */
.right-column {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

/* Controles del propietario */
.owner-controls {
  background: #1e1e2f;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #3a3a5f;
}

.owner-controls h3 {
  color: #ff6f61;
  margin: 0 0 10px;
  font-size: 1.2em;
}

.delete-btn {
  background: #e63946;
  padding: 8px 15px;
  border-radius: 5px;
  color: #fff;
  font-size: 0.9em;
  display: flex;
  align-items: center;
  gap: 5px;
  border: none;
  cursor: pointer;
  transition: background 0.3s;
}

.delete-btn:hover {
  background: #d62828;
}

/* Información de la captura */
.screenshot-info {
  background: #1e1e2f;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #3a3a5f;
}

.screenshot-info h3 {
  color: #ff6f61;
  margin: 0 0 10px;
  font-size: 1.2em;
}

.screenshot-info p {
  color: #fff;
  margin: 5px 0;
  font-size: 0.9em;
}

/* Sección de comentarios */
.comments-section {
  background: #1e1e2f;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  border: 1px solid #3a3a5f;
}

.comments-title {
  color: #ff6f61;
  margin: 0 0 15px;
  font-size: 1.4em;
  font-weight: 600;
  border-bottom: 1px solid #3a3a5f;
  padding-bottom: 10px;
}

.no-comments {
  text-align: center;
  color: #999;
  padding: 20px;
  background: #1e1e2f;
  border-radius: 5px;
}

.no-comments p {
  margin: 0;
  font-size: 1em;
  font-style: italic;
}

.comments-list {
  max-height: 400px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 15px;
  padding-right: 10px;
}

/* Estilo para el scrollbar */
.comments-list::-webkit-scrollbar {
  width: 8px;
}

.comments-list::-webkit-scrollbar-track {
  background: #1e1e2f;
  border-radius: 4px;
}

.comments-list::-webkit-scrollbar-thumb {
  background: #ff6f61;
  border-radius: 4px;
}

.comments-list::-webkit-scrollbar-thumb:hover {
  background: #e63946;
}

.comment-card {
  background: #1e1e2f;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #3a3a5f;
  transition: transform 0.2s, box-shadow 0.2s;
}

.comment-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.comment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.comment-user-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.comment-avatar {
  width: 32px;
  height: 32px;
  background: #ff6f61;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2em;
  font-weight: bold;
  text-transform: uppercase;
}

.comment-meta {
  display: flex;
  flex-direction: column;
}

.comment-user {
  font-weight: 600;
  color: #2a9d8f;
  font-size: 1em;
}

.comment-timestamp {
  color: #999;
  font-size: 0.8em;
}

.comment-text {
  margin: 0;
  color: #fff;
  font-size: 0.95em;
  line-height: 1.5;
}

.delete-comment-btn {
  background: #e63946;
  padding: 6px 12px;
  border-radius: 5px;
  color: #fff;
  font-size: 0.85em;
  display: flex;
  align-items: center;
  gap: 5px;
  border: none;
  cursor: pointer;
  transition: background 0.3s, transform 0.2s;
}

.delete-comment-btn:hover {
  background: #d62828;
  transform: scale(1.05);
}

.comment-form {
  margin-top: 20px;
  background: #1e1e2f;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #3a3a5f;
}

.comment-form-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.comment-input {
  width: 96%;
  padding: 10px;
  border: 1px solid #3a3a5f;
  border-radius: 5px;
  background: #2a2a3f;
  color: #fff;
  resize: vertical;
  font-size: 0.95em;
  transition: border-color 0.3s;
}

.comment-input:focus {
  border-color: #ff6f61;
  outline: none;
}

.submit-comment-btn {
  background: linear-gradient(90deg, #ff6f61, #ff9f1c);
  color: #fff;
  padding: 8px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 0.95em;
  margin-top: 10px;
  transition: background 0.3s, transform 0.2s;
}

.submit-comment-btn:hover {
  background: linear-gradient(90deg, #e63946, #ff9f1c);
  transform: scale(1.05);
}

.login-to-comment {
  color: #999;
  font-size: 0.9em;
  text-align: center;
  margin-top: 15px;
}

.login-to-comment a {
  color: #ff6f61;
  text-decoration: none;
  font-weight: 600;
}

.login-to-comment a:hover {
  color: #e63946;
  text-decoration: underline;
}

.loading-message {
  color: #ff6f61;
  font-size: 1em;
  text-align: center;
  padding: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.loading-icon {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

/* Botón para volver */
.back-btn {
  background: linear-gradient(90deg, #ff6f61, #ff9f1c);
  color: #fff;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1em;
  transition: background 0.3s;
  margin: 15px 0;
  display: block;
  width: fit-content;
  align-self: center;
}

.back-btn:hover {
  background: linear-gradient(90deg, #e63946, #ff9f1c);
}

/* Responsive Design */
@media (max-width: 768px) {
  .main-content {
    width: 95%;
  }

  .content-wrapper {
    flex-direction: column;
  }

  .left-column,
  .right-column {
    flex: 1;
  }

  .screenshot-image {
    max-height: 300px;
  }

  .comments-list {
    max-height: 200px;
  }

  .comment-card {
    padding: 10px;
  }

  .comment-avatar {
    width: 28px;
    height: 28px;
    font-size: 1em;
  }

  .comment-user {
    font-size: 0.9em;
  }

  .comment-timestamp {
    font-size: 0.75em;
  }

  .comment-text {
    font-size: 0.9em;
  }

  .delete-comment-btn {
    padding: 5px 10px;
    font-size: 0.8em;
  }

  .comment-input {
    font-size: 0.9em;
  }

  .submit-comment-btn {
    padding: 7px 15px;
    font-size: 0.9em;
  }
}
</style>