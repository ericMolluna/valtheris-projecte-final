<template>
  <div class="main-container">
    <!-- NAV -->
    <NavBar :items="navItems" />

    <!-- HERO SECTION -->
    <section class="hero-section">
      <div class="hero-content">
        <h1 class="game-title">Valtheris: El Renacer del Cristal</h1>
        <p class="game-description">
          Un RPG clásico con esencia retro donde los cristales deciden el destino del mundo.
          Explora tierras antiguas, reúne héroes y enfrenta oscuras amenazas en turnos estratégicos.
        </p>
        <router-link to="/comunidad" class="cta-button">Únete a la comunidad</router-link>
      </div>
      <div class="hero-image">
        <img src="@/assets/hero-valtheris.png" alt="Arte del juego" />
      </div>
    </section>

    <!-- FOOTER -->
    <FooterSection />
  </div>
</template>

<script>
import NavBar from '@/components/NavBar.vue'
import FooterSection from '@/components/FooterSection.vue'

export default {
  name: 'HomeView',
  components: {
    NavBar,
    FooterSection,
  },
  data() {
    return {
      userTier: 'tier3',       // Cambia según tu lógica real
      isAuthenticated: true,   // Cambia según tu lógica real
    }
  },
  computed: {
    navItems() {
      return [
        this.userTier === 'tier3'
          ? { label: '⚔️ Jugar', route: '/juego' }
          : { label: '⚔️ Próximamente', route: '#' },
        { label: '👥 Comunidad', route: '/comunidad' },
        { label: '📜 Sobre nosotros', route: '/creadores' },
        this.isAuthenticated
          ? { label: '👤 Perfil', route: '/perfil' }
          : { label: '🔑 Login', route: '/login' }
      ]
    }
  }
}
</script>

<style src="@/assets/styles/HomeView.css"></style>