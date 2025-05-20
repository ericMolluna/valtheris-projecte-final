<template>
  <div class="edit-profile-container">
    <!-- Barra de navegación -->
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
        <li><router-link to="/comunidad"><i class="icon">👥</i> <span>Comunidad</span></router-link></li>
        <li><router-link to="/perfil"><i class="icon">👤</i> <span>Perfil</span></router-link></li>
      </ul>
    </nav>

    <!-- Contenedor principal con sidebar y formulario -->
    <div class="edit-profile-content">
      <!-- Sidebar -->
      <aside class="sidebar">
        <h3 class="sidebar-title">Editar Perfil</h3>
        <ul class="sidebar-nav">
          <li>
            <a href="#" :class="{ active: activeTab === 'general' }" @click.prevent="activeTab = 'general'">General</a>
          </li>
          <li>
            <a href="#" :class="{ active: activeTab === 'avatar' }" @click.prevent="activeTab = 'avatar'">Avatar</a>
          </li>
          <li>
            <a href="#" :class="{ active: activeTab === 'theme' }" @click.prevent="activeTab = 'theme'">Tema</a>
          </li>
        </ul>
      </aside>

      <!-- Sección de edición -->
      <div class="edit-profile-section">
        <!-- General Tab -->
        <div v-if="activeTab === 'general'">
          <h2 class="section-title">General</h2>
          <p class="section-description">
            Configura el nombre y los detalles de tu perfil. Proporcionar información adicional, como tu nombre real, puede ayudar a tus amigos a encontrarte en la comunidad de GameHub.
          </p>
          <div v-if="loading" class="loading-message">Cargando datos...</div>
          <div v-else-if="error" class="error-message">Error: {{ error }}</div>
          <form v-else @submit.prevent="updateProfile">
            <div class="form-group">
              <label for="name">Nombre de perfil</label>
              <input
                type="text"
                id="name"
                v-model="editableUser.name"
                placeholder="Introduce tu nuevo nombre"
                required
              />
            </div>
            <div class="form-group">
              <label for="email">Correo</label>
              <input
                type="email"
                id="email"
                v-model="editableUser.email"
                placeholder="Introduce tu nuevo correo"
                required
              />
            </div>
            <div class="form-group">
              <label for="password">Contraseña (dejar en blanco si no deseas cambiarla)</label>
              <input
                type="password"
                id="password"
                v-model="editableUser.password"
                placeholder="Introduce tu nueva contraseña"
              />
            </div>
            <button type="submit" class="save-btn" :disabled="isUpdating">
              {{ isUpdating ? 'Guardando...' : 'Guardar Cambios' }}
            </button>
            <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
            <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
          </form>
        </div>

        <!-- Avatar Tab -->
        <div v-if="activeTab === 'avatar'">
          <h2 class="section-title">Avatar</h2>
          <p class="section-description">
            Sube una nueva imagen para personalizar tu avatar en GameHub.
          </p>
          <div v-if="loading" class="loading-message">Cargando datos...</div>
          <div v-else-if="error" class="error-message">Error: {{ error }}</div>
          <div v-else class="avatar-section">
            <div class="avatar-preview">
              <img v-if="user?.avatar" :src="user.avatar" alt="User Avatar" class="avatar-img" @error="handleAvatarError" />
              <i v-else class="avatar-icon">👤</i>
            </div>
            <div class="avatar-upload">
              <input type="file" accept="image/*" @change="handleAvatarUpload" ref="avatarInput" style="display: none" />
              <button type="button" class="upload-btn" @click="$refs.avatarInput.click()" :disabled="avatarUploading">
                <i class="icon">📸</i> {{ avatarUploading ? 'Subiendo...' : 'Cambiar Avatar' }}
              </button>
            </div>
            <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
            <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
          </div>
        </div>

        <!-- Theme Tab (Placeholder) -->
        <div v-if="activeTab === 'theme'">
          <h2 class="section-title">Tema</h2>
          <p class="section-description">
            Personaliza el tema de tu perfil (próximamente).
          </p>
        </div>

        <div class="back-link">
          <router-link to="/perfil">Regresar a tu perfil</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import '@/assets/styles/Profile/EditProfileView.css';

