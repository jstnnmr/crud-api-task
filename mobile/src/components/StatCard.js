import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { useTheme } from '../context/ThemeContext';
import { borderRadius, spacing, fonts, shadows } from '../theme/colors';
import { Ionicons } from '@expo/vector-icons';

export default function StatCard({ icon, label, value, color }) {
  const { colors, isDark } = useTheme();
  const fill = isDark ? '30' : '18';

  return (
    <View
      style={[
        styles.card,
        {
          backgroundColor: colors.bgCard,
          borderColor: colors.border,
        },
      ]}
    >
      <View
        style={[
          styles.iconWrap,
          { backgroundColor: (color || colors.primary) + fill },
        ]}
      >
        <Ionicons name={icon} size={18} color={color || colors.primary} />
      </View>
      <Text style={[styles.value, { color: colors.text }]}>{value}</Text>
      <Text style={[styles.label, { color: colors.textMuted }]}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    flex: 1,
    borderRadius: borderRadius.lg,
    padding: spacing.md,
    borderWidth: 1,
    alignItems: 'flex-start',
    marginHorizontal: 4,
  },
  iconWrap: {
    width: 34,
    height: 34,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.sm,
  },
  value: {
    fontFamily: fonts.display,
    fontSize: 32,
    lineHeight: 36,
  },
  label: {
    fontFamily: fonts.uiMedium,
    fontSize: 12,
    marginTop: 2,
  },
});
