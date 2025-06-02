<template>
  <div class="upload-form-modal">
    <div class="upload-form">
      <h3>{{ type === 'screenshot' ? 'Subir Nueva Captura' : 'Subir Nuevo Video' }}</h3>
      <form @submit.prevent="handleSubmit">
        <div class="form-group">
          <label :for="`${type}-title`">Título</label>
          <input type="text" :id="`${type}-title`" v-model="item.title" :placeholder="`Título de tu ${type}...`" required />
        </div>
        <div class="form-group">
          <label :for="`${type}-file`">Seleccionar {{ type === 'screenshot' ? 'Imagen' : 'Video' }}</label>
          <input type="file" :id="`${type}-file`" :accept="type === 'screenshot' ? 'image/*' : 'video/*'" @change="handleFileChange" required />
        </div>
        <div v-if="type === 'video' || type === 'screenshot'" class="form-group">
          <label :for="`${type}-description`">Descripción (Opcional)</label>
          <textarea :id="`${type}-description`" v-model="item.description" :placeholder="`Describe tu ${type}...`"></textarea>
        </div>
        <div class="form-actions">
          <button type="submit" class="submit-btn">Subir</button>
          <button type="button" class="cancel-btn" @click="$emit('close')">Cancelar</button>
        </div>
        <p v-if="error" class="error-message">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UploadForm',
  props: {
    type: {
      type: String,
      required: true,
    },
    modelValue: {
      type: Object,
      required: true,
    },
    error: {
      type: String,
      default: '',
    },
  },
  emits: ['update:modelValue', 'close', 'upload'],
  computed: {
    item: {
      get() { return this.modelValue; },
      set(value) { this.$emit('update:modelValue', value); },
    },
  },
  methods: {
    handleFileChange(event) {
      const file = event.target.files[0];
      if (this.type === 'screenshot') this.item.image = file;
      else this.item.video = file;
    },
    handleSubmit() {
      this.$emit('upload');
    },
  },
};
</script>

<style scoped>
.upload-form-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.upload-form {
  background: #1a1a1a;
  padding: 20px;
  border-radius: 8px;
  border: 2px solid #ffcc00;
  width: 90%;
  max-width: 400px;
}

.form-group {
  margin-bottom: 15px;
}

label {
  color: #ffffff;
  display: block;
  margin-bottom: 5px;
}

input, textarea {
  width: 100%;
  padding: 8px;
  background: #2a2a2a;
  border: 1px solid #00ffcc;
  color: #ffffff;
  border-radius: 4px;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.submit-btn, .cancel-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.submit-btn {
  background: #00ffcc;
  color: #1a1a1a;
}

.cancel-btn {
  background: #ff6f61;
  color: #ffffff;
}

.error-message {
  color: #ff6f61;
  text-align: center;
  margin-top: 10px;
}
</style>  