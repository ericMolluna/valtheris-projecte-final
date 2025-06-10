import { reactive, onMounted, onUnmounted, watch, toRefs, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import { isAuthenticated } from '@/api/auth';

export default function CommunityViewLogic() {
  const router = useRouter();
  const state = reactive({
    isAuthenticated: localStorage.getItem('auth_token') !== null,
    user: null,
    userTier: 'Tier 1',
    guides: [],
    loadingGuides: false,
    errorMessageGuides: '',
    sortByGuides: 'recent',
    screenshots: [],
    loadingScreenshots: false,
    errorMessageScreenshots: '',
    sortByScreenshots: 'recent',
    showUploadForm: false,
    newScreenshot: { title: '', image: null, description: '' },
    uploadError: '',
    selectedScreenshot: null,
    comments: [],
    newComment: '',
    videos: [],
    loadingVideos: false,
    errorMessageVideos: '',
    sortByVideos: 'recent',
    showVideoUploadForm: false,
    newVideo: { title: '', video: null, description: '' },
    videoUploadError: '',
    selectedVideo: null,
    videoComments: [],
    newVideoComment: '',
    allContent: [],
    loadingAllContent: false,
    errorMessageAllContent: '',
    tabs: [
      { name: 'Todo', path: '/comunidad' },
      { name: 'Capturas', path: '/comunidad/capturas' },
      { name: 'Guías', path: '/comunidad/guias' },
      { name: 'Videos', path: '/videos' },
    ],
    isProcessing: false,
  });

  const navItems = computed(() => {
    const baseItems = [
      { label: 'Inicio', route: '/', icon: '🏠' },
      { label: 'Comunidad', route: '/comunidad', icon: '👥' },
    ];
    const authItem = state.isAuthenticated
      ? { label: 'Perfil', route: '/perfil', icon: '👤' }
      : { label: 'Login', route: '/login', icon: '🔑' };
    return [...baseItems, authItem];
  });

  const sortedGuides = computed(() => sortItems(state.guides, state.sortByGuides, 'likes', 'created_at'));
  const sortedScreenshots = computed(() => sortItems(state.screenshots, state.sortByScreenshots, 'popularity', 'created_at'));
  const sortedVideos = computed(() => sortItems(state.videos, state.sortByVideos, 'popularity', 'created_at'));

  function sortItems(items, sortBy, popularityKey, dateKey) {
    return [...items].sort((a, b) =>
      sortBy === 'popular' ? (b[popularityKey] || 0) - (a[popularityKey] || 0) : new Date(b[dateKey]) - new Date(a[dateKey])
    );
  }

  onMounted(() => {
    window.addEventListener('storage', updateAuthStatus);
    checkAuthStatus();
    fetchUserData();
    updateRouteData();
  });

  onUnmounted(() => {
    window.removeEventListener('storage', updateAuthStatus);
  });

  watch(() => router.currentRoute.value.path, updateRouteData);
  watch(() => [state.sortByGuides, state.sortByScreenshots, state.sortByVideos], () => updateRouteData());

  async function updateRouteData() {
    const path = router.currentRoute.value.path;
    if (path === '/comunidad') {
      await Promise.all([fetchGuides(), fetchScreenshots(), fetchVideos()]);
    } else if (path === '/comunidad/guias') {
      await fetchGuides();
    } else if (path === '/comunidad/capturas') {
      await fetchScreenshots();
    } else if (path === '/videos') {
      await fetchVideos();
    }
  }

  async function fetchData(url, stateKey, type, transform = (item) => item) {
    state[`loading${stateKey}`] = true;
    state[`errorMessage${stateKey}`] = '';
    try {
      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      await axios.get('sanctum/csrf-cookie');
      const response = await axios.get(`${url}?sort=${state[`sortBy${stateKey}`]}`);
      state[stateKey] = response.data.map(item => transform({ ...item, type }));
      combineAllContent();
    } catch (error) {
      state[`errorMessage${stateKey}`] = error.response?.data?.message || 'Error al cargar contenido.';
      state[stateKey] = [];
    } finally {
      state[`loading${stateKey}`] = false;
    }
  }

  async function fetchGuides() {
    await fetchData('/api/guides/all', 'guides', 'guide', (item) => {
        const transformed = {
            ...item,
            image: item.image_url ? `http://localhost:8000${item.image_url}` : '/images/placeholder.jpg',
            type: 'guide',
        };
        console.log('Transformed guide:', transformed);
        return transformed;
    });
}
  async function fetchScreenshots() {
    await fetchData('/api/screenshots', 'screenshots', 'screenshot', (item) => ({
      ...item,
      image_url: `http://localhost:8000${item.image_url}`,
      likesCount: item.likes || item.likes_count || 0,
      dislikesCount: item.dislikes || item.dislikes_count || 0,
      isLikedByUser: item.user_liked || false,
      isDislikedByUser: item.user_disliked || false,
    }));
  }

  async function fetchVideos() {
    await fetchData('/api/videos', 'videos', 'video', (item) => ({
        ...item,
        video_url: `http://localhost:8000${item.video_url}`,
        thumbnail_url: item.thumbnail_url ? `http://localhost:8000${item.thumbnail_url}` : '/images/placeholder.jpg',
        type: 'video',
    }));
}

  function combineAllContent() {
    state.loadingAllContent = true;
    try {
      state.allContent = [...state.guides, ...state.screenshots, ...state.videos].sort((a, b) =>
        new Date(b.created_at) - new Date(a.created_at)
      );
    } catch (error) {
      state.errorMessageAllContent = 'Error al combinar el contenido.';
      state.allContent = [];
    } finally {
      state.loadingAllContent = false;
    }
  }

  async function handleLogout() {
    try {
      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      await axios.get('sanctum/csrf-cookie');
      const token = localStorage.getItem('auth_token');
      if (token) {
        await axios.post('/api/logout', {}, {
          headers: { Authorization: `Bearer ${token}` },
        });
      }
      localStorage.removeItem('auth_token');
      state.isAuthenticated = false;
      state.user = null;
      state.userTier = 'Tier 1';
      router.push('/login');
      await Swal.fire({
        title: 'Sesión cerrada',
        text: 'Has cerrado sesión exitosamente.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
      });
    } catch (error) {
      console.error('Error en logout:', error);
      await Swal.fire({
        title: 'Error',
        text: 'No se pudo cerrar la sesión.',
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
    }
  }

  async function checkAuthStatus() {
    state.isAuthenticated = await isAuthenticated();
    if (!state.isAuthenticated) {
      localStorage.removeItem('auth_token');
    }
  }

  function updateAuthStatus() {
    state.isAuthenticated = localStorage.getItem('auth_token') !== null;
    if (state.isAuthenticated) {
      fetchUserData();
    } else {
      state.userTier = 'Tier 1';
    }
  }

  async function fetchUserData() {
    try {
      const token = localStorage.getItem('auth_token');
      if (!token) return;

      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      await axios.get('sanctum/csrf-cookie');
      const response = await axios.get('/api/user');
      state.user = {
        id: response.data.id,
        name: response.data.name,
        email: response.data.email,
      };
      state.userTier = response.data.tier || 'Tier 1';
    } catch (error) {
      console.error('Error en fetchUserData:', error);
      state.errorMessageGuides = error.response ? error.response.data.message : error.message;
      state.errorMessageScreenshots = error.response ? error.response.data.message : error.message;
      state.errorMessageVideos = error.response ? error.response.data.message : error.message;
      localStorage.removeItem('auth_token');
      state.isAuthenticated = false;
      state.userTier = 'Tier 1';
    }
  }

  function handleFileChange(event) {
    state.newScreenshot.image = event.target.files[0];
  }

  async function uploadScreenshot() {
    if (!state.newScreenshot.image) {
      state.uploadError = 'Por favor, selecciona una imagen válida.';
      await Swal.fire({
        title: 'Error',
        text: state.uploadError,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
      return;
    }
    if (!state.newScreenshot.title.trim()) {
      state.uploadError = 'Por favor, proporciona un título para la captura.';
      await Swal.fire({
        title: 'Error',
        text: state.uploadError,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
      return;
    }
    const maxSize = 2048 * 1024;
    if (state.newScreenshot.image.size > maxSize) {
      state.uploadError = 'La imagen excede el tamaño máximo de 2MB.';
      await Swal.fire({
        title: 'Error',
        text: state.uploadError,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
      return;
    }
    if (!state.newScreenshot.image.type.startsWith('image/')) {
      state.uploadError = 'El archivo seleccionado no es una imagen válida.';
      await Swal.fire({
        title: 'Error',
        text: state.uploadError,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
      return;
    }
    const formData = new FormData();
    formData.append('title', state.newScreenshot.title);
    formData.append('image', state.newScreenshot.image);
    formData.append('description', state.newScreenshot.description || '');
    try {
      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      await axios.get('sanctum/csrf-cookie');
      const token = localStorage.getItem('auth_token');
      if (!token) {
        throw new Error('No se encontró el token de autenticación.');
      }
      await axios.post('/api/screenshots', formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data',
        },
      });
      state.showUploadForm = false;
      state.newScreenshot = { title: '', image: null, description: '' };
      state.uploadError = '';
      await fetchScreenshots();
      await Swal.fire({
        title: '¡Captura subida!',
        text: 'Tu captura ha sido subida exitosamente.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
      });
    } catch (error) {
      let errorMessage = 'Error al subir la captura.';
      if (error.response) {
        errorMessage = error.response.data.message || `Error del servidor: ${error.response.status}`;
      } else if (error.request) {
        errorMessage = 'No se recibió respuesta del servidor.';
      } else {
        errorMessage = error.message;
      }
      state.uploadError = errorMessage;
      await Swal.fire({
        title: 'Error',
        text: state.uploadError,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
    }
  }

  function handleVideoFileChange(event) {
    state.newVideo.video = event.target.files[0];
  }

  async function uploadVideo() {
    if (!state.newVideo.video) {
      state.videoUploadError = 'Por favor, selecciona un video válido.';
      await Swal.fire({
        title: 'Error',
        text: state.videoUploadError,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
      return;
    }
    if (!state.newVideo.title.trim()) {
      state.videoUploadError = 'Por favor, proporciona un título para el video.';
      await Swal.fire({
        title: 'Error',
        text: state.videoUploadError,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
      return;
    }
    const maxSize = 10 * 1024 * 1024;
    if (state.newVideo.video.size > maxSize) {
      state.videoUploadError = 'El video excede el tamaño máximo de 10MB.';
      await Swal.fire({
        title: 'Error',
        text: state.videoUploadError,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
      return;
    }
    if (!state.newVideo.video.type.startsWith('video/')) {
      state.videoUploadError = 'El archivo seleccionado no es un video válido.';
      await Swal.fire({
        title: 'Error',
        text: state.videoUploadError,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
      return;
    }
    const formData = new FormData();
    formData.append('title', state.newVideo.title);
    formData.append('video', state.newVideo.video);
    formData.append('description', state.newVideo.description || '');
    try {
      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      await axios.get('sanctum/csrf-cookie');
      const token = localStorage.getItem('auth_token');
      if (!token) {
        throw new Error('No se encontró el token de autenticación.');
      }
      await axios.post('/api/videos', formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data',
        },
      });
      state.showVideoComments = false;
      state.newVideo = { title: '', video: null, description: '' };
      state.videoComments.state = '';
      await fetchVideos();
      await Swal.fire({
        title: '¡Video subido!',
        text: 'Tu video ha sido subido exitosamente.',
        icon: 'success',
        timer: 1500,
        showConfirmButton: false,
      });
    } catch (error) {
      let errorMessage = 'Error al cargar el video.';
      if (error.response) {
        errorMessage = error.response.data.message || `Error del servidor: ${error.response.status}`;
      } else if (error.request) {
        errorMessage = 'No se recibió respuesta del servidor.';
      } else {
        errorMessage = error.message;
      }
      state.videoUploadError = errorMessage;
      await Swal.fire({
        title: 'Error',
        text: state.videoUploadError,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
    }
  }

  async function openScreenshotModal(screenshot) {
    try {
      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      await axios.get('sanctum/csrf-cookie');
      const token = localStorage.getItem('auth_token');
      const headers = token ? { Authorization: `Bearer ${token}` } : {};
      const response = await axios.get(`/api/screenshots/${screenshot.id}`, { headers });
      state.selectedScreenshot = {
        ...response.data,
        image_url: `http://localhost:8000${response.data.image_url}`,
        type: 'screenshot',
        likesCount: response.data.likes_count || response.data.likes || 0,
        dislikesCount: response.data.dislikes_count || response.data.dislikes || 0,
        isLikedByUser: response.data.user_liked || false,
        isDislikedByUser: response.data.user_disliked || false,
      };
      await fetchComments(screenshot.id);
    } catch (error) {
      state.errorMessageScreenshots = 'No se pudo cargar la captura.';
    }
  }

  function closeScreenshotModal() {
    state.selectedScreenshot = null;
    state.comments = [];
    state.newComment = '';
  }

  async function fetchComments(screenshotId) {
    try {
      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      await axios.get('sanctum/csrf-cookie');
      const response = await axios.get(`/api/screenshots/${screenshotId}/comments`);
      state.comments = response.data;
    } catch (error) {
      state.errorMessageScreenshots = 'No se pudieron cargar los comentarios.';
    }
  }

  async function submitComment() {
    console.log('CommunityViewLogic: Evento submit-comment recibido, newComment:', JSON.stringify(state.newComment));
    if (!state.newComment.trim()) {
        console.log('CommunityViewLogic: Comentario vacío detectado:', JSON.stringify(state.newComment));
        await Swal.fire({
            title: 'Comentario vacío',
            text: 'Por favor, escribe un comentario antes de enviar.',
            icon: 'warning',
            confirmButtonColor: '#ff6f61',
        });
        return;
    }
    try {
        axios.defaults.baseURL = 'http://localhost:8000';
        axios.defaults.withCredentials = true;
        console.log('CommunityViewLogic: Obteniendo CSRF cookie para submitComment...');
        await axios.get('sanctum/csrf-cookie').then(() => {
            console.log('CommunityViewLogic: CSRF cookie obtenido para submitComment');
        });
        const token = localStorage.getItem('auth_token');
        if (!token) {
            throw new Error('No se encontró el token de autenticación.');
        }
        console.log('CommunityViewLogic: Enviando comentario:', state.newComment);
       
        state.newComment = '';
        await fetchComments(state.selectedScreenshot.id);
        await Swal.fire({
            title: '¡Comentario enviado!',
            text: 'Tu comentario ha sido publicado exitosamente.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
        });
    } catch (error) {
        console.error('CommunityViewLogic: Error al enviar comentario:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Error al enviar el comentario.';
        state.errorMessageScreenshots = errorMessage;
        await Swal.fire({
            title: 'Error',
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#ff6f61',
        });
    }
}

  async function toggleLike(screenshot) {
    if (!state.isAuthenticated) {
      await Swal.fire({
        title: 'Inicia sesión',
        text: 'Debes iniciar sesión para dar "Me gusta".',
        icon: 'warning',
        confirmButtonColor: '#ff6f61',
      });
      router.push('/login');
      return;
    }
    if (state.isProcessing) return;
    state.isProcessing = true;
    try {
      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      await axios.get('sanctum/csrf-cookie');
      const token = localStorage.getItem('auth_token');
      if (!token) {
        throw new Error('No se encontró el token de autenticación.');
      }
      const method = screenshot.isLikedByUser ? 'delete' : 'post';
      const response = await axios({
        method,
        url: `/api/screenshots/${screenshot.id}/like`,
        headers: { Authorization: `Bearer ${token}` },
      });
      state.selectedScreenshot = {
        ...state.selectedScreenshot,
        likesCount: response.data.likes,
        dislikesCount: response.data.dislikes,
        isLikedByUser: response.data.user_liked,
        isDislikedByUser: response.data.user_disliked,
      };
      await fetchScreenshots();
      await Swal.fire({
        title: screenshot.isLikedByUser ? 'Me gusta eliminado' : '¡Me gusta añadido!',
        text: screenshot.isLikedByUser ? 'Has quitado tu "Me gusta".' : 'Has dado "Me gusta" a esta captura.',
        icon: 'success',
        timer: 1500,
        showConfirmButton: false,
      });
    } catch (error) {
      state.errorMessageScreenshots = error.response?.data?.message || 'Error al procesar tu Me gusta.';
      await Swal.fire({
        title: 'Error',
        text: state.errorMessageScreenshots,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
    } finally {
      state.isProcessing = false;
    }
  }

  async function toggleDislike(screenshot) {
    if (!state.isAuthenticated) {
      await Swal.fire({
        title: 'Inicia sesión',
        text: 'Debes iniciar sesión para dar "No me gusta".',
        icon: 'warning',
        confirmButtonColor: '#ff6f61',
      });
      router.push('/login');
      return;
    }
    if (state.isProcessing) return;
    state.isProcessing = true;
    try {
      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      await axios.get('sanctum/csrf-cookie');
      const token = localStorage.getItem('auth_token');
      if (!token) {
        throw new Error('No se encontró el token de autenticación.');
      }
      const method = screenshot.isDislikedByUser ? 'delete' : 'post';
      const response = await axios({
        method,
        url: `/api/screenshots/${screenshot.id}/dislike`,
        headers: { Authorization: `Bearer ${token}` },
      });
      state.selectedScreenshot = {
        ...state.selectedScreenshot,
        likesCount: response.data.likes,
        dislikesCount: response.data.dislikes,
        isLikedByUser: response.data.user_liked,
        isDislikedByUser: response.data.user_disliked,
      };
      await fetchScreenshots();
      await Swal.fire({
        title: screenshot.isDislikedByUser ? 'No me gusta eliminado' : '¡No me gusta añadido!',
        text: screenshot.isDislikedByUser ? 'Has quitado tu "No me gusta".' : 'Has dado "No me gusta" a esta captura.',
        icon: 'success',
        timer: 1500,
        showConfirmButton: false,
      });
    } catch (error) {
      state.errorMessageScreenshots = error.response?.data?.message || 'Error al procesar tu No me gusta.';
      await Swal.fire({
        title: 'Error',
        text: state.errorMessageScreenshots,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
    } finally {
      state.isProcessing = false;
    }
  }

  async function openVideoModal(video) {
    try {
      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      await axios.get('sanctum/csrf-cookie');
      const token = localStorage.getItem('auth_token');
      const headers = token ? { Authorization: `Bearer ${token}` } : {};
      const response = await axios.get(`/api/videos/${video.id}`, { headers });
      state.selectedVideo = {
        ...response.data,
        video_url: `http://localhost:8000${response.data.video_url}`,
        type: 'video',
      };
      await fetchVideoComments(video.id);
    } catch (error) {
      state.errorMessageVideos = 'No se pudo cargar el video.';
    }
  }

  function closeVideoModal() {
    state.selectedVideo = null;
    state.videoComments = [];
    state.newVideoComment = '';
  }

  async function fetchVideoComments(videoId) {
    try {
      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      await axios.get('sanctum/csrf-cookie');
      const response = await axios.get(`/api/videos/${videoId}/comments`);
      state.videoComments = response.data;
    } catch (error) {
      state.errorMessageVideos = 'No se pudieron cargar los comentarios.';
    }
  }

  async function submitVideoComment() {
    if (!state.newVideoComment.trim()) {
      await Swal.fire({
        title: 'Comentario vacío',
        text: 'Por favor, escribe un comentario antes de enviar.',
        icon: 'warning',
        confirmButtonColor: '#ff6f61',
      });
      return;
    }
    try {
      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      await axios.get('sanctum/csrf-cookie');
      const token = localStorage.getItem('auth_token');
      if (!token) {
        throw new Error('No se encontró el token de autenticación.');
      }
      await axios.post(
        `/api/videos/${state.selectedVideo.id}/comments`,
        { text: state.newVideoComment },
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
      state.newVideoComment = '';
      await fetchVideoComments(state.selectedVideo.id);
      await Swal.fire({
        title: '¡Comentario enviado!',
        text: 'Tu comentario ha sido publicado exitosamente.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
      });
    } catch (error) {
      state.errorMessageVideos = error.response?.data?.message || 'Error al enviar el comentario.';
      await Swal.fire({
        title: 'Error',
        text: state.errorMessageVideos,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
    }
  }

  async function deleteScreenshot(screenshotId) {
    const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: '¿Quieres eliminar esta captura?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ff6f61',
      cancelButtonColor: '#2a3f2a',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
    });
    if (!result.isConfirmed) return;
    try {
      axios.defaults.baseURL = 'http://localhost:8000';
      axios.defaults.withCredentials = true;
      await axios.get('sanctum/csrf-cookie');
      const token = localStorage.getItem('auth_token');
      if (!token) {
        throw new Error('No se encontró el token de autenticación.');
      }
      await axios.delete(`/api/screenshots/${screenshotId}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      state.screenshots = state.screenshots.filter(s => s.id !== screenshotId);
      closeScreenshotModal();
      await Swal.fire({
        title: '¡Eliminado!',
        text: 'La captura ha sido eliminada.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
      });
      if (router.currentRoute.value.path !== '/comunidad/capturas') {
        router.push('/comunidad/capturas');
      }
    } catch (error) {
      state.errorMessageScreenshots = error.response
        ? `Error ${error.response.status}: ${error.response.data.message || error.response.statusText}`
        : error.message;
      await Swal.fire({
        title: 'Error',
        text: `No se pudo eliminar la captura: ${state.errorMessageScreenshots}`,
        icon: 'error',
        confirmButtonColor: '#ff6f61',
      });
    }
  }

  function viewGuide(guide) {
    router.push(`/comunidad/guia/${guide.id}`);
  }

  function handleContentClick(item) {
    if (item.type === 'guide') {
      viewGuide(item);
    } else if (item.type === 'screenshot') {
      openScreenshotModal(item);
    } else if (item.type === 'video') {
      openVideoModal(item);
    }
  }

  return {
    ...toRefs(state),
    navItems,
    sortedGuides,
    sortedScreenshots,
    sortedVideos,
    handleLogout,
    checkAuthStatus,
    updateAuthStatus,
    fetchUserData,
    fetchGuides,
    fetchScreenshots,
    fetchVideos,
    combineAllContent,
    handleFileChange,
    uploadScreenshot,
    handleVideoFileChange,
    uploadVideo,
    openScreenshotModal,
    closeScreenshotModal,
    fetchComments,
    submitComment,
    toggleLike,
    toggleDislike,
    openVideoModal,
    closeVideoModal,
    fetchVideoComments,
    submitVideoComment,
    deleteScreenshot,
    viewGuide,
    handleContentClick,
  };
}