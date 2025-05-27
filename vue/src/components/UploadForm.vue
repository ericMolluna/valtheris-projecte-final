<template>
  <div class="modal-overlay">
    <div class="modal-content">
      <h3>{{ type === 'screenshot' ? 'Subir Captura' : 'Subir Video' }}</h3>
      <input v-model="uploadData" type="text" placeholder="URL o descripción" class="upload-input" />
      <button @click="handleUpload" class="upload-btn">Subir</button>
      <button @click="$emit('close')" class="close-btn">Cancelar</button>
      <p v-if="error" class="error">{{ error }}</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UploadForm',
  props: {
    type: String,
    modelValue: String,
    error: String,
  },
  computed: {
    uploadData: {
      get() { return this.modelValue; },
      set(value) { this.$emit('update:modelValue', value); },
    },
  },
  methods: {
    handleUpload() {
      this.$emit('upload', this.uploadData);
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
  max-width: 400px;
  width: 90%;
  text-align: center;
}

.upload-input {
  width: 80%;
  padding: 10px;
  margin: 10px 0;
  background-color: #1a1a1a;
  border: 2px solid #00ffcc;
  color: #ffffff;
}

.upload-btn, .close-btn {
  padding: 10px 20px;
  margin: 5px;
  border: 2px solid #000;
  border-radius: 4px;
  font-family: 'Press Start 2P', monospace;
  cursor: pointer;
}

.upload-btn {
  background-color: #ffcc00;
  color: #000;
}

.close-btn {
  background-color: #ff0066;
  color: #fff;
}

.error {
  color: #ff0066;
  margin-top: 10px;
}

@media (max-width: 768px) {
  .modal-content {
    padding: 15px;
  }

  .upload-input {
    width: 90%;
    padding: 8px;
  }

  .upload-btn, .close-btn {
    padding: 8px 15px;
  }
}
</style>