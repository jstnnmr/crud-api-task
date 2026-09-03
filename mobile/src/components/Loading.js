import React, { useEffect, useRef } from 'react';
import { View, Animated, StyleSheet, Easing } from 'react-native';
import { useTheme } from '../context/ThemeContext';

export default function Loading({ fullScreen = true }) {
  const { colors } = useTheme();
  const dot1 = useRef(new Animated.Value(0)).current;
  const dot2 = useRef(new Animated.Value(0)).current;
  const dot3 = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    const fade = (anim, delay) =>
      Animated.loop(
        Animated.sequence([
          Animated.delay(delay),
          Animated.timing(anim, {
            toValue: 1,
            duration: 480,
            easing: Easing.inOut(Easing.ease),
            useNativeDriver: true,
          }),
          Animated.timing(anim, {
            toValue: 0,
            duration: 480,
            easing: Easing.inOut(Easing.ease),
            useNativeDriver: true,
          }),
        ])
      );

    fade(dot1, 0).start();
    fade(dot2, 220).start();
    fade(dot3, 440).start();

    return () => {
      dot1.stopAnimation();
      dot2.stopAnimation();
      dot3.stopAnimation();
    };
  }, []);

  const content = (
    <View style={styles.loader}>
      <Animated.View style={[styles.dot, { opacity: dot1.interpolate({ inputRange: [0, 1], outputRange: [0.25, 1] }) }, { backgroundColor: colors.primary }]} />
      <Animated.View style={[styles.dot, { opacity: dot2.interpolate({ inputRange: [0, 1], outputRange: [0.25, 1] }) }, { backgroundColor: colors.primary }]} />
      <Animated.View style={[styles.dot, { opacity: dot3.interpolate({ inputRange: [0, 1], outputRange: [0.25, 1] }) }, { backgroundColor: colors.primary }]} />
    </View>
  );

  if (fullScreen) {
    return (
      <View style={[styles.container, { backgroundColor: colors.bg }]}>
        {content}
      </View>
    );
  }

  return <View style={styles.inline}>{content}</View>;
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  inline: {
    padding: 20,
    alignItems: 'center',
  },
  loader: {
    flexDirection: 'row',
    gap: 8,
  },
  dot: {
    width: 9,
    height: 9,
    borderRadius: 4.5,
  },
});
