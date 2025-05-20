<template>
  <div class="navbar-container">
    <nav>
      <ul>
        <li v-for="(item, index) in items" :key="index">
          <router-link :to="item.route">{{ item.label }}</router-link>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
export default {
  name: 'NavBar',
  props: {
    items: {
      type: Array,
      required: true,
    },
  },
};
</script>

<style scoped>
.navbar-container {
  text-align: center;
  padding: 20px;
}

nav ul {
  list-style: none;
  padding: 0;
}

nav ul li {
  display: inline;
  margin: 0 15px;
}

nav ul li a {
  text-decoration: none;
  color: #42b983;
  font-size: 1.2em;
}

nav ul li a:hover {
  color: #35495e;
}
</style>
