<template>
  <nav class="nav-container">
    <div class="nav-header">
      <router-link to="/" class="nav-logo">Valtheris</router-link>
      <button class="menu-toggle" @click="toggleMenu">☰</button>
    </div>
    <ul class="nav-links" :class="{ 'nav-links-open': isMenuOpen }">
      <li v-for="(item, index) in navItems" :key="index" class="nav-item">
        <router-link
          v-if="item.route"
          :to="item.route"
          class="nav-link"
        >{{ item.label }}</router-link>
        <button
          v-if="item.action"
          class="nav-link"
          @click="item.action"
        >{{ item.label }}</button>
      </li>
    </ul>
  </nav>
</template>

<script>
import { isAuthenticated, logout } from '@/api/auth';

export default {
  name: 'NavBar',
  data() {
    return {
      isLoggedIn: isAuthenticated(),
      isMenuOpen: false,
      baseNavItems: [
        { label: 'Comunidad', route: '/comunidad' },
        { label: 'Acerca', route: '/about' }
      ]
    };
  },
  computed: {
    navItems() {
      if (this.isLoggedIn) {
        return [
          ...this.baseNavItems,
          { label: 'Perfil', route: '/perfil' }, // Ajusta a /perfil
          { label: 'Cerrar Sesión', action: this.handleLogout }
        ];
      } else {
        return [
          ...this.baseNavItems,
          { label: 'Iniciar Sesión', route: '/login' },
          { label: 'Registrarse', route: '/register' }
        ];
      }
    }
  },
  methods: {
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen;
    },
    handleLogout() {
      logout();
      this.isLoggedIn = false; // Actualiza inmediatamente
      this.$router.push('/login');
    },
    updateAuthStatus() {
      console.log('Actualizando estado de autenticación:', isAuthenticated());
      this.isLoggedIn = isAuthenticated();
    }
  },
  created() {
    window.addEventListener('authChange', this.updateAuthStatus);
    this.updateAuthStatus(); // Verifica el estado inicial
  },
  beforeUnmount() {
    window.removeEventListener('authChange', this.updateAuthStatus);
  }
};
</script>

<style src="@/assets/styles/NavBar.css" scoped></style>