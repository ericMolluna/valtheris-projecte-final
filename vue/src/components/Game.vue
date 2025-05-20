<template>
  <div class="game-container">
    <!-- Barra de navegación superior -->
    <nav class="nav-container">
      <div class="logo">
        <router-link to="/" class="logo-link">🎮 GameHub</router-link>
      </div>
      <ul>
        <li>
          <router-link to="/juego"><i class="icon">🎮</i> Jugar</router-link>
        </li>
        <li>
          <router-link to="/comunidad"><i class="icon">👥</i> Comunidad</router-link>
        </li>
        <li>
          <router-link :to="isAuthenticated ? '/perfil' : '/login'">
            <i class="icon">{{ isAuthenticated ? '👤' : '🔑' }}</i>
            {{ isAuthenticated ? 'Perfil' : 'Login' }}
          </router-link>
        </li>
      </ul>
    </nav>

    <!-- Contenedor del juego -->
    <div id="game-content">
      <iframe src="http://localhost:3000" frameborder="0"></iframe>
    </div>
  </div>
</template>

<script>
import { isAuthenticated } from '@/api/auth';

export default {
  name: 'RpgGame',
  data() {
    return {
      isAuthenticated: localStorage.getItem('auth_token') !== null,
    };
  },
  created() {
    window.addEventListener('storage', this.updateAuthStatus);
    this.checkAuthStatus();
  },
  mounted() {
    // Prevenir desplazamiento en la página padre
    document.body.style.overflow = 'hidden';
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.updateAuthStatus);
    // Restaurar el desplazamiento al salir del componente
    document.body.style.overflow = 'auto';
  },
  methods: {
    async checkAuthStatus() {
      this.isAuthenticated = await isAuthenticated();
    },
    updateAuthStatus() {
      this.isAuthenticated = localStorage.getItem('auth_token') !== null;
    },
  },
};
</script>

<style scoped>
.game-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #1e1e2f 0%, #2a2a3f 100%);
  color: #fff;
  font-family: 'Arial', sans-serif;
  display: flex;
  flex-direction: column;
  align-items: center;
  overflow: hidden; /* Evita desplazamiento del contenedor */
}

/* Barra de navegación (copiada del estilo de comunidad) */
.nav-container {
  width: 100%;
  padding: 15px 20px; /* Ajustado desde el estilo de comunidad */
  background-color: rgba(255, 255, 255, 0.95);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
  position: sticky;
  top: 0;
  z-index: 10;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo-link {
  text-decoration: none;
  font-size: 1.5em; /* Ajustado desde el estilo de comunidad */
  font-weight: bold;
  color: #ff6f61;
  transition: color 0.3s;
}

.logo-link:hover {
  color: #e63946;
}

.nav-container ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  align-items: center;
}

.nav-container ul li {
  margin: 0 20px; /* Ajustado desde el estilo de comunidad */
}

.nav-container ul li a {
  text-decoration: none;
  color: #ff6f61;
  font-size: 1.2em; /* Ajustado desde el estilo de comunidad */
  display: flex;
  align-items: center;
  transition: color 0.3s;
}

.nav-container ul li a .icon {
  margin-right: 8px; /* Ajustado desde el estilo de comunidad */
  font-size: 1.2em; /* Ajustado desde el estilo de comunidad */
  vertical-align: middle;
}

.nav-container ul li a:hover {
  color: #e63946;
}

/* Contenedor del juego (más grande en PC) */
#game-content {
  width: 90%;
  max-width: 1200px; /* Aumentado de 1000px a 1200px para PC */
  height: calc(100vh - 60px); /* Ajustado para la altura de la barra */
  display: flex;
  justify-content: center;
  align-items: center;
  flex-grow: 1;
  overflow: hidden; /* Evita que el contenido desborde */
}

iframe {
  width: 100%;
  height: 100%; /* Ajusta al 100% del contenedor */
  max-height: 100%; /* Limita al contenedor */
  border: none;
  display: block;
  overflow: hidden; /* Evita barras de desplazamiento internas */
}

/* Responsive Design */
@media (max-width: 768px) {
  #game-content {
    width: 95%;
    max-width: 750px; /* Mantenido en 750px para móviles */
    height: calc(100vh - 50px); /* Ajustado para barra más pequeña */
  }

  .nav-container {
    padding: 10px 15px; 
  }

  .nav-container ul li {
    margin: 0 10px; 
  }

  .nav-container ul li a {
    font-size: 1em; 
  }

  .nav-container ul li a .icon {
    font-size: 1em; 
  }

  .logo-link {
    font-size: 1.2em; 
  }
}
</style>