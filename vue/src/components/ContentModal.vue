<template>
  <div class="modal-overlay" v-if="show">
    <div class="modal-content">
      <h3>{{ type === 'screenshot' ? 'Detalles de Captura' : 'Detalles de Video' }}</h3>
      <p>{{ item.title || item.name }}</p>
      <div v-if="type === 'screenshot' && isAuthenticated" class="actions">
        <button @click="$emit('toggle-like')">Like</button>
        <button @click="$emit('toggle-dislike')">Dislike</button>
        <button @click="$emit('delete')">Eliminar</button>
      </div>
      <div class="comments">
        <input v-model="comment" placeholder="Añadir comentario" @keyup.enter="submitComment" />
        <ul>
          <li v-for="c in comments" :key="c.id">{{ c.text }}</li>
        </ul>
      </div>
      <button @click="$emit('close')" class="close-btn">Cerrar</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ContentModal',
  props: {
    show: Boolean,
    type: String,
    item: Object,
    comments: Array,
    isAuthenticated: Boolean,
    modelValue: String,
  },
  computed: {
    comment: {
      get() { return this.modelValue; },
      set(value) { this.$emit('update:modelValue', value); },
    },
  },
  methods: {
    submitComment() {
      if (this.comment) {
        this.$emit('submit-comment', this.comment);
        this.$emit('update:modelValue', '');
      }
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
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.modal-content {
  background: rgba(20, 20, 20, 0.9);
  padding: 20px;
  border: 4px solid #00ffcc;
  border-radius: 8px;
  max-width: 500px;
  width: 90%;
  text-align: center;
}

.actions button {
  margin: 5px;
  padding: 5px 10px;
  background-color: #ffcc00;
  border: 2px solid #000;
}

.comments input {
  width: 80%;
  padding: 5px;
  margin: 10px 0;
  background-color: #1a1a1a;
  border: 2px solid #00ffcc;
}

.comments ul {
  list-style: none;
  text-align: left;
  padding: 0;
}

.close-btn {
  margin-top: 10px;
  padding: 10px 20px;
  background-color: #ff0066;
  border: 2px solid #000;
  color: #fff;
  font-family: 'Press Start 2P', monospace;
}
</style>