<template>
  <div class="screenshots-container">
    <!-- Barra de navegación superior -->
    <nav class="nav-container">
      <div class="logo">
        <router-link to="/" class="logo-link">🎮 Valtheris</router-link>
      </div>
      <button class="menu-toggle" @click="toggleMenu" aria-label="Toggle Menu">
        ☰
      </button>
      <ul :class="{ 'nav-links': true, 'nav-links-open': isMenuOpen }">
        <li>
          <router-link to="/juego" class="nav-item">
            <span class="icon">🎮</span> Jugar
          </router-link>
        </li>
        <li class="dropdown" data-testid="comunidad-item">
          <span class="dropdown-toggle" @click="toggleDropdown" aria-haspopup="true" :aria-expanded="isDropdownOpen">
            <span class="icon">👥</span> Comunidad
          </span>
          <ul class="dropdown-menu" v-show="isDropdownOpen">
            <li><router-link to="/comunidad/capturas" class="dropdown-item">Capturas</router-link></li>
            <li><router-link to="/comunidad/guias" class="dropdown-item">Guías</router-link></li>
            <li><router-link to="/videos" class="dropdown-item">Videos</router-link></li>
          </ul>
        </li>
        <li>
          <router-link :to="isAuthenticated ? '/perfil' : '/login'" class="nav-item">
            <span class="icon">{{ isAuthenticated ? '👤' : '🔑' }}</span>
            {{ isAuthenticated ? 'Perfil' : 'Login' }}
          </router-link>
        </li>
        <li v-if="isAuthenticated">
          <button class="nav-item logout-btn" @click="handleLogout" aria-label="Cerrar Sesión">
            <span class="icon">🚪</span> Cerrar Sesión
          </button>
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
            <span class="icon">📸</span> Subir Captura
          </button>
        </div>

        <!-- Formulario para subir captura (modal) -->
        <div v-if="showUploadForm" class="upload-form-modal">
          <div class="upload-form">
            <h3>Subir Nueva Captura</h3>
            <form @submit.prevent="uploadScreenshot">
              <div class="form-group">
                <label for="screenshot-title">Título</label>
                <input type="text" id="screenshot-title" v-model="newScreenshot.title"
                  placeholder="Título de tu captura..." required maxlength="100" />
              </div>
              <div class="form-group">
                <label for="screenshot-image">Seleccionar</label>
                <input type="file" id="screenshot-image" accept="image/*" @change="handleFileChange" required
                  ref="fileInput" />
                <div v-if="imagePreview" class="image-preview">
                  <img :src="imagePreview" alt="Vista previa de la imagen" />
                </div>
              </div>
              <div class="form-actions">
                <button type="submit" class="submit-btn" :disabled="isUploading">Subir</button>
                <button type="button" class="cancel-btn" @click="closeUploadForm">Cancelar</button>
              </div>
              <p v-if="uploadError" class="error-message">{{ uploadError }}</p>
            </form>
          </div>
        </div>

        <!-- Lista de capturas o placeholder -->
        <div v-if="loadingScreenshots && !screenshots.length" class="loading-message">
          Cargando capturas...
        </div>
        <div v-else-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
        <div v-else-if="screenshots.length === 0" class="no-screenshots-placeholder">
          <div class="placeholder-content">
            <span class="placeholder-icon">📸</span>
            <p>No hay capturas disponibles todavía.</p>
            <p>¡Sé el primero en compartir tus momentos en el juego!</p>
          </div>
        </div>
        <div v-else class="screenshots-list" ref="screenshotsList">
          <div class="screenshot-card" v-for="screenshot in screenshots" :key="screenshot.id"
            @click="openScreenshotModal(screenshot)">
            <div class="screenshot-image">
              <img :src="screenshot.image_url" alt="Captura del juego" @error="handleImageError" />
            </div>
            <div class="screenshot-meta">
              <span>Subido por: {{ screenshot.user?.name || 'Anónimo' }}</span>
              <div class="quick-actions">
                <button class="quick-action-btn" @click.stop="likeScreenshot(screenshot.id)"
                  :disabled="!isAuthenticated || screenshot.user_liked || screenshot.user_disliked">
                  <span class="icon">👍</span> {{ screenshot.likes || 0 }}
                </button>
                <button class="quick-action-btn" @click.stop="dislikeScreenshot(screenshot.id)"
                  :disabled="!isAuthenticated || screenshot.user_liked || screenshot.user_disliked">
                  <span class="icon">👎</span> {{ screenshot.dislikes || 0 }}
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-if="loadingMore" class="loading-message">
          Cargando más capturas...
        </div>
      </div>
    </div>

    <!-- Modal para mostrar la captura en tamaño completo con comentarios -->
    <div v-if="selectedScreenshot" class="screenshot-modal">
      <div class="modal-content">
        <button class="close-btn" @click="closeScreenshotModal" aria-label="Cerrar modal">✖</button>
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
                <span class="icon">👍</span> Me Tonto ({{ selectedScreenshot.likes || 0 }})
              </button>
              <button class="action-btn"
                :disabled="!isAuthenticated || (selectedScreenshot.user_liked || selectedScreenshot.user_disliked)"
                @click="dislikeScreenshot(selectedScreenshot.id)">
                <span class="icon">👎</span> No me gusta ({{ selectedScreenshot.dislikes || 0 }})
              </button>
              <div class="rating-section">
                <span
                  v-for="star in 5"
                  :key="star"
                  class="star"
                  :class="{ filled: star <= selectedScreenshot.user_rating || (selectedScreenshot.average_rating && star <= Math.round(selectedScreenshot.average_rating)) }"
                  @click="isAuthenticated && rateScreenshot(selectedScreenshot.id, star)"
                >
                  ★
                </span>
                <span v-if="selectedScreenshot.average_rating">
                  ({{ selectedScreenshot.average_rating.toFixed(1) }}/5)
                </span>
                <span v-else>Sin calificaciones</span>
              </div>
              <button v-if="isAuthenticated && user && selectedScreenshot.user_id === user.id"
                @click="deleteScreenshot(selectedScreenshot.id)" class="delete-btn">
                <span class="icon">🗑️</span> Eliminar
              </button>
            </div>
          </div>
          <div class="comments-section">
            <h3>Comentarios</h3>
            <div v-if="loadingComments" class="loading-message">
              Cargando comentarios...
            </div>
            <div v-else-if="comments.length === 0" class="no-comments">
              <p>No hay comentarios todavía. ¡Sé el primero en comentar!</p>
            </div>
            <div v-else class="comments-list">
              <div v-for="comment in comments" :key="comment.id" class="comment">
                <span class="comment-user">{{ comment.user?.name || 'Anónimo' }}:</span>
                <p class="comment-text">{{ comment.text }}</p>
                <button v-if="isAuthenticated && user && comment.user_id === user.id" @click="deleteComment(comment.id)"
                  class="delete-comment-btn">
                  <span class="icon">🗑️</span> Eliminar
                </button>
              </div>
            </div>
            <div v-if="isAuthenticated" class="comment-form">
              <textarea v-model="newComment" @input="logComment" placeholder="Añade un comentario..." rows="2"
                maxlength="280" aria-label="Escribe un comentario"></textarea>
              <div class="comment-counter" :class="{ 'warning': newComment.length > 260 }">
                {{ newComment.length }}/280
              </div>
              <button @click="submitComment" class="submit-comment-btn" :disabled="!newComment.trim()">Comentar</button>
            </div>
            <div v-else class="login-to-comment">
              <p><router-link to="/login">Inicia sesión</router-link> para comentar.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="footer-section">
      <div class="footer-content">
        <p>© 2025 Valtheris. Todos los derechos reservados.</p>
        <div class="social-links">
          <a href="#" class="social-icon" aria-label="Twitter">🐦</a>
          <a href="#" class="social-icon" aria-label="Discord">💬</a>
          <a href="#" class="social-icon" aria-label="Instagram">📸</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import { isAuthenticated } from '@/api/auth';

