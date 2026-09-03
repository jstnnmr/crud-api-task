import React, { useEffect } from 'react';
import { StatusBar } from 'expo-status-bar';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import * as Font from 'expo-font';
import {
  PlusJakartaSans_400Regular,
  PlusJakartaSans_500Medium,
  PlusJakartaSans_600SemiBold,
  PlusJakartaSans_700Bold,
} from '@expo-google-fonts/plus-jakarta-sans';
import { InstrumentSerif_400Regular } from '@expo-google-fonts/instrument-serif';
import { AuthProvider } from './src/context/AuthContext';
import { ThemeProvider, useTheme } from './src/context/ThemeContext';
import { ToastProvider } from './src/context/ToastContext';
import AppNavigator from './src/navigation/AppNavigator';

function AppContent() {
  const { isDark } = useTheme();

  return (
    <>
      <StatusBar style={isDark ? 'light' : 'dark'} />
      <AppNavigator />
    </>
  );
}

export default function App() {
  useEffect(() => {
    Font.loadAsync({
      PlusJakartaSans_400Regular,
      PlusJakartaSans_500Medium,
      PlusJakartaSans_600SemiBold,
      PlusJakartaSans_700Bold,
      InstrumentSerif_400Regular,
    }).catch(() => {
      // Fonts are optional: fall back to system fonts rather than blocking render.
    });
  }, []);

  return (
    <SafeAreaProvider>
      <ThemeProvider>
        <AuthProvider>
          <ToastProvider>
            <AppContent />
          </ToastProvider>
        </AuthProvider>
      </ThemeProvider>
    </SafeAreaProvider>
  );
}
