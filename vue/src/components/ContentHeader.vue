<template>
    <div class="content-header">
      <div class="header-left">
        <h2>{{ title }}</h2>
      </div>
      <div class="header-right" v-if="showCreate">
        <router-link v-if="createPath" :to="createPath" class="create-btn">
          <i class="icon">✍️</i> Crear Nueva Guía
        </router-link>
        <button v-else @click="$emit('create-click')" class="create-btn">
          <i class="icon">{{ createIcon }}</i> Subir
        </button>
      </div>
    </div>
  </template>

  <script>
  export default {
    name: 'ContentHeader',
    props: {
      title: String,
      showCreate: Boolean,
      createPath: String,
      createIcon: {
        type: String,
        default: '📸',
      },
    },
    emits: ['create-click'],
  };
  </script>

  <style scoped>
  .content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 2px solid #ffcc00;
    margin-bottom: 20px;
  }

  h2 {
    color: #ff0066;
    text-shadow: 2px 2px #000;
  }

  .create-btn {
    background: #00ffcc;
    color: #1a1a1a;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s;
    margin-left: 20px;
  }

  .create-btn:hover {
    background: #ffcc00;
  }
  </style>