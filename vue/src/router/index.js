import { createRouter, createWebHistory } from 'vue-router';

// Componentes y vistas
import Game from '../components/Game.vue';
import MenuView from '../views/MenuView.vue';
import HomeView from '../views/HomeView.vue';
import NotFound from '../views/NotFound.vue';

// Vistas de autenticación
import LoginView from '../views/Auth/LoginView.vue';
import RegisterView from '../views/Auth/RegisterView.vue';

// Vistas de guías
import CommunityView from '../views/Guides/CommunityView.vue';
import GuideView from '../views/Guides/GuideView.vue';
import CreateGuideView from '../views/Guides/CreateGuideView.vue';
import ScreenshotsDetailsView from '../views/Screenshots/ScreenshotsDetailsView.vue';

// Vistas de videos
import VideoView from '../views/Videos/VideoView.vue';
import VideoDetailsView from '../views/Videos/VideoDetailsView.vue';

// Vistas de perfil
import PerfilView from '../views/Profile/PerfilView.vue';
import EditProfileView from '../views/Profile/EditProfileView.vue';


// Definición de rutas
const routes = [
  { path: '/', component: MenuView, name: 'Home' },
  { path: '/home', component: HomeView, name: 'HomeView' },
  { path: '/login', component: LoginView, name: 'Login' },
  { path: '/register', component: RegisterView, name: 'Register' },
  { path: '/juego', component: Game, name: 'Juego' },
  { path: '/perfil', component: PerfilView, name: 'Perfil' },
  { path: '/perfil/editar', component: EditProfileView, name: 'EditProfile' },
  {
    path: '/comunidad',
    component: CommunityView,
    name: 'Comunidad',
  },
  {
    path: '/comunidad/guias',
    component: CommunityView,
    name: 'Guias',
  },
  {
    path: '/comunidad/capturas',
    name: 'Screenshots',
    component: CommunityView,
  },
  {
    path: '/capturas/:id',
    name: 'ScreenshotDetails',
    component: ScreenshotsDetailsView,
    props: true,
  },
  {
    path: '/comunidad/guia/:id',
    name: 'GuideView',
    component: GuideView,
    props: true,
  },
  {
    path: '/comunidad/crear-guia',
    name: 'CreateGuide',
    component: CreateGuideView,
    meta: { requiresAuth: true },
  },
  {
    path: '/videos',
    name: 'Videos',
    component: VideoView,
  },
  {
    path: '/videos/:id',
    name: 'VideoDetails',
    component: VideoDetailsView,
    props: true,
  },
  {
    path: '/retransmisiones',
    name: 'Retransmissions',
    component: CommunityView,
  },
  {
    path: '/about',
    name: 'About',
    component: () => import('../views/AboutView.vue'),
  },
 
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound,
  },
];

// Crear el enrutador
const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

// Guardia de navegación para rutas protegidas
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('auth_token');
  
  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'Login' });
  } else {
    next();
  }
});

export default router;