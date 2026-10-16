import React, { createContext, useState, useEffect, useContext } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import api from '../api/client';

const AuthContext = createContext();

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [employee, setEmployee] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadStoredAuth();
  }, []);

  const loadStoredAuth = async () => {
    try {
      const storedToken = await AsyncStorage.getItem('user_token');
      const storedUser = await AsyncStorage.getItem('user_data');
      const storedEmployee = await AsyncStorage.getItem('employee_data');

      if (storedToken && storedUser) {
        setToken(storedToken);
        setUser(JSON.parse(storedUser));
        if (storedEmployee) {
          setEmployee(JSON.parse(storedEmployee));
        }
      }
    } catch (e) {
      console.log('Error loading auth storage', e);
    } finally {
      setLoading(false);
    }
  };

  const login = async (email, password) => {
    try {
      const response = await api.post('/mobile/login', { email, password });
      if (response.data.success) {
        const { token, user, employee } = response.data;
        await AsyncStorage.setItem('user_token', token);
        await AsyncStorage.setItem('user_data', JSON.stringify(user));
        if (employee) {
          await AsyncStorage.setItem('employee_data', JSON.stringify(employee));
        }
        setToken(token);
        setUser(user);
        setEmployee(employee);
        return { success: true };
      }
      return { success: false, message: response.data.message || 'Login gagal' };
    } catch (err) {
      console.log('Login error details:', err.response?.status, err.response?.data, err.message);
      const msg = err.response?.data?.message || err.message || 'Gagal terhubung ke server. Periksa koneksi internet.';
      return { success: false, message: msg };
    }
  };

  const logout = async () => {
    try {
      if (token) {
        await api.post('/mobile/logout').catch(() => {});
      }
    } catch (e) {
      // ignore
    } finally {
      await AsyncStorage.multiRemove(['user_token', 'user_data', 'employee_data']);
      setToken(null);
      setUser(null);
      setEmployee(null);
    }
  };

  const updateEmployee = (emp) => {
    setEmployee(emp);
    AsyncStorage.setItem('employee_data', JSON.stringify(emp)).catch(() => {});
  };

  return (
    <AuthContext.Provider value={{ user, employee, token, loading, login, logout, updateEmployee }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}
