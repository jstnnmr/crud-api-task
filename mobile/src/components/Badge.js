import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { useTheme } from '../context/ThemeContext';
import { borderRadius, fonts } from '../theme/colors';

export default function Badge({ label, color, textColor, size = 'sm' }) {
  const { isDark } = useTheme();
  const padH = size === 'sm' ? 12 : 16;
  const padV = size === 'sm' ? 4 : 7;
  const fontSize = size === 'sm' ? 11 : 12;
  const fill = isDark ? '30' : '16';
  const border = isDark ? '55' : '2E';

  return (
    <View
      style={[
        styles.badge,
        {
          backgroundColor: (color || '#888') + fill,
          borderColor: (color || '#888') + border,
          paddingHorizontal: padH,
          paddingVertical: padV,
        },
      ]}
    >
      <Text
        style={[
          styles.text,
          { color: textColor || color, fontSize },
        ]}
        numberOfLines={1}
      >
        {label}
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: {
    borderRadius: borderRadius.full,
    alignSelf: 'flex-start',
    borderWidth: 1,
  },
  text: {
    fontFamily: fonts.uiSemiBold,
    textTransform: 'capitalize',
    letterSpacing: 0.2,
  },
});
