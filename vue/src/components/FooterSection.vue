<template>
  <footer class="footer-section">
    <div class="footer-content">
      <div class="footer-logo">Valtheris Devs</div>
      <div class="footer-links">
        <p>Contacto: contacto@valtheris.com</p>
        <p>Dirección: Calle Ficticia 123, Fantasía</p>
        <p>
          <router-link to="/terminos">Términos</router-link> |
          <router-link to="/privacidad">Privacidad</router-link>
        </p>
      </div>
      <div class="social-links">
        <a href="https://github.com/JoMaGuPa" class="social-icon" aria-label="GitHub Repo 1">
          🐙
        </a>
        <a href="https://github.com/ericMolluna" class="social-icon" aria-label="GitHub Repo 2">
          🐙
        </a>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'FooterSection'
}
</script>

<style src="@/assets/styles/FooterSection.css" scoped></style>