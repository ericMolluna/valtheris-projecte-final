<template>
  <nav class="nav-container">
    <div class="nav-header">
      <router-link to="/" class="nav-logo">Valtheris</router-link>
      <button class="menu-toggle" @click="toggleMenu">☰</button>
    </div>
    <ul class="nav-links" :class="{ 'nav-links-open': isMenuOpen }">
      <li v-for="(item, index) in navItems" :key="index" class="nav-item">
        <router-link :to="item.route" class="nav-link">{{ item.label }}</router-link>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  name: 'NavBar',
  data() {
    return {
      isLoggedIn: false,
      baseNavItems: [
        { label: 'Comunidad', route: '/comunidad' },
        { label: 'Acerca', route: '/about' }
      ],
      isMenuOpen: false
    };
  },
  computed: {
    navItems() {
      if (this.isLoggedIn) {
        return [...this.baseNavItems, { label: 'Perfil', route: '/profile' }];
      } else {
        return [
          ...this.baseNavItems,
          { label: 'Iniciar Sesión', route: '/login' },
          { label: 'Registrar', route: '/register' }
        ];
      }
    }
  },
  methods: {
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen;
    }
  }
};
</script>

<style src="@/assets/styles/NavBar.css" scoped></style>