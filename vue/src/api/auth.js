import axios from 'axios';

export async function initializeCsrfToken() {
  try {
    await axios.get('http://localhost:8000/sanctum/csrf-cookie');
  } catch (error) {
    console.error('Error al obtener el token CSRF:', error);
    throw error;
  }
}

export async function login(email, password) {
  try {
    await initializeCsrfToken();
    const response = await axios.post('http://localhost:8000/api/login', {
      email,
      password,
    });
    const token = response.data.token;
    localStorage.setItem('auth_token', token);
    window.dispatchEvent(new Event('authChange'));
    return response.data;
  } catch (error) {
    console.error('Error en el login:', error.response ? error.response.data : error.message);
    throw error;
  }
}

export function isAuthenticated() {
  return !!localStorage.getItem('auth_token');
}

export function logout() {
  localStorage.removeItem('auth_token');
  window.dispatchEvent(new Event('authChange'));
}

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