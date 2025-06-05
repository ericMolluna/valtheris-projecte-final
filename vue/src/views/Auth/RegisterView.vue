<template>
  <div>
    <NavBar />
    
    <div class="register-container">
      <h2 class="register-title">Registro</h2>
      <form @submit.prevent="handleRegister" class="register-form">
        <div class="form-group">
          <label for="name">Nombre:</label>
          <input type="text" id="name" v-model="name" required />
        </div>
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" v-model="email" autocomplete="username" required />
        </div>
        <div class="form-group">
          <label for="password">Contraseña:</label>
          <input type="password" id="password" v-model="password" autocomplete="new-password" required />
        </div>
        <button type="submit" class="cta-button">Registrarse</button>
        <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
      </form>
    </div>
    
    <FooterSection />
  </div>
</template>

<script>
import axios from 'axios';
import NavBar from '@/components/NavBar.vue';
import FooterSection from '@/components/FooterSection.vue';

export default {
  components: {
    NavBar,
    FooterSection,
  },
  async created() {
    try {
      await axios.get('http://localhost:8000/sanctum/csrf-cookie');
      console.log('Token CSRF inicializado correctamente');
    } catch (error) {
      console.error('Error al obtener el token CSRF:', error);
    }
  },
  data() {
    return {
      name: '',
      email: '',
      password: '',
      successMessage: '',
      errorMessage: '',
    };
  },
  methods: {
    async handleRegister() {
      try {
        const response = await axios.post('http://localhost:8000/api/register', {
          name: this.name,
          email: this.email,
          password: this.password,
        }, {
          withCredentials: true,
        });
        if (response.data?.message) {
          this.successMessage = 'Registro exitoso. Ahora puedes iniciar sesión.';
          this.errorMessage = '';
          setTimeout(() => this.$router.push('/login'), 2000);
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Error al registrar.';
        this.successMessage = '';
      }
    },
  },
};
</script>

<style scoped>
@import '@/assets/styles/Auth/RegisterView.css';
</style>
