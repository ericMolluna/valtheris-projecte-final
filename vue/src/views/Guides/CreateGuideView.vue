<template>
    <div class="create-guide-container">
      <!-- Barra de navegación superior -->
      <nav class="nav-container">
        <div class="logo">
          <router-link to="/" class="logo-link">🎮 Valtheris</router-link>
        </div>
        <ul>
          <li>
            <router-link to="/comunidad/guias"><i class="icon">📖</i> Volver a las guias</router-link>
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
        <div class="guide-form-header">
          <h2>{{ editingGuide ? 'Editar Guía' : 'Creando una nueva guía' }}</h2>
          <p>Para empezar con tu guía, rellena los campos de abajo. A continuación, se te pedirá que le añadas secciones.</p>
        </div>
  
        <!-- Pestañas de navegación del formulario -->
        <div class="guide-form-tabs">
          <button :class="{ 'active-tab': activeFormTab === 'basic' }" @click="setActiveTab('basic')">
            Información básica de la guía
          </button>
          <button :class="{ 'active-tab': activeFormTab === 'content' }" @click="setActiveTab('content')">
            Contenido de la guía
          </button>
          <button :class="{ 'active-tab': activeFormTab === 'preview' }" @click="setActiveTab('preview')">
            Previsualizar guía
          </button>
        </div>
  
        <div class="guide-form-content">
          <form @submit.prevent="saveGuide">
            <div v-if="activeFormTab === 'basic'">
              <div class="form-group">
                <label for="title">Introduce el título de tu guía</label>
                <input
                  type="text"
                  id="title"
                  v-model="guideForm.title"
                />
              </div>
              <div class="form-group">
                <label for="branding-image">Selecciona una imagen distintiva (requerido)</label>
                <p>Esta imagen representará a tu guía en la comunidad de GameHub. Cuando los usuarios busquen, exploren o filtren en la lista de guías, esta es la imagen que aparecerá junto a tu guía.</p>
                <p>Para los mejores resultados, utiliza algo que represente la temática o contenido de la guía.</p>
                <p>La imagen debe ser cuadrada y de al menos 196px por 196px. Si es más grande, se redimensionará automáticamente.</p>
                <input
                  type="file"
                  id="branding-image"
                  @change="handleImageUpload"
                  accept="image/*"
                />
                <p v-if="imageError" class="error-message">{{ imageError }}</p>
                <p v-if="imageInfo" class="info-message">{{ imageInfo }}</p>
                <p v-if="imagePreview">
                  <img :src="imagePreview" alt="Vista previa" class="image-preview" @error="handleImageError" />
                </p>
                <span class="file-placeholder">{{ selectedFileName || 'Ningún archivo seleccionado' }}</span>
              </div>
              <div class="form-group">
                        
              </div>
              <div class="form-group">
                <label for="description">Describe tu guía</label>
                <p>Usa este espacio para describir tu guía, qué la hace interesante y cuál es su ámbito. Esto aparecerá como descripción cuando se explore la lista de guías para este juego.</p>
                <textarea
                  id="description"
                  v-model="guideForm.description"
                  rows="3"
                ></textarea>
              </div>
              <div class="form-group">
                <label for="category">Categoría</label>
                <select id="category" v-model="guideForm.category">
                  <option value="">Selecciona una categoría</option>
                  <option value="Misiones">Misiones</option>
                  <option value="Personajes">Personajes</option>
                  <option value="Mecánicas">Mecánicas</option>
                  <option value="Consejos">Consejos</option>
                </select>
              </div>
            </div>
  
            <div v-if="activeFormTab === 'content'">
              <div class="form-group">
                <label for="content">Contenido de la guía</label>
                <textarea
                  id="content"
                  v-model="guideForm.content"
                  rows="10"
                ></textarea>
              </div>
            </div>
  
            <div v-if="activeFormTab === 'preview'">
              <h3>Vista previa de la guía</h3>
              <div class="preview-section">
                <h4>{{ guideForm.title || 'Título no especificado' }}</h4>
                <p><strong>Categoría:</strong> {{ guideForm.category || 'Sin categoría' }}</p>
                <p>{{ guideForm.description || 'Sin descripción' }}</p>
                <div v-if="imagePreview">
                  <img :src="imagePreview" alt="Portada de la guía" class="preview-image" @error="handleImageError" />
                </div>
                <div v-else class="no-image-placeholder">Sin imagen</div>
                <div class="preview-content" v-html="guideForm.content || 'Sin contenido'"></div>
              </div>
            </div>
  
            <div class="form-actions">
              <button type="submit">
                {{ editingGuide ? 'Actualizar Guía' : 'Crear Guía' }}
              </button>
              <button type="button" @click="cancelEdit">Cancelar</button>
            </div>
          </form>
          <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
          <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
        </div>
      </div>
  
    
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import { isAuthenticated } from '@/api/auth';
  import Swal from 'sweetalert2';
  import '@/assets/styles/Guides/CreateGuideView.css'; 

  
  export default {
    name: 'CreateGuideView',
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
        selectedFileName: '',
        imageError: '',
        imageInfo: '',
        imagePreview: null,
        editingGuide: null,
        successMessage: '',
        errorMessage: '',
        user: null,
      };
    },
    computed: {
      isFormValid() {
        // Check if all required fields are filled
        const hasRequiredFields =
          this.guideForm.title.trim() !== '' &&
          this.guideForm.description.trim() !== '' &&
          this.guideForm.content.trim() !== '';
  
        // Image is required for both creating and editing
        const hasImage = this.guideForm.image !== null && (this.guideForm.image instanceof File || typeof this.guideForm.image === 'string');
  
        return hasRequiredFields && hasImage;
      },
      isBasicInfoValid() {
        // Check if all required fields in the "Información básica" tab are filled
        return (
          this.guideForm.title.trim() !== '' &&
          this.guideForm.description.trim() !== '' &&
          this.guideForm.image !== null &&
          (this.guideForm.image instanceof File || typeof this.guideForm.image === 'string')
        );
      },
    },
    created() {
      this.checkAuthStatus();
      this.fetchUserData();
  
      // Check for editGuide query parameter
      const editGuideId = this.$route.query.editGuide;
      if (editGuideId) {
        this.fetchGuide(editGuideId);
      }
    },
    methods: {
      async checkAuthStatus() {
        this.isAuthenticated = await isAuthenticated();
        if (!this.isAuthenticated) {
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
            name: response.data.name,
            email: response.data.email,
          };
        } catch (error) {
          this.errorMessage = error.response ? error.response.data.message : error.message;
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
            image: guide.image || null,
          };
          this.imagePreview = guide.image || null;
        } catch (error) {
          this.errorMessage = error.response ? error.response.data.message : error.message;
        }
      },
      handleImageUpload(event) {
        const file = event.target.files[0];
        this.imageError = '';
        this.imageInfo = '';
        this.guideForm.image = null;
        this.selectedFileName = '';
        this.imagePreview = null;
  
        if (!file) {
          return;
        }
  
        this.selectedFileName = file.name;
        const img = new Image();
        img.src = URL.createObjectURL(file);
  
        img.onload = () => {
          if (img.width < 196 || img.height < 196) {
            Swal.fire({
              icon: 'error',
              title: 'Error en la imagen',
              text: 'La imagen debe ser al menos 196x196 píxeles.',
            });
            this.selectedFileName = '';
            this.imagePreview = null;
            event.target.value = '';
            return;
          }
  
          if (img.width > 196 || img.height > 196) {
            this.resizeImage(img, 196, 196, file.type, (resizedDataUrl) => {
              this.guideForm.image = file;
              this.imageInfo = 'Imagen redimensionada a 196x196 píxeles.';
              this.selectedFileName = file.name;
              this.imagePreview = resizedDataUrl;
            });
          } else {
            this.guideForm.image = file;
            this.imagePreview = URL.createObjectURL(file);
          }
        };
  
        img.onerror = () => {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo cargar la imagen. Por favor, intenta con otro archivo.',
          });
          this.selectedFileName = '';
          this.imagePreview = null;
          event.target.value = '';
        };
      },
      resizeImage(img, targetWidth, targetHeight, mimeType, callback) {
        const canvas = document.createElement('canvas');
        canvas.width = targetWidth;
        canvas.height = targetHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
        const resizedDataUrl = canvas.toDataURL(mimeType);
        callback(resizedDataUrl);
      },
      handleImageError(event) {
        event.target.style.display = 'none';
        const placeholder = document.createElement('div');
        placeholder.className = 'no-image-placeholder';
        placeholder.textContent = 'Sin imagen';
        event.target.parentNode.appendChild(placeholder);
      },
      setActiveTab(tab) {
        // If trying to switch to 'content' or 'preview', validate the 'basic' tab first
        if (tab === 'content' || tab === 'preview') {
          if (!this.isBasicInfoValid) {
            // Show SweetAlert2 pop-up with missing fields
            const missingFields = [];
            if (!this.guideForm.title.trim()) missingFields.push('Título');
            if (!this.guideForm.description.trim()) missingFields.push('Descripción');
            if (!this.guideForm.image) missingFields.push('Imagen');
  
            Swal.fire({
              icon: 'warning',
              title: 'Campos requeridos',
              html: `Por favor, completa los siguientes campos en "Información básica":<br><strong>${missingFields.join(', ')}</strong>`,
            });
            return; // Prevent tab switch
          }
        }
        this.activeFormTab = tab;
      },
      async saveGuide() {
        // Validate all fields before saving
        if (!this.isFormValid) {
          const missingFields = [];
          if (!this.guideForm.title.trim()) missingFields.push('Título');
          if (!this.guideForm.description.trim()) missingFields.push('Descripción');
          if (!this.guideForm.image) missingFields.push('Imagen');
          if (!this.guideForm.content.trim()) missingFields.push('Contenido');
  
          Swal.fire({
            icon: 'warning',
            title: 'Campos requeridos',
            html: `Por favor, completa los siguientes campos:<br><strong>${missingFields.join(', ')}</strong>`,
          });
          return;
        }
  
        try {
          console.log('Fetching CSRF token...');
          axios.defaults.baseURL = 'http://localhost:8000';
          axios.defaults.withCredentials = true;
          await axios.get('sanctum/csrf-cookie');
          console.log('CSRF token fetched successfully.');
  
          const token = localStorage.getItem('auth_token');
          console.log('Auth token:', token);
  
          if (!token) {
            this.errorMessage = 'No estás autenticado. Por favor, inicia sesión.';
            this.$router.push('/login');
            return;
          }
  
          const guideData = new FormData();
          guideData.append('title', this.guideForm.title);
          guideData.append('description', this.guideForm.description);
          guideData.append('content', this.guideForm.content);
          guideData.append('category', this.guideForm.category);
  
          if (this.guideForm.image) {
            if (this.guideForm.image instanceof File) {
              console.log('Uploading new image file');
              guideData.append('image', this.guideForm.image);
            } else if (typeof this.guideForm.image === 'string') {
              console.log('Using existing image URL:', this.guideForm.image);
              guideData.append('image_url', this.guideForm.image);
            }
          } else {
            console.log('No image provided');
            this.errorMessage = 'Por favor, selecciona una imagen.';
            return;
          }
  
          const config = {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'multipart/form-data',
            },
          };
  
          if (this.editingGuide && this.editingGuide.id) {
            console.log('Updating guide with ID:', this.editingGuide.id);
            guideData.append('_method', 'PUT');
            const response = await axios.post(`/api/guides/${this.editingGuide.id}`, guideData, config);
            console.log('Update response:', response.data);
            this.successMessage = response.data.message || 'Guía actualizada con éxito';
          } else {
            console.log('Creating new guide...');
            const response = await axios.post('/api/guides', guideData, config);
            console.log('Create response:', response.data);
            this.successMessage = response.data.message || 'Guía creada con éxito';
          }
  
          this.$router.push('/comunidad/guias');
        } catch (error) {
          console.error('Error saving guide:', error);
          if (error.response) {
            console.error('Response data:', error.response.data);
            console.error('Response status:', error.response.status);
          }
          this.errorMessage = error.response
            ? error.response.data.message
            : 'Error al guardar la guía';
        }
      },
      cancelEdit() {
        this.$router.push('/comunidad/guias');
      },
    },
  };
  </script>
  