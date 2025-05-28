<template>
  <div class="main-container">
    <!-- Navbar -->
    <NavBar />

    <!-- About Content -->
    <section class="about-section">
      <h1 class="section-title">Acerca de Valtheris</h1>
      <p class="section-description">
        Valtheris es un RPG de pixel art al estilo de la antigua escuela, inspirado en los clásicos Final Fantasy originales. 
        Con un diseño retro que rinde homenaje a los juegos de los 80 y 90, ofrecemos una aventura épica llena de combates estratégicos, 
        historias inmersivas y un mundo lleno de nostalgia para los amantes del género.
      </p>

      <!-- Team Section -->
      <div class="team-info">
        <h2 class="section-subtitle">Nuestro Equipo</h2>
        <p class="section-text">
          Somos un equipo de dos desarrolladores web apasionados por los videojuegos retro, trabajando juntos para dar vida a Valtheris. 
          Nuestro objetivo es recrear la magia de los RPG clásicos con un toque moderno.
        </p>

        <!-- Creators Profiles -->
        <div class="creators-grid">
          <div class="creator-card">
            <h3 class="creator-name">Josep Maria Guasch</h3>
            <p class="creator-role">Desarrollador Principal del Juego</p>
            <p class="creator-bio">
              Josep Maria es el genio detrás del desarrollo principal de Valtheris, diseñando las mecánicas de combate y el mundo del juego. 
              Con una pasión por los RPG clásicos, lidera el proyecto con una visión detallada y estratégica.
            </p>
            <a href="https://github.com/JoMaGuPa" target="_blank" class="cta-link">GitHub: JoMaGuPa</a>
            <div class="creator-socials">
              <a href="https://linkedin.com/in/josep-maria-guasch" target="_blank" class="social-link">LinkedIn</a>
            </div>
          </div>

          <div class="creator-card">
            <h3 class="creator-name">Eric Molluna</h3>
            <p class="creator-role">Creador de la Página Web</p>
            <p class="creator-bio">
              Eric es el responsable de dar vida a la página web de Valtheris, asegurándose de que los fans tengan un portal atractivo para explorar el juego. 
              Su experiencia en desarrollo web complementa el equipo con un enfoque en la experiencia del usuario.
            </p>
            <a href="https://github.com/EricMolluna" target="_blank" class="cta-link">GitHub: EricMolluna</a>
            <div class="creator-socials">
              <a href="https://linkedin.com/in/eric-molluna" target="_blank" class="social-link">LinkedIn</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Our Work Section -->
      <div class="work-info">
        <h2 class="section-subtitle">Nuestro Trabajo</h2>
        <p class="section-text">
          En Valtheris, Josep Maria se encarga del desarrollo principal del juego, creando las mecánicas de combate y el mundo pixelado que define su esencia. 
          Eric diseña y mantiene la página web, asegurando que los jugadores puedan acceder a información y contenido de manera fluida. 
          Juntos, hemos trabajado durante más de dos años para construir un RPG que capture la nostalgia de los Final Fantasy originales.
        </p>
      </div>

      <!-- Fun Facts Section -->
      <div class="fun-facts">
        <h2 class="section-subtitle">Datos Curiosos</h2>
        <ul class="facts-list">
          <li>El nombre "Valtheris" se inspira en la región de Valisthea de Final Fantasy XVI.</li>
          <li>Este proyecto nació gracias a la framework RPGJS, que nos permitió empezar rápidamente.</li>
          <li>El dachshund de Eric, Thor, ha sido una fuente de inspiración y compañía durante el desarrollo.</li>
        </ul>
      </div>

      <!-- Contact Section -->
      <div class="contact-info">
        <h2 class="section-subtitle">Contacto</h2>
        <p class="section-text">
          ¿Tienes preguntas o quieres colaborar con nosotros? Escríbenos a 
          <a href="mailto:contacto@valtheris.com" class="cta-link">contacto@valtheris.com</a> 
          o visita nuestro repositorio en 
          <a href="https://github.com/valtheris" class="cta-link">GitHub</a>.
        </p>
      </div>
    </section>

    <!-- Footer -->
    <FooterSection />
  </div>
</template>

<script>
import NavBar from '@/components/NavBar.vue';
import FooterSection from '@/components/FooterSection.vue';

export default {
  name: 'AboutView',
  components: {
    NavBar,
    FooterSection
  }
};
</script>

<style src="@/assets/styles/AboutView.css" scoped></style>