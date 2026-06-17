import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { borderRadius } from '../theme/colors';

export default function Badge({ label, color, textColor, size = 'sm' }) {
  const padH = size === 'sm' ? 12 : 16;
  const padV = size === 'sm' ? 4 : 7;
  const fontSize = size === 'sm' ? 10 : 12;

  return (
    <View
      style={[
        styles.badge,
        {
          backgroundColor: color + '18',
          borderColor: color + '30',
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
    fontWeight: '700',
    textTransform: 'capitalize',
    letterSpacing: 0.3,
  },
});
