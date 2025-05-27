<template>
    <div class="filter-buttons">
      <button v-for="option in options" :key="option" :class="{ active: sortBy === option }" @click="sortBy = option" class="filter-btn">
        {{ option === 'recent' ? 'Más Recientes' : 'Más Populares' }}
      </button>
    </div>
  </template>

  <script>
  export default {
    name: 'FilterButtons',
    props: {
      options: Array,
      modelValue: String, // For v-model
    },
    computed: {
      sortBy: {
        get() { return this.modelValue; },
        set(value) { this.$emit('update:modelValue', value); },
      },
    },
  };
  </script>

  <style scoped>
  .filter-buttons {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
  }

  .filter-btn {
    background: #1a1a1a;
    color: #00ffcc;
    border: 2px solid #ffcc00;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
  }

  .filter-btn.active {
    background: #ffcc00;
    color: #1a1a1a;
  }

  .filter-btn:hover {
    background: rgba(255, 204, 0, 0.2);
  }
  </style>