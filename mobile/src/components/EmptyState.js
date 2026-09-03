import React, { useEffect, useRef } from 'react';
import { View, Text, StyleSheet, Animated, Easing } from 'react-native';
import { useTheme } from '../context/ThemeContext';
import { spacing, fonts } from '../theme/colors';
import { Ionicons } from '@expo/vector-icons';

export default function EmptyState({ icon, title, message }) {
  const { colors } = useTheme();
  const fadeAnim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    Animated.timing(fadeAnim, {
      toValue: 1,
      duration: 320,
      easing: Easing.out(Easing.ease),
      useNativeDriver: true,
    }).start();
  }, []);

  return (
    <Animated.View style={[styles.container, { opacity: fadeAnim }]}>
      <View
        style={[styles.iconWrap, { backgroundColor: colors.softPrimary }]}
      >
        <Ionicons
          name={icon || 'document-text-outline'}
          size={30}
          color={colors.primary}
        />
      </View>
      <Text style={[styles.title, { color: colors.text }]}>{title}</Text>
      {message && (
        <Text style={[styles.message, { color: colors.textMuted }]}>{message}</Text>
      )}
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: spacing.xl,
    paddingVertical: 60,
  },
  iconWrap: {
    width: 64,
    height: 64,
    borderRadius: 20,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.md,
  },
  title: {
    fontFamily: fonts.uiBold,
    fontSize: 18,
    marginTop: spacing.xs,
  },
  message: {
    fontFamily: fonts.uiRegular,
    fontSize: 14,
    marginTop: spacing.sm,
    textAlign: 'center',
    lineHeight: 20,
    maxWidth: 260,
  },
});
