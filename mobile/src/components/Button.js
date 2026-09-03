import React, { useRef } from 'react';
import { Animated, Text, StyleSheet, ActivityIndicator, Pressable } from 'react-native';
import { useTheme } from '../context/ThemeContext';
import { borderRadius, spacing, fonts, shadows } from '../theme/colors';

export default function Button({
  title,
  onPress,
  variant = 'primary',
  size = 'md',
  loading = false,
  disabled = false,
  style,
  icon,
}) {
  const { colors } = useTheme();
  const scaleAnim = useRef(new Animated.Value(1)).current;

  const config = {
    primary: { bg: colors.primary, fg: '#FFFFFF' },
    secondary: { bg: colors.softPrimary, fg: colors.primary },
    ghost: { bg: 'transparent', fg: colors.textSecondary },
    danger: { bg: colors.danger, fg: '#FFFFFF' },
  }[variant];

  const isFilled = variant === 'primary' || variant === 'danger';

  const height = size === 'sm' ? 42 : size === 'lg' ? 58 : 52;
  const fontSize = size === 'sm' ? 13 : size === 'lg' ? 16 : 15;

  const handlePressIn = () => {
    Animated.timing(scaleAnim, {
      toValue: 0.975,
      duration: 90,
      useNativeDriver: true,
    }).start();
  };

  const handlePressOut = () => {
    Animated.timing(scaleAnim, {
      toValue: 1,
      duration: 140,
      useNativeDriver: true,
    }).start();
  };

  return (
    <Pressable
      onPressIn={handlePressIn}
      onPressOut={handlePressOut}
      onPress={onPress}
      disabled={disabled || loading}
      accessibilityRole="button"
      accessibilityState={{ disabled: disabled || loading, busy: loading }}
      style={({ pressed }) => [{ opacity: pressed || disabled ? 0.75 : 1 }]}
    >
      <Animated.View
        style={[
          styles.button,
          {
            backgroundColor: config.bg,
            height,
            transform: [{ scale: scaleAnim }],
          },
          variant === 'ghost' && { borderWidth: 1, borderColor: 'transparent' },
          isFilled && shadows.sm,
          disabled && { opacity: 0.5 },
          style,
        ]}
      >
        {loading ? (
          <ActivityIndicator color={config.fg} size="small" />
        ) : (
          <>
            {icon}
            <Text style={[styles.text, { color: config.fg, fontSize }]}>{title}</Text>
          </>
        )}
      </Animated.View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  button: {
    borderRadius: borderRadius.md,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
    gap: spacing.sm,
    paddingHorizontal: spacing.lg,
  },
  text: {
    fontFamily: fonts.uiSemiBold,
    letterSpacing: 0.2,
  },
});
