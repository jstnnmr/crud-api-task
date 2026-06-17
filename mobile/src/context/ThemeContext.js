import React, { createContext, useState, useContext, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { colors as darkColors } from '../theme/colors';

const lightColors = {
  ...darkColors,
  bg: '#f8f7ff',
  bgLight: '#ffffff',
  bgCard: '#ffffff',
  bgInput: '#f0eff5',
  text: '#1a1a2e',
  textSecondary: '#6b6b8a',
  textMuted: '#a0a0c0',
  textInverse: '#ffffff',
  border: '#e8e7f0',
  borderLight: '#f0eff5',
  overlay: 'rgba(0,0,0,0.3)',
};

const ThemeContext = createContext(null);

export function ThemeProvider({ children }) {
  const [isDark, setIsDark] = useState(true);

  useEffect(() => {
    AsyncStorage.getItem('theme').then((t) => {
      if (t === 'light') setIsDark(false);
    });
  }, []);

  const toggleTheme = async () => {
    const next = !isDark;
    setIsDark(next);
    await AsyncStorage.setItem('theme', next ? 'dark' : 'light');
  };

  const colors = isDark ? darkColors : lightColors;

  return (
    <ThemeContext.Provider value={{ isDark, toggleTheme, colors }}>
      {children}
    </ThemeContext.Provider>
  );
}

export const useTheme = () => useContext(ThemeContext);
