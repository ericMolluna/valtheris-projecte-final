<template>
  <div class="create-guide-container">
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
      
        <li>
            <router-link to="/comunidad" class="nav-item">
            <span class="icon">👥</span> Comunidad
          </router-link>
      
        
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
      <div class="guide-form-header">
        <h2>{{ editingGuide ? 'Editar Guía' : 'Crear una Nueva Guía' }}</h2>
        <p>Comparte tu conocimiento con la comunidad. Usa el editor para formatear el contenido de tu guía.</p>
      </div>

      <!-- Pestañas de navegación del formulario -->
      <div class="guide-form-tabs">
        <button :class="{ 'active-tab': activeFormTab === 'basic' }" @click="setActiveTab('basic')" aria-label="Información básica">
          Información Básica
        </button>
        <button :class="{ 'active-tab': activeFormTab === 'content' }" @click="setActiveTab('content')" aria-label="Contenido de la guía">
          Contenido
        </button>
        <button :class="{ 'active-tab': activeFormTab === 'preview' }" @click="setActiveTab('preview')" aria-label="Previsualizar guía">
          Previsualizar
        </button>
      </div>

      <div class="guide-form-content">
        <form @submit.prevent="saveGuide">
          <div v-if="activeFormTab === 'basic'">
            <div class="form-group">
              <label for="title">Título de la Guía</label>
              <input
                type="text"
                id="title"
                v-model="guideForm.title"
                placeholder="Escribe un título atractivo..."
                maxlength="100"
                required
                aria-describedby="title-counter"
              />
              <div class="char-counter" :class="{ 'warning': guideForm.title.length > 80 }">
                {{ guideForm.title.length }}/100
              </div>
              <p v-if="formErrors.title" class="error-message">{{ formErrors.title }}</p>
            </div>
            <div class="form-group">
              <label for="branding-image">Imagen de Portada (Requerida)</label>
              <p>Elige una imagen que represente tu guía. Mínimo 196x196 píxeles, se redimensionará si es necesario.</p>
              <div
                class="drop-zone"
                @dragover.prevent="onDragOver"
                @dragleave.prevent="onDragLeave"
                @drop.prevent="handleImageDrop"
                @click="$refs.fileInput.click()"
                :class="{ 'drag-over': isDragging }"
              >
                <p>{{ selectedFileName || 'Arrastra una imagen aquí o haz clic para seleccionar' }}</p>
                <input
                  type="file"
                  id="branding-image"
                  ref="fileInput"
                  @change="handleImageUpload"
                  accept="image/*"
                  hidden
                />
              </div>
              <p v-if="imageError" class="error-message">{{ imageError }}</p>
              <p v-if="imageInfo" class="info-message">{{ imageInfo }}</p>
              <div v-if="imagePreview" class="image-preview">
                <img :src="imagePreview" alt="Vista previa de la portada" @error="handleImageError" />
              </div>
            </div>
            <div class="form-group">
              <label for="description">Descripción</label>
              <p>Describe de qué trata tu guía. Sé breve y atractivo.</p>
              <textarea
                id="description"
                v-model="guideForm.description"
                placeholder="Describe tu guía..."
                rows="4"
                maxlength="500"
                required
                aria-describedby="description-counter"
              ></textarea>
              <div class="char-counter" :class="{ 'warning': guideForm.description.length > 450 }">
                {{ guideForm.description.length }}/500
              </div>
              <p v-if="formErrors.description" class="error-message">{{ formErrors.description }}</p>
            </div>
            <div class="form-group">
              <label for="category">Categoría</label>
              <select id="category" v-model="guideForm.category" required>
                <option value="" disabled>Selecciona una categoría</option>
                <option value="Misiones">Misiones</option>
                <option value="Personajes">Personajes</option>
                <option value="Mecánicas">Mecánicas</option>
                <option value="Consejos">Consejos</option>
              </select>
              <p v-if="formErrors.category" class="error-message">{{ formErrors.category }}</p>
            </div>
          </div>

          <div v-if="activeFormTab === 'content'">
            <div class="form-group">
              <label for="content">Contenido de la Guía</label>
              <p>Usa el editor para formatear tu guía (títulos, listas, enlaces, imágenes, etc.).</p>
              <QuillEditor
                v-model:content="guideForm.content"
                contentType="html"
                :toolbar="quillToolbar"
                placeholder="Escribe el contenido de tu guía..."
              />
              <p v-if="formErrors.content" class="error-message">{{ formErrors.content }}</p>
            </div>
          </div>

          <div v-if="activeFormTab === 'preview'">
            <h3>Vista Previa de la Guía</h3>
            <div class="preview-section">
              <h4>{{ guideForm.title || 'Título no especificado' }}</h4>
              <p><strong>Categoría:</strong> {{ guideForm.category || 'Sin categoría' }}</p>
              <p>{{ guideForm.description || 'Sin descripción' }}</p>
              <div v-if="imagePreview">
                <img :src="imagePreview" alt="Portada de la guía" class="preview-image" @error="handleImageError" />
              </div>
              <div v-else class="no-image-placeholder">Sin imagen</div>
              <div class="preview-content" v-html="renderedContent"></div>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" :disabled="!isFormValid || isSubmitting">
              {{ editingGuide ? 'Actualizar Guía' : 'Crear Guía' }}
            </button>
            <button type="button" @click="cancelEdit" :disabled="isSubmitting">Cancelar</button>
          </div>
        </form>
        <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
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
import { QuillEditor } from '@vueup/vue-quill';
import 'quill/dist/quill.snow.css';
import DOMPurify from 'dompurify';

