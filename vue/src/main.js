import { createApp } from 'vue'
import App from './App.vue'
import router from './router' // Asegúrate de que esta línea esté presente

const app = createApp(App)
app.use(router) // Registra el router
app.mount('#app')

console.log('Aplicación iniciada con router', router);