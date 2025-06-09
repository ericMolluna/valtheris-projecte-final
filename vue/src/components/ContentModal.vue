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

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  overflow-y: auto;
}

/* Modal Content */
.modal-content {
  background: rgba(20, 20, 20, 0.85);
  padding: 20px;
  border: 2px solid #00ffcc;
  border-radius: 6px;
  width: 90%;
  max-width: 800px;
  position: relative;
  display: flex;
  flex-direction: row;
  box-shadow: 0 0 8px rgba(0, 255, 204, 0.3);
  font-family: 'VT323', monospace;
  color: #e0e0e0;
}

@media (max-width: 768px) {
  .modal-content {
    flex-direction: column;
    width: 95%;
  }
}

/* Close Button */
.close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: #00ffcc;
  border: 2px solid #000000;
  color: #1a1a1a;
  padding: 8px 15px;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'VT323', monospace;
  font-size: 1em;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.close-btn:hover {
  background: #ff0066;
  color: #ffffff;
  transform: translateY(-2px);
}

/* Modal Image and Video */
.modal-image,
.modal-video {
  flex: 1;
  padding: 10px;
}

.modal-image img,
.modal-video .video-player {
  width: 100%;
  max-height: 500px;
  object-fit: contain;
  border: 2px solid #00ffcc;
  border-radius: 6px;
  box-shadow: 0 0 8px rgba(0, 255, 204, 0.3);
  background: #1a1a1a;
}

@media (max-width: 768px) {
  .modal-image,
  .modal-video {
    padding: 5px;
  }

  .modal-image img,
  .modal-video .video-player {
    max-height: 300px;
  }
}

/* Modal Details */
.modal-details {
  flex: 0.5;
  padding: 15px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

@media (max-width: 768px) {
  .modal-details {
    padding: 10px;
    border-radius: 0 0 6px 6px;
  }
}

/* Modal Meta */
.modal-meta {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.modal-meta span {
  color: #ff0066;
  font-size: 1.3em;
  font-weight: 400;
}

/* Modal Actions */
.modal-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.action-btn {
  background: #00ffcc;
  color: #1a1a1a;
  border: 2px solid #000000;
  padding: 8px 15px;
  border-radius: 6px;
  cursor: pointer;
  font-family: 'VT323', monospace;
  font-size: 1em;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.action-btn:hover {
  background: #ff0066;
  color: #ffffff;
  transform: translateY(-2px);
}

.liked,
.disliked {
  background: #00ffcc;
}

.liked:hover,
.disliked:hover {
  background: #ff0066;
}

.dislike-btn {
  background: #00ffcc;
}

.dislike-btn:hover {
  background: #ff0066;
}

/* Comments Section */
.comments-section {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.comments-section h3 {
  color: #ff0066;
  font-size: 1.5em;
  font-weight: 400;
  margin: 0;
  border-bottom: 2px solid #00ffcc;
  padding-bottom: 10px;
}

.no-comments {
  text-align: center;
  color: #888888;
  padding: 15px;
  background: rgba(20, 20, 20, 0.9);
  border: 2px solid #00ffcc;
  border-radius: 6px;
}

.no-comments p {
  margin: 0;
  font-size: 1em;
}

.comments-list {
  max-height: 400px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.comments-list::-webkit-scrollbar {
  width: 8px;
}

.comments-list::-webkit-scrollbar-track {
  background: rgba(20, 20, 20, 0.9);
  border-radius: 4px;
}

.comments-list::-webkit-scrollbar-thumb {
  background: #00ffcc;
  border-radius: 4px;
}

.comments-list::-webkit-scrollbar-thumb:hover {
  background: #ff0066;
}

.comment {
  background: rgba(20, 20, 20, 0.9);
  padding: 15px;
  border: 2px solid #00ffcc;
  border-radius: 6px;
  box-shadow: 0 0 5px rgba(0, 255, 204, 0.3);
  transition: transform 0.2s ease;
}

.comment:hover {
  transform: translateY(-2px);
}

.comment-user {
  color: #00ffcc;
  font-size: 1em;
  font-weight: 400;
  margin-right: 5px;
}

.comment-text {
  margin: 5px 0 0;
  color: #e0e0e0;
  font-size: 1em;
  line-height: 1.6;
}

/* Comment Form */
.comment-form {
  margin-top: 20px;
  background: rgba(20, 20, 20, 0.9);
  padding: 15px;
  border: 2px solid #00ffcc;
  border-radius: 6px;
  box-shadow: 0 0 5px rgba(0, 255, 204, 0.3);
}

.comment-form textarea {
  width: 100%;
  padding: 10px;
  border: 2px solid #00ffcc;
  border-radius: 6px;
  background: rgba(20, 20, 20, 0.9);
  color: #e0e0e0;
  font-family: 'VT323', monospace;
  font-size: 1em;
  resize: vertical;
  transition: border-color 0.3s ease;
}

.comment-form textarea:focus {
  border-color: #ff0066;
  outline: none;
}

.submit-comment-btn {
  background: #00ffcc;
  color: #1a1a1a;
  border: 2px solid #000000;
  padding: 8px 20px;
  border-radius: 6px;
  font-family: 'VT323', monospace;
  font-size: 1em;
  cursor: pointer;
  margin-top: 10px;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.submit-comment-btn:hover {
  background: #ff0066;
  color: #ffffff;
  transform: scale(1.05);
}

/* Comment Actions */
.comment-actions {
  margin-top: 10px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.view-details-btn,
.delete-btn {
  background: #00ffcc;
  color: #1a1a1a;
  padding: 8px 15px;
  border: 2px solid #000000;
  border-radius: 6px;
  text-decoration: none;
  font-family: 'VT323', monospace;
  font-size: 1em;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.view-details-btn:hover,
.delete-btn:hover {
  background: #ff0066;
  color: #ffffff;
  transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
  .modal-content {
    width: 95%;
    margin: 20px auto;
  }

  .modal-image img,
  .modal-video .video-player {
    max-height: 300px;
  }

  .comments-list {
    max-height: 200px;
  }

  .comment {
    padding: 10px;
  }

  .comment-user {
    font-size: 0.9em;
  }

  .comment-text {
    font-size: 0.9em;
  }

  .action-btn,
  .view-details-btn,
  .delete-btn,
  .submit-comment-btn,
  .close-btn {
    padding: 7px 15px;
    font-size: 0.9em;
  }
}

@media (max-width: 480px) {
  .modal-content {
    width: 95%;
  }

  .modal-image img,
  .modal-video .video-player {
    max-height: 200px;
  }

  .comment-text {
    font-size: 0.85em;
  }

  .comments-section h3 {
    font-size: 1.2em;
  }
}
</style>