<template>
  <div v-if="item" class="modal-overlay" @click.self="close">
    <div class="modal-content">
      <button class="close-btn" @click="close">✖</button>
      <div v-if="type === 'screenshot'" class="modal-image">
        <img :src="item.image_url" alt="Captura del juego en tamaño completo" />
      </div>
      <div v-else-if="type === 'video'" class="modal-video">
        <video controls :src="item.video_url" class="video-player" @error="$emit('video-error')">
          Tu navegador no soporta el elemento de video.
        </video>
      </div>
      <div class="modal-details">
        <div class="modal-meta">
          <span>{{ item.title || 'Sin título' }}</span>
          <div class="modal-actions" v-if="type === 'screenshot'">
            <button class="action-btn" :class="{ 'liked': item.isLikedByUser }" @click="toggleLike">
              <i class="icon">👍</i> Me gusta ({{ item.likesCount || 0 }})
            </button>
            <button class="action-btn dislike-btn" :class="{ 'disliked': item.isDislikedByUser }" @click="toggleDislike">
              <span class="icon">👎</span> No me gusta ({{ item.dislikesCount || 0 }})
            </button>
          </div>
        </div>
        <div class="comments-section">
          <h3>Comentarios</h3>
          <div v-if="comments.length === 0" class="no-comments">
            <p>No hay comentarios todavía. ¡Sé el primero en comentar!</p>
          </div>
          <div v-else class="comments-list">
            <div v-for="comment in comments" :key="comment.id" class="comment">
              <span class="comment-user">{{ comment.user?.name || 'Anónimo' }}:</span>
              <p class="comment-text">{{ comment.text }}</p>
            </div>
          </div>
          <div v-if="isAuthenticated" class="comment-form">
            <textarea v-model="commentText" placeholder="Añade un comentario..." rows="2"></textarea>
            <button @click="submitComment" class="submit-comment-btn">Comentar</button>
          </div>
          <div class="comment-actions">
            <router-link :to="`/${type === 'screenshot' ? 'capturas' : 'videos'}/${item.id}`" class="view-details-btn">
              Ver más detalles
            </router-link>
            <button v-if="type === 'screenshot' && isAuthenticated" @click="deleteItem" class="delete-btn">
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ContentModal',
  props: {
    type: {
      type: String,
      required: true,
    },
    item: Object,
    comments: Array,
    modelValue: {
      type: String,
      default: '',
    },
    isAuthenticated: Boolean,
  },
  emits: ['update:modelValue', 'close', 'submit-comment', 'delete', 'video-error', 'toggle-like', 'toggle-dislike'],
  data() {
    return {
      commentText: this.modelValue,
    };
  },
  watch: {
    modelValue(newValue) {
      this.commentText = newValue;
    },
    commentText(newValue) {
      this.$emit('update:modelValue', newValue);
    },
  },
  methods: {
    close() {
      this.$emit('close');
    },
    submitComment() {
      this.$emit('submit-comment');
    },
    deleteItem() {
      this.$emit('delete', this.item.id);
    },
    toggleLike() {
      this.$emit('toggle-like', this.item);
    },
    toggleDislike() {
      this.$emit('toggle-dislike', this.item);
    },
  },
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: #1a1a1a;
  padding: 20px;
  border-radius: 8px;
  border: 2px solid #ffcc00;
  width: 90%;
  max-width: 600px;
  position: relative;
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: #ff6f61;
  border: none;
  color: #ffffff;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
}

.modal-image img,
.modal-video .video-player {
  width: 100%;
  max-height: 400px;
  object-fit: contain;
  border: 2px solid #00ffcc;
  border-radius: 4px;
}

.modal-details {
  margin-top: 15px;
}

.modal-meta span {
  color: #ffffff;
  font-size: 1.2em;
  margin-bottom: 10px;
  display: block;
}

.modal-actions {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.action-btn {
  background: #1a1a1a;
  color: #00ffcc;
  border: 2px solid #ffcc00;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
}

.liked,
.disliked {
  background: #ffcc00;
  color: #1a1a1a;
}

.comments-section h3 {
  color: #ff0066;
  margin-bottom: 10px;
}

.comment-form textarea {
  width: 100%;
  padding: 8px;
  background: #2a2a2a;
  border: 1px solid #00ffcc;
  color: #ffffff;
  border-radius: 4px;
  margin-bottom: 10px;
}

.submit-comment-btn {
  background: #00ffcc;
  color: #1a1a1a;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}

.comment-actions {
  margin-top: 10px;
  display: flex;
  gap: 10px;
}

.view-details-btn,
.delete-btn {
  padding: 8px 16px;
  border-radius: 4px;
  text-decoration: none;
  color: #ffffff;
}

.view-details-btn {
  background: #00ffcc;
}

.delete-btn {
  background: #ff6f61;
}
</style>