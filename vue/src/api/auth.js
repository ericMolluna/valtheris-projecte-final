import axios from 'axios';

export async function initializeCsrfToken() {
  try {
    await axios.get('http://localhost:8000/api/sanctum/csrf-cookie');
  } catch (error) {
    console.error('Error al obtener el token CSRF:', error);
    throw error;
  }
}

export function login(email, password) {
  // Simulación de login (reemplazar con llamada a tu API)
  return new Promise((resolve, reject) => {
    if (email === 'root@example' && password === 'root') {
      const token = 'fake-jwt-token';
      resolve({ token });
    } else {
      reject({ message: 'Credenciales incorrectas' });
    }
  });
}

export function isAuthenticated() {
  const token = localStorage.getItem('auth_token');
  return !!token; // Devuelve true si hay token
}

export function logout() {
  localStorage.removeItem('auth_token');
  window.dispatchEvent(new Event('authChange')); // Notifica a toda la app
}

// Función para obtener datos del usuario autenticado
export async function getUser() {
  try {
    const response = await axios.get('http://localhost:8000/api/user', {
      headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` },
    });
    return response.data;
  } catch (error) {
    console.error('Error obteniendo usuario:', error.response ? error.response.data : error.message);
    throw error;
  }
}