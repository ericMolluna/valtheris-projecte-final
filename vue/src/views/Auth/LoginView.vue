<template>
  <div class="main-container">
    <!-- Navbar -->
    <NavBar />

    <!-- Login Content -->
    <div class="login-content">
      <h2 class="animated-title">Iniciar Sesión</h2>
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" v-model="email" autocomplete="username" required />
        </div>
        <div class="form-group">
          <label for="password">Contraseña:</label>
          <input type="password" id="password" v-model="password" autocomplete="current-password" required />
        </div>
        <button type="submit">Iniciar Sesión</button>
        <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
      </form>
      <div id="g_id_onload" :data-client_id="googleClientId" data-callback="handleGoogleSignIn"
        data-auto_prompt="false">
      </div>
      <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline" data-text="sign_in_with"
        data-shape="rectangular" data-logo_alignment="left">
      </div>
    </div>

    <!-- Footer -->
    <FooterSection />
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import NavBar from '@/components/NavBar.vue';
import FooterSection from '@/components/FooterSection.vue';

export default {
  components: {
    NavBar,
    FooterSection
  },
  data() {
    return {
      email: '',
      password: '',
      successMessage: '',
      errorMessage: '',
      googleClientId: '916302553196-267v6rqu5djak1t94f9ub0eeqv9oh8rn.apps.googleusercontent.com',
    };
  },
  async created() {
    axios.defaults.baseURL = 'http://localhost:8000';
    axios.defaults.withCredentials = true;

    try {
      await axios.get('/sanctum/csrf-cookie');
      console.log('Token CSRF inicializado correctamente');
    } catch (error) {
      console.error('Error al obtener el token CSRF:', error.message);
      this.errorMessage = 'No se pudo conectar con el servidor.';
    }

    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    script.onload = () => {
      console.log('Google Identity Services cargado');
    };
    script.onerror = () => {
      console.error('Error al cargar Google Identity Services');
      this.errorMessage = 'No se pudo cargar Google Sign-In.';
    };
    document.body.appendChild(script);

    window.handleGoogleSignIn = this.handleGoogleSignIn;
  },
  methods: {
    async handleLogin() {
      Swal.fire({
        title: 'Cargando...',
        text: 'Por favor, espera mientras iniciamos sesión.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        },
        customClass: {
          popup: 'swal-custom',
        },
      });

      try {
        const response = await axios.post('/api/login', {
          email: this.email,
          password: this.password,
        });
        console.log('Respuesta del servidor:', response.data);
        if (response.data.token) {
          localStorage.setItem('auth_token', response.data.token);
          this.successMessage = 'Inicio de sesión exitoso.';
          this.errorMessage = '';
          Swal.close();
          setTimeout(() => this.$router.push('/perfil'), 1000);
        }
      } catch (error) {
        console.error('Error en login:', error.response ? error.response.data : error.message);
        this.errorMessage = error.response?.data?.message || 'Error en inicio de sesión.';
        this.successMessage = '';
        Swal.close();
      }
    },
    async handleGoogleSignIn(response) {
      Swal.fire({
        title: 'Cargando...',
        text: 'Iniciando sesión con Google, por favor espera.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        },
        customClass: {
          popup: 'swal-custom',
        },
      });

      try {
        console.log('Iniciando sesión con Google...', response);
        const idToken = response.credential;
        console.log('ID Token:', idToken);

        const serverResponse = await axios.post('/api/google-login', { id_token: idToken });
        console.log('Respuesta del servidor:', serverResponse.data);
        if (serverResponse.data.token) {
          localStorage.setItem('auth_token', serverResponse.data.token);
          this.successMessage = 'Inicio de sesión con Google exitoso.';
          this.errorMessage = '';
          Swal.close();
          setTimeout(() => this.$router.push('/'), 1000);
        }
      } catch (error) {
        console.error('Error al iniciar sesión con Google:', error);
        this.errorMessage = 'Error al iniciar sesión con Google.';
        this.successMessage = '';
        Swal.close();
      }
    },
  },
};
</script>

<style src="@/assets/styles/Auth/LoginView.css" scoped></style>