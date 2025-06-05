import { createApp } from 'vue';
import App from './App.vue';
import router from './router'; // Imports the router

const app = createApp(App);
app.use(router); // Registers the router
app.mount('#app');

console.log('Aplicación iniciada con router', router);