<template>
  <div>
    <NavBar />
    <div class="login-container">
      <h2 class="login-title">Iniciar Sesión</h2>
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" v-model="email" autocomplete="username" required />
        </div>
        <div class="form-group">
          <label for="password">Contraseña:</label>
          <input type="password" id="password" v-model="password" autocomplete="current-password" required />
        </div>
        <button type="submit" class="cta-button">Iniciar Sesión</button>
        <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
      </form>
      <div id="g_id_onload" :data-client_id="googleClientId" data-callback="handleGoogleSignIn" data-auto_prompt="false"></div>
      <div class="g_id_signin" data-type="standard" data-size="large" data-theme="filled_black" data-text="continue_with" data-shape="pill" data-logo_alignment="left"></div>
    </div>
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
    FooterSection,
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
    window.handleGoogleSignIn = this.handleGoogleSignIn.bind(this);

    axios.defaults.baseURL = 'http://localhost:8000';
    axios.defaults.withCredentials = true;

    try {
      await axios.get('/sanctum/csrf-cookie');
      console.log('CSRF token initialized successfully');
    } catch (error) {
      console.error('Error fetching CSRF token:', error.message);
      this.errorMessage = 'Could not connect to the server.';
    }

    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    script.onload = () => {
      console.log('Google Identity Services loaded successfully');
    };
    script.onerror = () => {
      console.error('Error loading Google Identity Services');
      this.errorMessage = 'Failed to load Google Sign-In.';
    };
    document.body.appendChild(script);
  },
  beforeUnmount() {
    delete window.handleGoogleSignIn;
  },
  methods: {
    async handleGoogleSignIn(response) {
      console.log('Google Sign-In response:', JSON.stringify(response, null, 2));
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
        const idToken = response.credential;
        if (!idToken) {
          console.error('No ID token received in response');
          throw new Error('No ID token received');
        }
        console.log('ID Token:', idToken);
        const serverResponse = await axios.post('/api/google-login', { id_token: idToken });
        console.log('Server response:', serverResponse.data);
        if (serverResponse.data.token) {
          localStorage.setItem('auth_token', serverResponse.data.token);
          this.successMessage = 'Inicio de sesión con Google exitoso.';
          this.errorMessage = '';
          Swal.close();
          setTimeout(() => this.$router.push('/'), 1000);
        }
      } catch (error) {
        console.error('Google Sign-In error:', error.response ? error.response.data : error.message);
        this.errorMessage = error.response?.data?.message || 'Error al iniciar sesión con Google.';
        this.successMessage = '';
        Swal.close();
      }
    },
  },
};
</script>

<style scoped>
/* Import the external CSS file */
@import '@/assets/styles/Auth/LoginView.css';
</style>