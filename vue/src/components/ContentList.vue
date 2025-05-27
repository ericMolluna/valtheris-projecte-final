<template>
  <div class="content-list">
    <div v-if="loading" class="loading">Cargando...</div>
    <div v-else-if="errorMessage" class="error">{{ errorMessage }}</div>
    <div v-else-if="items.length" class="items">
      <div v-for="item in items" :key="item.id" class="item" @click="$emit('item-click', item)">
        {{ item.title || item.name }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ContentList',
  props: {
    items: Array,
    loading: Boolean,
    errorMessage: String,
  },
};
</script>

<style scoped>
.content-list {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.item {
  padding: 10px;
  margin: 10px 0;
  background-color: rgba(0, 0, 0, 0.7);
  border: 2px solid #00ffcc;
  border-radius: 4px;
  color: #ffffff;
  cursor: pointer;
  transition: transform 0.2s;
}

.item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 255, 204, 0.5);
}

.loading, .error {
  color: #ff0066;
  text-align: center;
  padding: 20px;
}

@media (max-width: 768px) {
  .item {
    padding: 8px;
    font-size: 0.9em;
  }
}

@media (max-width: 480px) {
  .item {
    padding: 6px;
    font-size: 0.8em;
  }
}
</style>