import React, { createContext, useState, useEffect, useContext } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { authApi } from '../api/auth';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadStorage();
  }, []);

  const loadStorage = async () => {
    try {
      const [storedToken, storedUser] = await AsyncStorage.multiGet([
        'authToken',
        'user',
      ]);
      if (storedToken[1]) {
        setToken(storedToken[1]);
        if (storedUser[1]) {
          setUser(JSON.parse(storedUser[1]));
        }
      }
    } catch (e) {
      console.error('Failed to load auth state', e);
    } finally {
      setLoading(false);
    }
  };

  const saveAuth = async (newToken, newUser) => {
    setToken(newToken);
    setUser(newUser);
    await AsyncStorage.multiSet([
      ['authToken', newToken],
      ['user', JSON.stringify(newUser)],
    ]);
  };

  const login = async (email, password) => {
    const res = await authApi.login({ email, password });
    const { user: u, token: t } = res.data.data;
    await saveAuth(t, u);
    return res.data;
  };

  const register = async (data) => {
    const res = await authApi.register(data);
    return res.data;
  };

  const verifyEmail = async (code) => {
    await authApi.verifyEmail({ email: user?.email, code });
    const updated = { ...user, email_verified_at: new Date().toISOString() };
    setUser(updated);
    await AsyncStorage.setItem('user', JSON.stringify(updated));
  };

  const logout = async () => {
    setToken(null);
    setUser(null);
    await AsyncStorage.multiRemove(['authToken', 'user']);
  };

  const refreshUser = async () => {
    try {
      const res = await authApi.getProfile();
      setUser(res.data.user || res.data.data || res.data);
    } catch (e) {
      console.error('Failed to refresh user', e);
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        loading,
        login,
        register,
        logout,
        verifyEmail,
        refreshUser,
        isAuthenticated: !!token,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);