export default {
  name: 'EditProfileView',
  data() {
    return {
      user: null,
      editableUser: { name: '', email: '', password: '', realName: '', customUrl: '' },
      userTier: 'Tier 1', // Valor por defecto
      activeTab: 'general',
      loading: true,
      error: null,
      successMessage: '',
      errorMessage: '',
      avatarUploading: false,
      isUpdating: false,
    };
  },
  computed: {
    profileUrl() {
      return this.editableUser.customUrl
        ? `https://gamehub.com/profiles/${this.editableUser.customUrl}`
        : 'https://gamehub.com/profiles/[tu-url-personalizada]';
    },
  },
  async created() {
    await this.fetchUserData();
  },
  methods: {
    async fetchUserData() {
      try {
        this.loading = true;
        this.error = null;
        const token = localStorage.getItem('auth_token');
        if (!token) {
          this.error = 'No estás autenticado.';
          this.$router.push('/login');
          return;
        }

        const response = await axios.get('http://localhost:8000/api/user', {
          headers: {
            Authorization: `Bearer ${token}`,
          },
          withCredentials: true,
        });

        this.user = {
          name: response.data.name,
          email: response.data.email,
          avatar: response.data.avatar ? `http://localhost:8000${response.data.avatar}` : null,
          tier: response.data.tier || 'Tier 1', // Obtener tier desde la API
        };
        this.userTier = this.user.tier; // Asignar el tier del usuario
        this.editableUser = {
          name: this.user.name,
          email: this.user.email,
          password: '',
          realName: this.user.realName || '',
          customUrl: this.user.customUrl || '',
        };
      } catch (error) {
        const errorMsg = error.response
          ? `${error.response.status}: ${error.response.data.message || error.response.statusText}`
          : error.message;
        console.error('Error al cargar datos:', error.response ? error.response : error);
        this.error = errorMsg;
        setTimeout(() => this.$router.push('/login'), 3000);
      } finally {
        this.loading = false;
      }
    },
    async updateProfile() {
      try {
        this.isUpdating = true;
        this.errorMessage = '';
        this.successMessage = '';

        const token = localStorage.getItem('auth_token');
        const data = {
          name: this.editableUser.name,
          email: this.editableUser.email,
          realName: this.editableUser.realName,
          customUrl: this.editableUser.customUrl,
        };
        if (this.editableUser.password) {
          data.password = this.editableUser.password;
        }

        const response = await axios.put('http://localhost:8000/api/user', data, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
          withCredentials: true,
        });

        console.log('Perfil actualizado:', response.data);
        this.successMessage = 'Perfil actualizado con éxito.';
        this.errorMessage = '';
        // Actualizar el tier si la API lo devuelve
        this.user.tier = response.data.tier || this.user.tier;
        this.userTier = this.user.tier;
        setTimeout(() => {
          this.successMessage = '';
          this.$router.push('/perfil');
        }, 2000);
      } catch (error) {
        const errorMsg = error.response
          ? error.response.data.message || error.response.statusText
          : error.message;
        console.error('Error al actualizar perfil:', error.response ? error.response : error);
        this.errorMessage = errorMsg;
        this.successMessage = '';
        setTimeout(() => (this.errorMessage = ''), 3000);
      } finally {
        this.isUpdating = false;
      }
    },
    async handleAvatarUpload(event) {
      const file = event.target.files[0];
      if (!file) return;

      if (!file.type.startsWith('image/')) {
        this.errorMessage = 'Por favor, selecciona una imagen válida';
        return;
      }

      if (file.size > 5 * 1024 * 1024) {
        this.errorMessage = 'La imagen debe ser menor a 5MB';
        return;
      }

      const formData = new FormData();
      formData.append('avatar', file);

      try {
        this.avatarUploading = true;
        this.errorMessage = '';
        this.successMessage = '';

        const token = localStorage.getItem('auth_token');
        await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
          withCredentials: true,
        });
        const response = await axios.post('http://localhost:8000/api/upload-avatar', formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data',
          },
          withCredentials: true,
        });

        // Update the avatar URL with a cache-busting query parameter
        const timestamp = new Date().getTime();
        this.user.avatar = `${response.data.avatar_url}?t=${timestamp}`;
        this.successMessage = 'Avatar actualizado exitosamente';
      } catch (error) {
        console.error('Error uploading avatar:', error);
        this.errorMessage = error.response?.data?.message || 'Error al subir el avatar';
      } finally {
        this.avatarUploading = false;
        this.$refs.avatarInput.value = '';
      }
    },
    handleAvatarError(event) {
      event.target.style.display = 'none';
      const placeholder = document.createElement('i');
      placeholder.className = 'avatar-icon';
      placeholder.textContent = '👤';
      event.target.parentNode.appendChild(placeholder);
    },
  },
};
</script>

<style scoped>
/* Estilos necesarios para la barra de navegación, copiados y adaptados de ProfileView.vue */
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
  font-size: 1.2em;
  vertical-align: middle;
  transition: none;
}

.nav-container ul li a:hover {
  color: #e63946;
}

.nav-container ul li a:hover .icon {
  transform: none;
}

.nav-container ul li a:hover span {
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

.coming-soon span {
  color: #999;
  font-style: italic;
  cursor: not-allowed;
}

</style>