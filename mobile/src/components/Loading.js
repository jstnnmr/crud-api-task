import React, { useEffect, useRef } from 'react';
import { View, Animated, StyleSheet, Easing } from 'react-native';
import { useTheme } from '../context/ThemeContext';

export default function Loading({ fullScreen = true }) {
  const { colors } = useTheme();
  const pulseAnim = useRef(new Animated.Value(1)).current;
  const dot1 = useRef(new Animated.Value(0)).current;
  const dot2 = useRef(new Animated.Value(0)).current;
  const dot3 = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    const pulse = Animated.loop(
      Animated.sequence([
        Animated.timing(pulseAnim, { toValue: 0.6, duration: 800, easing: Easing.inOut(Easing.ease), useNativeDriver: false }),
        Animated.timing(pulseAnim, { toValue: 1, duration: 800, easing: Easing.inOut(Easing.ease), useNativeDriver: false }),
      ])
    );

    const bounce = (anim, delay) =>
      Animated.loop(
        Animated.sequence([
          Animated.delay(delay),
          Animated.timing(anim, { toValue: 1, duration: 400, useNativeDriver: false }),
          Animated.timing(anim, { toValue: 0, duration: 400, useNativeDriver: false }),
        ])
      );

    pulse.start();
    bounce(dot1, 0).start();
    bounce(dot2, 200).start();
    bounce(dot3, 400).start();

    return () => { pulse.stop(); dot1.setValue(0); dot2.setValue(0); dot3.setValue(0); };
  }, []);

  const content = (
    <View style={styles.loader}>
      <Animated.View style={[styles.dot, { opacity: dot1.interpolate({ inputRange: [0, 1], outputRange: [0.3, 1] }), transform: [{ scale: dot1.interpolate({ inputRange: [0, 1], outputRange: [0.8, 1.2] }) }] }, { backgroundColor: colors.primary }]} />
      <Animated.View style={[styles.dot, { opacity: dot2.interpolate({ inputRange: [0, 1], outputRange: [0.3, 1] }), transform: [{ scale: dot2.interpolate({ inputRange: [0, 1], outputRange: [0.8, 1.2] }) }] }, { backgroundColor: colors.secondary }]} />
      <Animated.View style={[styles.dot, { opacity: dot3.interpolate({ inputRange: [0, 1], outputRange: [0.3, 1] }), transform: [{ scale: dot3.interpolate({ inputRange: [0, 1], outputRange: [0.8, 1.2] }) }] }, { backgroundColor: colors.accent }]} />
    </View>
  );

  if (fullScreen) {
    return (
      <View style={[styles.container, { backgroundColor: colors.bg }]}>
        <Animated.View style={{ opacity: pulseAnim }}>
          {content}
        </Animated.View>
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
    width: 14,
    height: 14,
    borderRadius: 7,
  },
});
