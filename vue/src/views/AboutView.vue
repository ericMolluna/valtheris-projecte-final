<template>
  <div class="main-container">
    <!-- Navbar -->
    <NavBar />

    <!-- About Content -->
    <section class="about-section">
      <h1 class="section-title">Acerca de Valtheris</h1>
      <p class="section-description">
        Valtheris: El Despertar del Código es un RPG de píxeles que combina programación y magia en un mundo lleno de misterios. 
        Creado por un equipo apasionado por los videojuegos retro y la educación en programación, nuestro objetivo es ofrecer una 
        experiencia única donde los jugadores puedan aprender mientras se divierten.
      </p>
      <div class="team-info">
        <h2 class="section-subtitle">Nuestro Equipo</h2>
        <p class="section-text">
          Somos un pequeño equipo de desarrolladores, artistas y educadores que trabajan juntos para dar vida a Valtheris. 
          Nos inspira la estética de los juegos retro de los 80 y 90, así como la idea de hacer que la programación sea accesible para todos.
        </p>
      </div>
      <div class="contact-info">
        <h2 class="section-subtitle">Contacto</h2>
        <p class="section-text">
          ¿Tienes preguntas o quieres colaborar con nosotros? Únete a nuestra comunidad en 
          <a href="https://discord.gg/valtheris" class="cta-link">Discord</a> o visita nuestro repositorio en 
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