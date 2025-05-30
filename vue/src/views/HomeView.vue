<template>
  <div class="main-container">
    <!-- Navbar -->
    <NavBar />

    <!-- Home Content -->
    <div class="home-view">
      <!-- Hero Section -->
      <section class="hero-section">
        <div class="hero-content">
          <h1 class="game-title">Valtheris: El despertar del código</h1>
          <p class="game-description">
            Sumérgete en un RPG de píxeles donde la programación y la magia colisionan. Resuelve acertijos, lucha contra enemigos y descubre los secretos de un reino donde cada línea de código puede cambiar tu destino.
          </p>
          <a href="https://discord.gg/valtheris" class="cta-button">Únete a la comunidad</a>
        </div>
        <div class="hero-image">
          <!-- Temporarily commented out due to missing hero-image.png -->
          <!-- <img src="@/assets/hero-image.png" alt="Valtheris Hero Image" /> -->
        </div>
      </section>

      <!-- Roadmap Section -->
      <section class="roadmap-section">
        <h2 class="roadmap-title">Nuestra Hoja de Ruta</h2>
        <p class="current-phase-text">Fase actual: Pre-Alpha</p>
        <div class="roadmap-container">
          <div class="roadmap-timeline">
            <div
              v-for="(phase, index) in roadmapPhases"
              :key="index"
              class="roadmap-orb"
              :class="{ current: phase.current }"
              @click="openModal(index)"
            >
              <span class="orb-icon">{{ phase.icon }}</span>
            </div>
          </div>
        </div>
        <div class="roadmap-cta">
          <a href="https://github.com/valtheris" class="cta-button">Visita el repositorio en GitHub</a>
        </div>
      </section>

      <!-- Zone Carousel Section -->
      <section class="zone-carousel-section">
        <h2 class="zone-title">Descubre lo que visitarás</h2>
        <div class="carousel-wrapper">
          <button class="carousel-arrow left-arrow" @click="prevZone">
            <span>⬅</span>
          </button>
          <div class="zone-carousel-container">
            <div class="zone-carousel-wrapper" :style="{ transform: `translateX(-${currentZoneIndex * 33.33}%)` }">
              <div v-for="(zone, index) in zones" :key="index" class="zone-card">
                <img :src="zone.image" :alt="zone.title" class="zone-image" />
                <div class="zone-content">
                  <h3 class="zone-name">{{ zone.title }}</h3>
                  <p class="zone-description">{{ zone.description }}</p>
                </div>
              </div>
            </div>
          </div>
          <button class="carousel-arrow right-arrow" @click="nextZone">
            <span>➡</span>
          </button>
        </div>
      </section>

      <!-- Modal -->
      <div v-if="selectedPhase !== null" class="modal-overlay" @click="closeModalOnOverlayClick">
        <div class="modal-content" @click.stop>
          <button class="modal-close" @click="closeModal">X</button>
          <h3 class="phase-title">{{ roadmapPhases[selectedPhase].title }}</h3>
          <p class="phase-date">{{ roadmapPhases[selectedPhase].date }}</p>
          <ul class="phase-details">
            <li v-for="(detail, index) in roadmapPhases[selectedPhase].details" :key="index">
              {{ detail }}
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <FooterSection />
  </div>
</template>

<script>
import NavBar from '@/components/NavBar.vue';
import FooterSection from '@/components/FooterSection.vue';

