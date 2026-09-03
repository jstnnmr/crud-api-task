import React, { createContext, useState, useContext, useRef, useCallback } from 'react';
import { View, Text, StyleSheet, Animated, TouchableOpacity } from 'react-native';
import { useTheme } from './ThemeContext';
import { spacing, borderRadius } from '../theme/colors';
import { Ionicons } from '@expo/vector-icons';

const ToastContext = createContext(null);

const ICONS = {
  success: 'checkmark-circle',
  error: 'alert-circle',
  info: 'information-circle',
};

export function ToastProvider({ children }) {
  const { colors } = useTheme();
  const [toast, setToast] = useState(null);
  const opacity = useRef(new Animated.Value(0)).current;
  const translateY = useRef(new Animated.Value(50)).current;
  const timerRef = useRef(null);

  const showToast = useCallback((message, type = 'info', duration = 3000) => {
    if (timerRef.current) clearTimeout(timerRef.current);
    setToast({ message, type });
    Animated.parallel([
      Animated.timing(opacity, { toValue: 1, duration: 250, useNativeDriver: false }),
      Animated.timing(translateY, { toValue: 0, duration: 250, useNativeDriver: false }),
    ]).start();
    timerRef.current = setTimeout(() => {
      Animated.parallel([
        Animated.timing(opacity, { toValue: 0, duration: 200, useNativeDriver: false }),
        Animated.timing(translateY, { toValue: 50, duration: 200, useNativeDriver: false }),
      ]).start(() => setToast(null));
    }, duration);
  }, []);

  const dismiss = () => {
    if (timerRef.current) clearTimeout(timerRef.current);
    Animated.parallel([
      Animated.timing(opacity, { toValue: 0, duration: 200, useNativeDriver: false }),
      Animated.timing(translateY, { toValue: 50, duration: 200, useNativeDriver: false }),
    ]).start(() => setToast(null));
  };

  const toastColors = {
    success: colors.success,
    error: colors.danger,
    info: colors.primary,
  };

  return (
    <ToastContext.Provider value={{ showToast }}>
      {children}
      {toast && (
        <Animated.View
          style={[
            styles.wrapper,
            { opacity, transform: [{ translateY }] },
          ]}
          pointerEvents="box-none"
        >
          <TouchableOpacity
            activeOpacity={0.9}
            onPress={dismiss}
            style={[
              styles.toast,
              {
                backgroundColor: colors.bgCard,
                borderColor: toastColors[toast.type],
                borderLeftColor: toastColors[toast.type],
              },
            ]}
          >
            <Ionicons name={ICONS[toast.type]} size={20} color={toastColors[toast.type]} />
            <Text style={[styles.message, { color: colors.text }]} numberOfLines={2}>
              {toast.message}
            </Text>
          </TouchableOpacity>
        </Animated.View>
      )}
    </ToastContext.Provider>
  );
}

export const useToast = () => useContext(ToastContext);

const styles = StyleSheet.create({
  wrapper: {
    position: 'absolute',
    bottom: 90,
    left: 16,
    right: 16,
    zIndex: 9999,
    elevation: 9999,
  },
  toast: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    padding: 14,
    borderRadius: borderRadius.md,
    borderWidth: 1,
    borderLeftWidth: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 8,
  },
  message: {
    flex: 1,
    fontSize: 14,
    fontWeight: '500',
  },
});
