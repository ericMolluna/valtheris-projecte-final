<!-- src/views/GuideView.vue -->
<template>
  <div class="guide-detail-container">
    <!-- Import Navbar -->
    <Navbar />

    <!-- Main Content -->
    <div class="guide-content-wrapper">
      <div v-if="loading" class="loading-message">Cargando guía...</div>
      <div v-else-if="errorMessage" class="error-message">{{ errorMessage }}</div>
      <div v-else-if="guide" class="guide-content">
        <div class="guide-header">
          <div class="guide-image-container">
    <div v-if="guide.image">Debug: {{ guide.image }}</div>
    <img
        v-if="guide.image"
        :src="guide.image"
        alt="Portada de la guía"
        @error="handleImageError"
    />
    <div v-else class="no-image-placeholder">Sin imagen</div>
</div>
          <div class="guide-title-container">
            <div class="title-and-actions">
              <h1 class="guide-title">{{ guide.title }}</h1>
              <!-- Botones de Editar y Eliminar -->
              <div class="header-actions" v-if="isAuthenticated && guide.createdBy === user?.name">
                <button class="action-btn edit-btn" @click="editGuide">
                  <span class="icon">✏️</span> Editar
                </button>
                <button class="action-btn delete-btn" @click="deleteGuide">
                  <span class="icon">🗑️</span> Eliminar
                </button>
              </div>
            </div>
            <div class="guide-rating">
              <span class="stars">{{ displayStars(averageRating) }}</span>
              <span class="rating-text">
                {{
                  averageRating > 0
                    ? `${averageRating.toFixed(1)}/5 (${ratings.length
                    } valoraciones)`
                    : "No hay suficientes valoraciones"
                }}
              </span>
            </div>
            <p class="guide-author">Por {{ guide.createdBy || "Anónimo" }}</p>
          </div>
        </div>

        <div class="guide-actions">
          <button class="action-btn like-btn" :disabled="!isAuthenticated || guide.user_liked" @click="likeGuide">
            <span class="icon">👍</span> Me gusta ({{ guide.likes || 0 }})
          </button>
          <button class="action-btn dislike-btn" :disabled="!isAuthenticated || guide.user_disliked"
            @click="dislikeGuide">
            <span class="icon">👎</span> No me gusta ({{ guide.dislikes || 0 }})
          </button>
        </div>

        <!-- Contenido principal y metadatos -->
        <div class="guide-main-content">
          <div class="guide-body">
            <p class="guide-content-text" v-html="guide.content"></p>
          </div>
          <div class="guide-meta">
            <h3>Detalles</h3>
            <div class="meta-item">
              <span class="meta-label">Creado por:</span>
              <span class="meta-value">{{
                guide.createdBy || "Anónimo"
              }}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">Categoría:</span>
              <span class="meta-value">{{
                guide.category || "Sin categoría"
              }}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">Idioma:</span>
              <span class="meta-value">Español</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">Publicado:</span>
              <span class="meta-value">{{
                formatDate(guide.created_at)
              }}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">Actualizado:</span>
              <span class="meta-value">{{
                formatDate(guide.updated_at || guide.created_at)
              }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { isAuthenticated } from "@/api/auth";
import Navbar from '@/components/NavBar.vue';

export default {
  name: "GuideView",
  components: { Navbar },
  data() {
    return {
      guide: null,
      loading: false,
      errorMessage: "",
      isAuthenticated: localStorage.getItem("auth_token") !== null,
      isFavorited: false,
      comments: [],
      newComment: "",
      ratings: [],
      userRating: 0,
      userReview: "",
      averageRating: 0,
      user: null,
    };
  },
  created() {
    window.addEventListener("storage", this.updateAuthStatus);
    this.checkAuthStatus();
    this.fetchGuide();
    this.loadComments();
    this.loadRatings();
    this.fetchUserData();
  },
  methods: {
    async checkAuthStatus() {
      this.isAuthenticated = await isAuthenticated();
      console.log("Estado de autenticación:", this.isAuthenticated);
    },
    updateAuthStatus() {
      this.isAuthenticated = localStorage.getItem("auth_token") !== null;
      console.log(
        "Estado de autenticación actualizado:",
        this.isAuthenticated
      );
    },
    async fetchUserData() {
      try {
        const token = localStorage.getItem("auth_token");
        if (!token) {
          console.log("No hay token de autenticación, usuario no autenticado");
          return;
        }
        axios.defaults.baseURL = "http://localhost:8000";
        axios.defaults.withCredentials = true;
        axios.defaults.headers.common[
          "Authorization"
        ] = `Bearer ${token}`;
        const response = await axios.get("/api/user");
        this.user = {
          name: response.data.name,
          email: response.data.email,
        };
        console.log("Datos del usuario cargados:", this.user);
      } catch (error) {
        this.errorMessage = error.response
          ? error.response.data.message
          : error.message;
        console.error("Error al cargar datos del usuario:", error);
      }
    },
    async fetchGuide() {
    this.loading = true;
    this.errorMessage = "";
    try {
        const guideId = this.$route.params.id;
        console.log('Guide ID:', guideId);
        if (!guideId || typeof guideId !== 'string' || guideId === '[object Object]') {
            throw new Error('Invalid guide ID');
        }
        const apiBaseUrl = process.env.VUE_APP_API_BASE_URL || "http://localhost:8000";
        console.log("API Base URL used:", apiBaseUrl);
        axios.defaults.baseURL = apiBaseUrl;
        axios.defaults.withCredentials = true;
        await axios.get("sanctum/csrf-cookie");
        const response = await axios.get(`/api/guides/${guideId}`);
        console.log("API Response:", response.data);
        console.log("Image URL from API:", response.data.image_url);
        const fullImageUrl = response.data.image_url
            ? `${apiBaseUrl}${response.data.image_url}`
            : null;
        console.log("Full Image URL:", fullImageUrl);
        this.guide = {
            ...response.data,
            likes: response.data.likes || 0,
            dislikes: response.data.dislikes || 0,
            user_liked: response.data.user_liked || false,
            user_disliked: response.data.user_disliked || false,
            image: fullImageUrl,
            createdBy: response.data.createdBy || 'Anónimo',
        };
        if (!response.data.image_url) {
            console.warn("No image URL provided by the API for guide ID:", guideId);
        }
    } catch (error) {
        console.error("Fetch guide error:", {
            status: error.response?.status,
            message: error.response?.data?.message || error.message,
            error,
        });
        if (error.response?.status === 404 || error.message === 'Invalid guide ID') {
            this.errorMessage = "Guía no encontrada";
            this.$router.push('/comunidad');
        } else if (error.response?.status === 401 || error.response?.status === 403) {
            this.errorMessage = "Sesión expirada o no autorizado";
            this.$router.push('/login');
        } else {
            this.errorMessage = error.response?.data?.message || "Error al cargar la guía";
        }
    } finally {
        this.loading = false;
    }
},
    async likeGuide() {
      if (!this.isAuthenticated) {
        alert('Debes iniciar sesión para dar "Me gusta".');
        return;
      }
      try {
        const guideId = this.$route.params.id;
        axios.defaults.baseURL = "http://localhost:8000";
        axios.defaults.withCredentials = true;
        await axios.get("sanctum/csrf-cookie");
        const token = localStorage.getItem("auth_token");
        if (!token) {
          throw new Error("No se encontró el token de autenticación.");
        }
        const response = await axios.post(
          `/api/guides/${guideId}/like`,
          {},
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        this.guide.likes = response.data.likes;
        this.guide.dislikes = response.data.dislikes;
        this.guide.user_liked = response.data.user_liked;
        this.guide.user_disliked = response.data.user_disliked;
      } catch (error) {
        console.error('Error al dar "Me gusta":', error);
        const errorMsg = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText
          }`
          : error.message;
        alert(`No se pudo dar "Me gusta": ${errorMsg}`);
      }
    },
    async dislikeGuide() {
      if (!this.isAuthenticated) {
        alert('Debes iniciar sesión para dar "No me gusta".');
        return;
      }
      try {
        const guideId = this.$route.params.id;
        axios.defaults.baseURL = "http://localhost:8000";
        axios.defaults.withCredentials = true;
        await axios.get("sanctum/csrf-cookie");
        const token = localStorage.getItem("auth_token");
        if (!token) {
          throw new Error("No se encontró el token de autenticación.");
        }
        const response = await axios.post(
          `/api/guides/${guideId}/dislike`,
          {},
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        this.guide.likes = response.data.likes;
        this.guide.dislikes = response.data.dislikes;
        this.guide.user_liked = response.data.user_liked;
        this.guide.user_disliked = response.data.user_disliked;
      } catch (error) {
        console.error('Error al dar "No me gusta":', error);
        const errorMsg = error.response
          ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText
          }`
          : error.message;
        alert(`No se pudo dar "No me gusta": ${errorMsg}`);
      }
    },
    handleImageError(event) {
      console.error("Image failed to load:", event.target.src);
      event.target.style.display = "none";
      if (!event.target.parentNode.querySelector(".no-image-placeholder")) {
        const placeholder = document.createElement("div");
        placeholder.className = "no-image-placeholder";
        placeholder.textContent = "Sin imagen disponible";
        event.target.parentNode.appendChild(placeholder);
      }
    },
    formatDate(dateString) {
      if (!dateString) return "Fecha desconocida";
      const date = new Date(dateString);
      return date.toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
      });
    },
    displayStars(rating) {
      const fullStars = Math.floor(rating);
      const emptyStars = 5 - fullStars;
      return "★".repeat(fullStars) + "☆".repeat(emptyStars);
    },
    async loadRatings() {
      try {
        const guideId = this.$route.params.id;
        axios.defaults.baseURL = "http://localhost:8000";
        axios.defaults.withCredentials = true;
        await axios.get("sanctum/csrf-cookie");
        const response = await axios.get(`/api/guides/${guideId}/ratings`);
        this.ratings = response.data;
        this.calculateAverageRating();
      } catch (error) {
        console.error("Error al cargar las valoraciones:", error);
      }
    },
    calculateAverageRating() {
      if (this.ratings.length === 0) {
        this.averageRating = 0;
        return;
      }
      const total = this.ratings.reduce((sum, rating) => sum + rating.rating, 0);
      this.averageRating = total / this.ratings.length;
    },
    setUserRating(star) {
      this.userRating = star;
    },
    async submitRating() {
      if (this.userRating === 0 || !this.userReview.trim()) return;
      try {
        const guideId = this.$route.params.id;
        axios.defaults.baseURL = "http://localhost:8000";
        axios.defaults.withCredentials = true;
        await axios.get("sanctum/csrf-cookie");
        const response = await axios.post(
          `/api/guides/${guideId}/ratings`,
          {
            rating: this.userRating,
            text: this.userReview,
          },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          }
        );
        this.ratings.push({
          id: response.data.id,
          author: this.guide.createdBy || "Usuario",
          rating: this.userRating,
          text: this.userReview,
          created_at: new Date().toISOString(),
        });
        this.userRating = 0;
        this.userReview = "";
        this.calculateAverageRating();
        console.log("Valoración enviada:", response.data);
      } catch (error) {
        console.error("Error al enviar la valoración:", error);
        alert("Error al enviar la valoración. Por favor, intenta de nuevo.");
      }
    },
    toggleFavorite() {
      this.isFavorited = !this.isFavorited;
      console.log(
        `Guía ${this.isFavorited ? "agregada a" : "eliminada de"} favoritos`
      );
    },
    shareGuide() {
      const url = window.location.href;
      navigator.clipboard.writeText(url).then(() => {
        alert("Enlace copiado al portapapeles: " + url);
      }).catch((err) => {
        console.error("Error al copiar el enlace:", err);
        alert("No se pudo copiar el enlace. Por favor, copia manualmente: " + url);
      });
    },
    loadComments() {
      // Placeholder for loading comments
    },
    submitComment() {
      if (!this.newComment.trim()) return;
      const newComment = {
        id: this.comments.length + 1,
        author: this.isAuthenticated
          ? this.guide?.createdBy || "Usuario"
          : "Anónimo",
        text: this.newComment,
        created_at: new Date().toISOString(),
      };
      this.comments.push(newComment);
      this.newComment = "";
      console.log("Comentario enviado:", newComment);
    },
    editGuide() {
      this.$router.push(`/comunidad/crear-guia?editGuide=${this.guide.id}`);
    },
    async deleteGuide() {
      if (confirm("¿Estás seguro de que quieres eliminar esta guía?")) {
        try {
          const guideId = this.$route.params.id;
          axios.defaults.baseURL = "http://localhost:8000";
          axios.defaults.withCredentials = true;
          await axios.get("sanctum/csrf-cookie");
          const response = await axios.delete(`/api/guides/${guideId}`, {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
            },
          });
          console.log("Guía eliminada:", response.data);
          this.$router.push("/comunidad/guias");
        } catch (error) {
          this.errorMessage = error.response
            ? error.response.data.message
            : "Error al eliminar la guía";
          console.error("Error al eliminar la guía:", error);
        }
      }
    },
  },
};
</script>

<style src="@/assets/styles/Guides/GuideView.css" scoped></style>