export default {
  name: 'HomeView',
  components: {
    NavBar,
    FooterSection
  },
  data() {
    return {
      roadmapPhases: [
        {
          title: 'Fase 1: Conceptualización',
          date: 'Q1 2023',
          details: [
            'Definición del concepto del juego.',
            'Creación del lore inicial y mecánicas.',
            'Prototipo básico de jugabilidad.'
          ],
          icon: '📝',
          current: false
        },
        {
          title: 'Fase 2: Pre-Alpha',
          date: 'Q3 2023',
          details: [
            'Desarrollo de los assets iniciales.',
            'Implementación de mecánicas de programación.',
            'Primeras pruebas internas.'
          ],
          icon: '🎨',
          current: true
        },
        {
          title: 'Fase 3: Alfa',
          date: 'Q2 2024',
          details: [
            'Lanzamiento de la versión alfa.',
            'Feedback de la comunidad.',
            'Ajustes basados en las pruebas.'
          ],
          icon: '🛠️',
          current: false
        },
        {
          title: 'Fase 4: Beta Cerrada',
          date: 'Q4 2024',
          details: [
            'Beta cerrada para testers.',
            'Optimización del rendimiento.',
            'Corrección de bugs.'
          ],
          icon: '🔒',
          current: false
        },
        {
          title: 'Fase 5: Beta Abierta',
          date: 'Q2 2025',
          details: [
            'Beta abierta para todos.',
            'Eventos comunitarios.',
            'Preparación para el lanzamiento.'
          ],
          icon: '🌐',
          current: false
        },
        {
          title: 'Fase 6: Lanzamiento',
          date: 'Q4 2025',
          details: [
            'Lanzamiento oficial del juego.',
            'Soporte post-lanzamiento.',
            'Expansión del lore.'
          ],
          icon: '🚀',
          current: false
        },
        {
          title: 'Fase 7: Expansión',
          date: '2026',
          details: [
            'Nuevos contenidos y DLCs.',
            'Eventos estacionales.',
            'Mejoras continuas.'
          ],
          icon: '🌟',
          current: false
        }
      ],
      selectedPhase: null,
      zones: [
        {
          title: 'Esplanada Inicial',
          description: 'Pueblo donde vive el protagonista, con casas de vecinos, comercios y un diseño simple pero reconfortante para una primera zona de aventura. Colores claros, con mucho verde y vegetación.',
          image: '@/assets/zone-esplanada.jpg'
        },
        {
          title: 'Zona Nevada',
          description: 'Tras una larga bajada por el desierto, encontrarás una zona muy fría, con colores fríos y casas congeladas en ruinas. Una zona muy oscura en contraste con la claridad que se ve desde lejos. Hay algunos comerciantes, pero nadie para ayudar. Solo se ve una gran montaña nevada y muchas más escarpadas.',
          image: '@assets/zone-nevada.jpg'
        },
        {
          title: 'Pujat Muntanyista',
          description: 'Tras salir del pueblo y pasar junto a un río, llegas a una subida vertiginosa por una montaña muy elevada que te hará dar muchas vueltas. Una zona con mucho verde como la de origen y toques marrones por los lados escarpados. Se entrevé una cascada mientras subes, con mucho movimiento animal.',
          image: '@/assets/zone-muntanyista.jpg'
        },
        {
          title: 'Cementerio Críptico',
          description: 'Junto al pueblo hay un camino bloqueado por árboles que lleva a una zona muy oscura con un cementerio y una cripta tenebrosa. Se dice que dentro hay una mazmorra llena de tesoros y monstruos. Esta zona tiene una ambientación muy oscura y tétrica, con estructuras sombrías y plantas muertas por todos lados. Entrar en la cripta te lleva a una mazmorra iluminada por antorchas y llena de trampas.',
          image: '@/assets/zone-cementerio.jpg'
        },
        {
          title: 'Ciudad Desértica',
          description: 'Bajo las antiguas ruinas de una civilización encuentras una tribu del desierto, con casas de arena y tiendas de campaña. Tienen fogatas y estatuas de su diosa. Esta zona tiene un color amarillento claro con un contraste brillante por las pinturas que usan para decorar. Cuentan con un gran altar al lado donde realizan ceremonias y sacrificios. Este tiene una gran subida de muchas escaleras hasta un templo lleno de decoraciones brillantes.',
          image: '@/assets/zone-desertica.jpg'
        },
        {
          title: 'Culto Satánico',
          description: 'Junto a la zona nevada y tras una caminata, llegas a otro desierto que destaca por los contrastes negros y rojos de la sangre y las banderas propagandísticas del culto. Una arena rojiza con plantas muertas y cuerpos humanos en putrefacción por todos lados. Una zona llena de gente muy radical con túnicas negras con manchas.',
          image: '@/assets/zone-culto.jpg'
        },
        {
          title: 'Bosque Quemado',
          description: 'Bajando de la montaña encuentras una zona boscosa donde no hay plantas vivas. Es todo gris, con toques negros por las plantas carbonizadas que decoran. Lleno de partículas de humo flotando. Una zona muy silenciosa, salvo por los monstruos que habitan las ruinas que sobreviven a las toxinas. La gente se pierde aquí y rara vez regresa.',
          image: '@/assets/zone-bosque.jpg'
        },
        {
          title: 'Pradera de la Ciudad',
          description: 'Al subir la montaña llegas a una vista elevada y sigues por un camino marrón junto al río que has observado mientras subías. Esta zona tiene colores claros y está llena de flores, árboles y plantas. Puedes cruzar el río por dos puentes; uno te lleva al final de la pradera donde empieza el bosque calcinado, con un cambio brusco de verdes claros a gris oscuro. Por el otro lado, verás caminos de piedra, murallas y vendedores que te dan la bienvenida a la capital.',
          image: '@/assets/zone-pradera.jpg'
        },
        {
          title: 'Capital',
          description: 'La gran ciudad, capital del reino, donde reside la mayoría de los habitantes, todo controlado por la iglesia. Son colores grises sin variedad, casas pequeñas con tonos marrones sin brillo, una fuente con toques azules que contrasta con el terreno, y un mercado con mucha actividad y productos importados de otras zonas. En contraste, la zona rica tiene colores brillantes, casas enormes con muchos pisos. La iglesia, en el centro sobre la fuente, tiene colores dorados con rojos en las banderas, una gran portada tras una escalinata y personajes con túnicas blancas entrando y saliendo.',
          image: '@/assets/zone-capital.jpg'
        },
        {
          title: 'Templos Antiguos',
          description: 'Bajo el pueblo inicial hay un puente que cruza el río; al otro lado, un bosc que parece no acabar nunca, todo de un verde oscuro con troncos grises. Caminos con baldosas de piedra cubiertas de musgo y ruinas azuladas escritas por todos lados. Lleno de pilares y decoraciones que se investigan hoy en día. Al fondo del bosque se encuentran antiguos templos y casas de piedra, derruidas y cubiertas de musgo. Muchas estatuas de antiguos dioses y altares. Pasan ríos de un color azul oscuro por el centro con puentes de piedra para cruzarlos.',
          image: '@/assets/zone-templos.jpg'
        }
      ],
      currentZoneIndex: 0
    };
  },
  computed: {
    totalSlides() {
      return Math.ceil(this.zones.length / 3);
    }
  },
  methods: {
    openModal(index) {
      this.selectedPhase = index;
    },
    closeModal() {
      this.selectedPhase = null;
    },
    closeModalOnOverlayClick(event) {
      if (event.target.classList.contains('modal-overlay')) {
        this.closeModal();
      }
    },
    nextZone() {
      this.currentZoneIndex = (this.currentZoneIndex + 1) % this.totalSlides;
    },
    prevZone() {
      this.currentZoneIndex = (this.currentZoneIndex - 1 + this.totalSlides) % this.totalSlides;
    },
    handleKeydown(event) {
      if (event.key === 'ArrowRight') {
        this.nextZone();
      } else if (event.key === 'ArrowLeft') {
        this.prevZone();
      }
    }
  },
  mounted() {
    window.addEventListener('keydown', this.handleKeydown);
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.handleKeydown);
  }
};
</script>

<style src="@/assets/styles/HomeView.css" scoped></style>

