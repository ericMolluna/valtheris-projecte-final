<template>
  <footer class="footer-section">
    <div class="footer-content">
      <p>© 2025 Valtheris Devs</p>
      <div class="social-links">
        <a href="#" class="social-icon" aria-label="Twitter">🐦</a>
        <a href="#" class="social-icon" aria-label="Facebook">📘</a>
        <a href="#" class="social-icon" aria-label="Twitch">🎮</a>
      </div>
      <div class="footer-extra">
        <p>Contacto: contacto@valtheris.com</p>
        <p>Dirección: Calle Ficticia 123, Fantasía</p>
        <p><router-link to="/terminos">Términos y Condiciones</router-link> | <router-link to="/privacidad">Política de Privacidad</router-link></p>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'FooterSection'
}
</script>

<style scoped>
/* Estilos para el footer */
.footer-extra {
  margin-top: 1rem;
  font-size: 0.85rem;
  color: #ccc;
}
</style>
