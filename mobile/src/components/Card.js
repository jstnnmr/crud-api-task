import React, { useRef } from 'react';
import { View, StyleSheet, Pressable, Animated } from 'react-native';
import { useTheme } from '../context/ThemeContext';
import { borderRadius, spacing, shadows } from '../theme/colors';

export default function Card({ children, onPress, style, noPadding, elevated }) {
  const { colors } = useTheme();
  const scaleAnim = useRef(new Animated.Value(1)).current;

  const handlePressIn = () => {
    Animated.timing(scaleAnim, {
      toValue: 0.99,
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

  const cardStyle = [
    styles.card,
    elevated ? shadows.sm : null,
    {
      backgroundColor: colors.bgCard,
      borderColor: colors.border,
    },
    noPadding && { padding: 0 },
    style,
  ];

  if (onPress) {
    return (
      <Pressable
        onPressIn={handlePressIn}
        onPressOut={handlePressOut}
        onPress={onPress}
        accessibilityRole="button"
        style={({ pressed }) => [{ opacity: pressed ? 0.85 : 1 }]}
      >
        <Animated.View style={[...cardStyle, { transform: [{ scale: scaleAnim }] }]}>
          {children}
        </Animated.View>
      </Pressable>
    );
  }

  return <View style={cardStyle}>{children}</View>;
}

const styles = StyleSheet.create({
  card: {
    borderRadius: borderRadius.lg,
    padding: spacing.md,
    borderWidth: 1,
    marginBottom: spacing.md,
  },
});
