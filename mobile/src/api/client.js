import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Change this to your computer's local IP when testing on a real iPhone
// e.g. 'http://192.168.1.100:8000/api'
const API_BASE = 'http://crud-api-task.test/api';

const api = axios.create({
  baseURL: API_BASE,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  timeout: 15000,
});

api.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem('authToken');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      await AsyncStorage.multiRemove(['authToken', 'user']);
    }
    return Promise.reject(error);
  }
);

export const setApiBase = (url) => {
  api.defaults.baseURL = url;
};

export default api;