export default {
  name: 'CreateGuideView',
  components: { QuillEditor },
  data() {
    return {
      isAuthenticated: localStorage.getItem('auth_token') !== null,
      activeFormTab: 'basic',
      guideForm: {
        title: '',
        description: '',
        content: '',
        category: '',
        image: null,
      },
      formErrors: {
        title: '',
        description: '',
        content: '',
        category: '',
        image: '',
      },
      selectedFileName: '',
      imageError: '',
      imageInfo: '',
      imagePreview: null,
      editingGuide: null,
      successMessage: '',
      errorMessage: '',
      user: null,
      isMenuOpen: false,
      isDropdownOpen: false,
      isDragging: false,
      isSubmitting: false,
      quillToolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ header: [1, 2, 3, 4, 5, 6, false] }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link', 'image'],
        ['clean'],
      ],
    };
  },
  computed: {
    isFormValid() {
      return (
        this.guideForm.title.trim() &&
        this.guideForm.description.trim() &&
        this.guideForm.content.trim() &&
        this.guideForm.category &&
        (this.guideForm.image instanceof File || typeof this.guideForm.image === 'string')
      );
    },
    isBasicInfoValid() {
      return (
        this.guideForm.title.trim() &&
        this.guideForm.description.trim() &&
        this.guideForm.category &&
        (this.guideForm.image instanceof File || typeof this.guideForm.image === 'string')
      );
    },
    renderedContent() {
      return this.guideForm.content
        ? DOMPurify.sanitize(this.guideForm.content)
        : 'Sin contenido';
    },
  },
  created() {
    window.addEventListener('storage', this.updateAuthStatus);
    this.checkAuthStatus();
    this.fetchUserData();
    const editGuideId = this.$route.query.editGuide;
    if (editGuideId) {
      this.fetchGuide(editGuideId);
    }
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.updateAuthStatus);
  },
  methods: {
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen;
      if (this.isMenuOpen) this.isDropdownOpen = false;
    },
    toggleDropdown() {
      this.isDropdownOpen = !this.isDropdownOpen;
    },
    async checkAuthStatus() {
      this.isAuthenticated = await isAuthenticated();
      if (!this.isAuthenticated) {
        Swal.fire({
          icon: 'warning',
          title: 'Inicia sesión',
          text: 'Debes iniciar sesión para crear o editar guías.',
          confirmButtonColor: '#ff0066',
        });
        this.$router.push('/login');
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
        this.errorMessage = error.response ? error.response.data.message : error.message;
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.errorMessage,
          confirmButtonColor: '#ff0066',
        });
      }
    },
    async fetchGuide(guideId) {
      try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const response = await axios.get(`/api/guides/${guideId}`);
        const guide = response.data;
        this.editingGuide = guide;
        this.guideForm = {
          title: guide.title || '',
          description: guide.description || '',
          content: guide.content || '',
          category: guide.category || '',
          image: guide.image_url ? `http://localhost:8000${guide.image_url}` : null,
        };
        this.imagePreview = this.guideForm.image;
        this.selectedFileName = guide.image_url ? guide.image_url.split('/').pop() : '';
      } catch (error) {
        this.errorMessage = error.response ? error.response.data.message : error.message;
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.errorMessage,
          confirmButtonColor: '#ff0066',
        });
      }
    },
    onDragOver() {
      this.isDragging = true;
    },
    onDragLeave() {
      this.isDragging = false;
    },
    handleImageDrop(event) {
      this.isDragging = false;
      const file = event.dataTransfer.files[0];
      if (file) {
        this.processImage(file);
      }
    },
    handleImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.processImage(file);
      }
    },
    processImage(file) {
      this.imageError = '';
      this.imageInfo = '';
      this.guideForm.image = null;
      this.selectedFileName = file.name;
      this.imagePreview = null;

      if (!file.type.startsWith('image/')) {
        this.imageError = 'Por favor, selecciona una imagen válida.';
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.imageError,
          confirmButtonColor: '#ff0066',
        });
        this.$refs.fileInput.value = '';
        return;
      }

      const maxSize = 2 * 1024 * 1024; // 2MB
      if (file.size > maxSize) {
        this.imageError = 'La imagen no debe exceder 2MB.';
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.imageError,
          confirmButtonColor: '#ff0066',
        });
        this.$refs.fileInput.value = '';
        return;
      }

      const img = new Image();
      img.src = URL.createObjectURL(file);

      img.onload = () => {
        if (img.width < 196 || img.height < 196) {
          this.imageError = 'La imagen debe ser al menos 196x196 píxeles.';
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: this.imageError,
            confirmButtonColor: '#ff0066',
          });
          this.$refs.fileInput.value = '';
          return;
        }

        this.compressImage(file, 196, 196, 0.8, (compressedFile) => {
          this.guideForm.image = compressedFile;
          this.imageInfo = 'Imagen procesada y lista para subir.';
          this.imagePreview = URL.createObjectURL(compressedFile);
        });
      };

      img.onerror = () => {
        this.imageError = 'No se pudo cargar la imagen.';
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.imageError,
          confirmButtonColor: '#ff0066',
        });
        this.$refs.fileInput.value = '';
      };
    },
    compressImage(file, targetWidth, targetHeight, quality, callback) {
      const img = new Image();
      img.src = URL.createObjectURL(file);
      img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = targetWidth;
        canvas.height = targetHeight;
        ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
        canvas.toBlob(
          (blob) => {
            const compressedFile = new File([blob], file.name, { type: file.type });
            callback(compressedFile);
          },
          file.type,
          quality
        );
      };
    },
    handleImageError(event) {
      event.target.style.display = 'none';
      const placeholder = document.createElement('div');
      placeholder.className = 'no-image-placeholder';
      placeholder.textContent = 'Sin imagen';
      event.target.parentNode.appendChild(placeholder);
    },
    validateForm() {
      this.formErrors = {
        title: '',
        description: '',
        content: '',
        category: '',
        image: '',
      };
      let isValid = true;

      if (!this.guideForm.title.trim()) {
        this.formErrors.title = 'El título es requerido.';
        isValid = false;
      }
      if (!this.guideForm.description.trim()) {
        this.formErrors.description = 'La descripción es requerida.';
        isValid = false;
      }
      if (!this.guideForm.content.trim()) {
        this.formErrors.content = 'El contenido es requerido.';
        isValid = false;
      }
      if (!this.guideForm.category) {
        this.formErrors.category = 'Selecciona una categoría.';
        isValid = false;
      }
      if (!this.guideForm.image) {
        this.formErrors.image = 'La imagen es requerida.';
        isValid = false;
      }

      return isValid;
    },
    setActiveTab(tab) {
      if (tab !== 'basic' && !this.isBasicInfoValid) {
        const missingFields = [];
        if (!this.guideForm.title.trim()) missingFields.push('Título');
        if (!this.guideForm.description.trim()) missingFields.push('Descripción');
        if (!this.guideForm.category) missingFields.push('Categoría');
        if (!this.guideForm.image) missingFields.push('Imagen');

        Swal.fire({
          icon: 'warning',
          title: 'Campos requeridos',
          html: `Completa los siguientes campos en "Información básica":<br><strong>${missingFields.join(', ')}</strong>`,
          confirmButtonColor: '#ff0066',
        });
        return;
      }
      this.activeFormTab = tab;
    },
    async saveGuide() {
      if (!this.validateForm()) {
        Swal.fire({
          icon: 'warning',
          title: 'Campos requeridos',
          text: 'Por favor, completa todos los campos obligatorios.',
          confirmButtonColor: '#ff0066',
        });
        return;
      }

      try {
        this.isSubmitting = true;
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        await axios.get('sanctum/csrf-cookie');
        const token = localStorage.getItem('auth_token');
        if (!token) throw new Error('No estás autenticado.');

        const guideData = new FormData();
        guideData.append('title', this.guideForm.title);
        guideData.append('description', this.guideForm.description);
        guideData.append('content', this.guideForm.content);
        guideData.append('category', this.guideForm.category);

        if (this.guideForm.image instanceof File) {
          guideData.append('image', this.guideForm.image);
        } else if (typeof this.guideForm.image === 'string') {
          guideData.append('image_url', this.guideForm.image);
        }

        const config = {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data',
          },
        };

        let response;
        if (this.editingGuide && this.editingGuide.id) {
          guideData.append('_method', 'PUT');
          response = await axios.post(`/api/guides/${this.editingGuide.id}`, guideData, config);
        } else {
          response = await axios.post('/api/guides', guideData, config);
        }

        this.successMessage = response.data.message || (this.editingGuide ? 'Guía actualizada con éxito' : 'Guía creada con éxito');
        Swal.fire({
          icon: 'success',
          title: '¡Éxito!',
          text: this.successMessage,
          timer: 2000,
          showConfirmButton: false,
        });
        this.$router.push('/comunidad/guias');
      } catch (error) {
        this.errorMessage = error.response
          ? error.response.data.message || `Error ${error.response.status}`
          : error.message;
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: this.errorMessage,
          confirmButtonColor: '#ff0066',
        });
      } finally {
        this.isSubmitting = false;
      }
    },
    cancelEdit() {
      Swal.fire({
        title: '¿Cancelar?',
        text: 'Perderás los cambios no guardados.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff0066',
        cancelButtonColor: '#2a2a3f',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No, continuar',
      }).then((result) => {
        if (result.isConfirmed) {
          this.$router.push('/comunidad/guias');
        }
      });
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
    updateAuthStatus() {
      this.isAuthenticated = localStorage.getItem('auth_token') !== null;
      if (this.isAuthenticated) {
        this.fetchUserData();
      } else {
        this.checkAuthStatus();
      }
    },
  },
};
</script>

<style src="@/assets/styles/Guides/CreateGuideView.css" scoped></style>
