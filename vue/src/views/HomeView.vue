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

    <!-- ROADMAP SECTION -->
    <section class="roadmap-section">
      <h2 class="roadmap-title">🗺️ Roadmap Valtheris</h2>
      <div class="current-phase-text">
        Estem a la <span>Fase Actual (Pre-Alpha)</span> – Febrer 2025 a Maig 2025
      </div>
      <div class="roadmap-container">
        <div class="roadmap-timeline">
          <div
            v-for="phase in phases"
            :key="phase.title"
            class="roadmap-orb"
            :class="{ current: phase.current }"
            @click="openModal(phase)"
          >
            <span class="orb-icon">{{ phase.icon }}</span>
          </div>
        </div>
      </div>

      <!-- Modal for Phase Details -->
      <div v-if="selectedPhase" class="modal-overlay">
        <div class="modal-content">
          <h3 class="phase-title">{{ selectedPhase.title }}</h3>
          <p class="phase-date">{{ selectedPhase.date }}</p>
          <ul class="phase-details">
            <li v-for="detail in selectedPhase.details" :key="detail">{{ detail }}</li>
          </ul>
          <button class="modal-close" @click="closeModal">X</button>
        </div>
      </div>

      <div class="roadmap-cta">
        <a href="https://github.com/ericMolluna/valtheris-projecte-final" class="cta-button" target="_blank">
          Sigue el proyecto en GitHub
        </a>
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
      userTier: 'tier3',
      isAuthenticated: true,
      selectedPhase: null,
      phases: [
        {
          title: 'Fase Actual (Pre-Alpha)',
          date: 'Febrer 2025 - Maig 2025',
          icon: '✅',
          current: true,
          details: [
            'Desenvolupament inicial del mapa.',
            'Primer sistema d’esdeveniments interactius.',
            'Funcionalitat bàsica de guardat (parcial).',
            'Plataforma web amb login i estructura de guies/posts.',
            'Sincronització parcial entre joc i web.'
          ]
        },
        {
          title: 'Fase 1.5 – Millores Pre-Alpha',
          date: 'Juny 2025 - Agost 2025',
          icon: '📌',
          current: false,
          details: [
            'Sistema de guardat complet funcional per usuari.',
            'Expansió de mapa (mínim 2 zones noves).',
            'Afegir NPCs bàsics amb diàlegs estàtics.',
            'Millorar la interacció web-joc (API REST Laravel ↔ RPGJS).',
            'Primeres proves internes de compatibilitat amb dispositius mòbils.',
            'Música.'
          ]
        },
        {
          title: 'Fase 2 – Alpha',
          date: 'Setembre 2025 - Desembre 2025',
          icon: '🚧',
          current: false,
          details: [
            'Mapa ampliat amb diferents biomes/zones.',
            'Sistema de missions bàsiques (fetch quests, exploració).',
            'Diàlegs dinàmics amb ramificació simple.',
            'Inici de narrativa i lore del món.',
            'Sistema de guardat 100% operatiu.',
            'Millores visuals amb suport Gamepad i dispositius tàctils.',
            'Proves de joc local multijugador.',
            'Llençament Alpha tancada amb grup de testers reduït.'
          ]
        },
        {
          title: 'Fase 3 – Beta',
          date: 'Gener 2026 - Març 2026',
          icon: '🧪',
          current: false,
          details: [
            'Nova tanda de missions + primers combats.',
            'Afegir més NPCs amb rol (botiguers, mentors, enemics).',
            'Sistema de combat inicial (PvE).',
            'Assoliments i progressos integrats a la web.',
            'Sistema de guies i mapes compartits 100% funcional.',
            'Integració completa amb Laravel (rànquing, perfil d’usuari, historial de partides).',
            'Llençament de Beta oberta per obtenir feedback massiu.'
          ]
        },
        {
          title: 'Fase 4 – Demo Pública',
          date: 'Abril 2026 - Juny 2026',
          icon: '🎮',
          current: false,
          details: [
            'Versió curta de la història principal (30-45 min jugables).',
            'Sistema de combat millorat i més complexitat en les missions.',
            'Suport complet per a Gamepad i mòbils.',
            'Web optimitzada per rebre i visualitzar feedback/bugs.',
            'Afegit de música i sons ambientals.',
            'Publicació de demo al web amb accés per a tothom.'
          ]
        },
        {
          title: 'Fase 5 – Early Access / Joc Complet',
          date: 'Juliol 2026 - Septembre 2026',
          icon: '🧩',
          current: false,
          details: [
            'Història principal completa amb mínim 3-4 capítols o zones.',
            'Sistemes de craft, inventari, equipament i progressió.',
            'Correcció de bugs, optimització de rendiment.',
            'Tests intensius en diversos dispositius i navegadors.'
          ]
        },
        {
          title: 'Fase 6 – Llançament Final',
          date: '2027-',
          icon: '🏁',
          current: false,
          details: [
            'Llançament oficial al web amb registre d’usuaris obert.',
            'Documentació i tutorials oficials a la web.',
            'Sistema de moderació i gestió de comunitat.',
            'Campanya de comunicació: xarxes, comunitats RPG, Discord.',
            'Inici de roadmap per a expansions o DLCs post-llançament.'
          ]
        }
      ]
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
  },
  methods: {
    openModal(phase) {
      this.selectedPhase = phase;
    },
    closeModal() {
      this.selectedPhase = null;
    }
  }
}
</script>

<style src="@/assets/styles/HomeView.css" scoped></style>