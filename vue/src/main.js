// src/main.js
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
console.log('API Base URL:', process.env.VUE_APP_API_BASE_URL);
const app = createApp(App);
app.use(router);
app.mount('#app');