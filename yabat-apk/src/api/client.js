import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Platform } from 'react-native';

// Konfigurasi Environment API
// Jika dibuka di browser web desktop (expo start --web), otomatis ke localhost:8000
const IS_WEB = Platform.OS === 'web';
const LOCAL_DEV_URL = IS_WEB ? 'http://localhost:8000' : 'http://192.168.1.100:8000';
const PRODUCTION_URL = 'https://yabatpresensi.stkip-us.ac.id';

export const BASE_URL = IS_WEB ? LOCAL_DEV_URL : PRODUCTION_URL;

const api = axios.create({
  baseURL: `${BASE_URL}/api`,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 15000,
});

// Auto attach Sanctum bearer token jika ada
api.interceptors.request.use(async (config) => {
  try {
    const token = await AsyncStorage.getItem('user_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
  } catch (error) {
    console.log('Error reading token:', error);
  }
  return config;
}, (error) => {
  return Promise.reject(error);
});

export default api;