export default {
  name: 'ScreenshotsView',
  data() {
    return {
      isAuthenticated: localStorage.getItem('auth_token') !== null,
      screenshots: [],
      loadingScreenshots: false,
      loadingMore: false,
      errorMessage: '',
      showUploadForm: false,
      newScreenshot: {
        title: '',
        image: null,
        description: '',
      },
      uploadError: '',
      user: null,
      selectedScreenshot: null,
      comments: [],
      loadingComments: false,
      newComment: '',
      sortFilter: 'recent',
      isMenuOpen: false,
      isDropdownOpen: false,
      imagePreview: null,
      isUploading: false,
      page: 1,
      hasMore: true,
    };
  },
  created() {
    window.addEventListener('storage', this.updateAuthStatus);
    window.addEventListener('scroll', this.handleScroll);
    this.checkAuthStatus();
    this.fetchUserData();
    this.fetchScreenshots();
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.updateAuthStatus);
    window.removeEventListener('scroll', this.handleScroll);
  },
  methods: {
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen;
      if (this.isMenuOpen) this.isDropdownOpen = false;
    },
    toggleDropdown() {
      this.isDropdownOpen = !this.isDropdownOpen;
    },
    closeUploadForm() {
      this.showUploadForm = false;
      this.newScreenshot = { title: '', image: null, description: '' };
      this.imagePreview = null;
      this.uploadError = '';
      this.$refs.fileInput.value = '';
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
    async fetchScreenshots(append = false) {
      if (this.loadingScreenshots || this.loadingMore || !this.hasMore) return;

      if (!append) {
        this.loadingScreenshots = true;
        this.page = 1;
        this.screenshots = [];
      } else {
        this.loadingMore = true;
      }

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const response = await axios.get('/api/screenshots', {
          params: {
            sort: this.sortFilter,
            page: this.page,
            per_page: 12,
          },
        });
        const newScreenshots = response.data.data.map(screenshot => ({
          ...screenshot,
          image_url: `http://localhost:8000${screenshot.image_url}`,
          likes: screenshot.likes || 0,
          dislikes: screenshot.dislikes || 0,
          user_liked: screenshot.user_liked || false,
          user_disliked: screenshot.user_disliked || false,
          average_rating: screenshot.average_rating || null,
          user_rating: screenshot.user_rating || null,
        }));
        this.screenshots = append ? [...this.screenshots, ...newScreenshots] : newScreenshots;
        this.hasMore = response.data.current_page < response.data.last_page;
        if (this.hasMore) this.page += 1;
      } catch (error) {
        this.errorMessage = error.response
          ? `${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        this.screenshots = [];
      } finally {
        this.loadingScreenshots = false;
        this.loadingMore = false;
      }
    },
    handleScroll() {
      const list = this.$refs.screenshotsList;
      if (!list) return;
      const bottomOfWindow = window.innerHeight + window.scrollY >= document.documentElement.offsetHeight - 100;
      if (bottomOfWindow && !this.loadingMore && this.hasMore) {
        this.fetchScreenshots(true);
      }
    },
    setSortFilter(filter) {
      if (this.sortFilter !== filter) {
        this.sortFilter = filter;
        this.fetchScreenshots();
      }
    },
    handleImageError(event) {
      event.target.style.display = 'none';
      const placeholder = document.createElement('div');
      placeholder.className = 'no-image-placeholder';
      placeholder.textContent = 'Sin imagen';
      event.target.parentNode.appendChild(placeholder);
    },
    handleFileChange(event) {
      const file = event.target.files[0];
      this.newScreenshot.image = file;
      if (file) {
        const reader = new FileReader();
        reader.onload = e => {
          this.imagePreview = e.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        this.imagePreview = null;
      }
    },
    async uploadScreenshot() {
      if (!this.newScreenshot.image) {
        this.uploadError = 'Por favor, selecciona una imagen válida.';
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.uploadError,
          confirmButtonColor: '#ff0066',
        });
        return;
      }
      if (!this.newScreenshot.title.trim()) {
        this.uploadError = 'Por favor, proporciona un título.';
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.uploadError,
          confirmButtonColor: '#ff0066',
        });
        return;
      }
      const maxSize = 2048 * 1024;
      if (this.newScreenshot.image.size > maxSize) {
        this.uploadError = 'La imagen excede el tamaño máximo de 2MB.';
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.uploadError,
          confirmButtonColor: '#ff0066',
        });
        return;
      }
      if (!this.newScreenshot.image.type.startsWith('image/')) {
        this.uploadError = 'El archivo seleccionado no es una imagen válida.';
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.uploadError,
          confirmButtonColor: '#ff0066',
        });
        return;
      }

      const formData = new FormData();
      formData.append('title', this.newScreenshot.title);
      formData.append('image', this.newScreenshot.image);
      formData.append('description', this.newScreenshot.description || '');

      try {
        this.isUploading = true;
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) throw new Error('No se encontró el token de autenticación.');
        await axios.post('/api/screenshots', formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data',
          },
        });
        this.closeUploadForm();
        await this.fetchScreenshots();
        Swal.fire({
          icon: 'success',
          title: '¡Captura subida!',
          text: 'Tu captura ha sido subida exitosamente.',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        this.uploadError = error.response
          ? error.response.data.message || `Error ${error.response.status}`
          : error.message;
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.uploadError,
          confirmButtonColor: '#ff0066',
        });
      } finally {
        this.isUploading = false;
      }
    },
    async deleteScreenshot(screenshotId) {
      const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff0066',
        cancelButtonColor: '#2a2a3f',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
      });
      if (!result.isConfirmed) return;

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) throw new Error('No se encontró el token de autenticación.');
        await axios.delete(`/api/screenshots/${screenshotId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.screenshots = this.screenshots.filter(s => s.id !== screenshotId);
        this.closeScreenshotModal();
        Swal.fire({
          icon: 'success',
          title: '¡Eliminado!',
          text: 'La captura ha sido eliminada.',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        this.errorMessage = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.errorMessage,
          confirmButtonColor: '#ff0066',
        });
      }
    },
    async likeScreenshot(screenshotId) {
      if (!this.isAuthenticated) {
        Swal.fire({
          icon: 'warning',
          title: 'Inicia sesión',
          text: 'Debes iniciar sesión para dar "Me gusta".',
          confirmButtonColor: '#ff0066',
        });
        this.$router.push('/login');
        return;
      }

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) throw new Error('No se encontró el token de autenticación.');
        const method = this.screenshots.find(s => s.id === screenshotId).user_liked ? 'delete' : 'post';
        const response = await axios({
          method,
          url: `/api/screenshots/${screenshotId}/like`,
          headers: { Authorization: `Bearer ${token}` },
        });
        const updatedScreenshot = response.data;
        this.screenshots = this.screenshots.map(s =>
          s.id === screenshotId ? { ...s, ...updatedScreenshot } : s
        );
        if (this.selectedScreenshot && this.selectedScreenshot.id === screenshotId) {
          this.selectedScreenshot = { ...this.selectedScreenshot, ...updatedScreenshot };
        }
        Swal.fire({
          icon: 'success',
          title: updatedScreenshot.user_liked ? '¡Me gusta añadido!' : 'Me gusta eliminado',
          timer: 1500,
          showConfirmButton: false,
        });
      } catch (error) {
        const errorMsg = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: `No se pudo dar "Me gusta": ${errorMsg}`,
          confirmButtonColor: '#ff0066',
        });
      }
    },
    async dislikeScreenshot(screenshotId) {
      if (!this.isAuthenticated) {
        Swal.fire({
          icon: 'warning',
          title: 'Inicia sesión',
          text: 'Debes iniciar sesión para dar "No me gusta".',
          confirmButtonColor: '#ff0066',
        });
        this.$router.push('/login');
        return;
      }

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) throw new Error('No se encontró el token de autenticación.');
        const method = this.screenshots.find(s => s.id === screenshotId).user_disliked ? 'delete' : 'post';
        const response = await axios({
          method,
          url: `/api/screenshots/${screenshotId}/dislike`,
          headers: { Authorization: `Bearer ${token}` },
        });
        const updatedScreenshot = response.data;
        this.screenshots = this.screenshots.map(s =>
          s.id === screenshotId ? { ...s, ...updatedScreenshot } : s
        );
        if (this.selectedScreenshot && this.selectedScreenshot.id === screenshotId) {
          this.selectedScreenshot = { ...this.selectedScreenshot, ...updatedScreenshot };
        }
        Swal.fire({
          icon: 'success',
          title: updatedScreenshot.user_disliked ? '¡No me gusta añadido!' : 'No me gusta eliminado',
          timer: 1500,
          showConfirmButton: false,
        });
      } catch (error) {
        const errorMsg = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: `No se pudo dar "No me gusta": ${errorMsg}`,
          confirmButtonColor: '#ff0066',
        });
      }
    },
    async rateScreenshot(screenshotId, rating) {
      if (!this.isAuthenticated) {
        Swal.fire({
          icon: 'warning',
          title: 'Inicia sesión',
          text: 'Debes iniciar sesión para calificar.',
          confirmButtonColor: '#ff0066',
        });
        return;
      }

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) throw new Error('No se encontró el token de autenticación.');
        const response = await axios.post(
          `/api/screenshots/${screenshotId}/rate`,
          { rating },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        this.screenshots = this.screenshots.map(s =>
          s.id === screenshotId ? { ...s, average_rating: response.data.average_rating, user_rating: response.data.user_rating } : s
        );
        if (this.selectedScreenshot && this.selectedScreenshot.id === screenshotId) {
          this.selectedScreenshot = { ...this.selectedScreenshot, average_rating: response.data.average_rating, user_rating: response.data.user_rating };
        }
        Swal.fire({
          icon: 'success',
          title: '¡Calificación enviada!',
          timer: 1500,
          showConfirmButton: false,
        });
      } catch (error) {
        const errorMsg = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: `No se pudo enviar la calificación: ${errorMsg}`,
          confirmButtonColor: '#ff0066',
        });
      }
    },
    async deleteComment(commentId) {
      const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff0066',
        cancelButtonColor: '#2a2a3f',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
      });
      if (!result.isConfirmed) return;

      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) throw new Error('No se encontró el token de autenticación.');
        await axios.delete(`/api/screenshots/${this.selectedScreenshot.id}/comments/${commentId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.comments = this.comments.filter(comment => comment.id !== commentId);
        Swal.fire({
          icon: 'success',
          title: '¡Comentario eliminado!',
          timer: 1500,
          showConfirmButton: false,
        });
      } catch (error) {
        const errorMsg = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: `No se pudo eliminar el comentario: ${errorMsg}`,
          confirmButtonColor: '#ff0066',
        });
      }
    },
    openScreenshotModal(screenshot) {
      this.selectedScreenshot = { ...screenshot };
      this.newComment = '';
      this.fetchComments(screenshot.id);
    },
    closeScreenshotModal() {
      this.selectedScreenshot = null;
      this.comments = [];
      this.newComment = '';
    },
    async fetchComments(screenshotId) {
      this.loadingComments = true;
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const response = await axios.get(`/api/screenshots/${screenshotId}/comments`);
        console.log('Respuesta de comentarios:', response.data);
        this.comments = [...response.data];
      } catch (error) {
        console.error('Error al cargar comentarios:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron cargar los comentarios.',
          confirmButtonColor: '#ff0066',
        });
      } finally {
        this.loadingComments = false;
      }
    },
    async submitComment() {
      this.newComment = this.newComment.trim().replace(/\s+/g, ' ');
      console.log('Valor normalizado de newComment:', JSON.stringify(this.newComment), 'Longitud:', this.newComment.length);

      if (!this.newComment) {
        Swal.fire({
          icon: 'warning',
          title: 'Comentario vacío',
          text: 'Por favor, escribe un comentario.',
          confirmButtonColor: '#ff0066',
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
        console.log('Enviando comentario:', this.newComment);
        const response = await axios.post(
          `/api/screenshots/${this.selectedScreenshot.id}/comments`,
          { text: this.newComment },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        console.log('Respuesta del POST:', response.data);
        this.newComment = '';
        setTimeout(() => this.fetchComments(this.selectedScreenshot.id), 500);
        Swal.fire({
          icon: 'success',
          title: '¡Comentario enviado!',
          timer: 1500,
          showConfirmButton: false,
        });
      } catch (error) {
        console.error('Error al enviar comentario:', error);
        const errorMsg = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: `No se pudo enviar el comentario: ${errorMsg}`,
          confirmButtonColor: '#ff0066',
        });
      }
    },
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
        this.$router.push('/login');
        Swal.fire({
          icon: 'success',
          title: 'Sesión cerrada',
          text: 'Has cerrado sesión exitosamente.',
          timer: 2000,
          showConfirmButton: false,
        });
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo cerrar la sesión.',
          confirmButtonColor: '#ff0066',
        });
      }
    },
    logComment() {
      console.log('Comentario actual:', this.newComment);
    },
  },
};
</script>

<style src="@/assets/styles/Screenshots/ScreenshotsView.css" scoped></style>