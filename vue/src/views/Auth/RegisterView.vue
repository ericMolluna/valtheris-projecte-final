<template>
  <div class="register-container">
    <nav class="nav-container">
      <div class="logo">
        <router-link to="/" class="logo-link">🎮 GameHub</router-link>
      </div>
      <ul>
        <li><router-link to="/"><i class="icon">🏠</i> Volver al Inicio</router-link></li>
        <li><router-link to="/login"><i class="icon">🔑</i> Iniciar Sesión</router-link></li>
      </ul>
    </nav>

    <div class="register-content">
      <h2 class="animated-title">Registro</h2>
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
        <button type="submit">Registrarse</button>
        <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
      </form>
    </div>
   
  </div>
</template>

<script>
import axios from 'axios';
import '@/assets/styles/Auth/RegisterView.css'; 


export default {
  async created() {
    try {
      await axios.get('http://localhost:8000/api/sanctum/csrf-cookie');
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
        console.log('Enviando datos al registro:', { name: this.name, email: this.email, password: this.password });
        const response = await axios.post('http://localhost:8000/api/register', {
          name: this.name,
          email: this.email,
          password: this.password,
        }, {
          withCredentials: true,
        });
        console.log('Respuesta del servidor:', response.data);
        if (response.data && response.data.message) {
          this.successMessage = 'Registro exitoso. Ahora puedes iniciar sesión.';
          this.errorMessage = '';
          setTimeout(() => {
            this.$router.push('/login');
          }, 2000);
        }
      } catch (error) {
        console.error('Error en registro:', error.response ? error.response.data : error.message);
        this.errorMessage = error.response?.data?.message || 'Error en registro: No se pudo conectar con el servidor.';
        this.successMessage = '';
      }
    },
  },
};
</script>
