import React, { createContext, useState, useContext, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { colors as lightColors } from '../theme/colors';
import { darkColors } from '../theme/palette';

const ThemeContext = createContext(null);

export function ThemeProvider({ children }) {
  const [isDark, setIsDark] = useState(false);
  const [loaded, setLoaded] = useState(false);

  useEffect(() => {
    AsyncStorage.getItem('theme').then((t) => {
      if (t === 'dark') setIsDark(true);
      setLoaded(true);
    });
  }, []);

  const toggleTheme = async () => {
    const next = !isDark;
    setIsDark(next);
    await AsyncStorage.setItem('theme', next ? 'dark' : 'light');
  };

  const colors = isDark ? darkColors : lightColors;

  return (
    <ThemeContext.Provider value={{ isDark, toggleTheme, colors, loaded }}>
      {children}
    </ThemeContext.Provider>
  );
}

export const useTheme = () => useContext(ThemeContext);
