<template>
  <nav class="nav-container">
    <div class="nav-header">
      <div class="nav-logo">Valtheris</div>
      <button class="menu-toggle" @click="toggleMenu" aria-label="Toggle Menu">
        ☰
      </button>
    </div>
    <ul :class="{ 'nav-links': true, 'nav-links-open': isMenuOpen }">
      <li v-for="item in items" :key="item.label">
        <router-link :to="item.route" @click="closeMenu">{{ item.label }}</router-link>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  name: 'NavBar',
  props: {
    items: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      isMenuOpen: false
    }
  },
  methods: {
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen;
    },
    closeMenu() {
      this.isMenuOpen = false;
    }
  }
}
</script>

<style src="@/assets/styles/NavBar.css" scoped></style>