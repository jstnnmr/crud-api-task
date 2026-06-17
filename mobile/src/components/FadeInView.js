import React, { useEffect, useRef } from 'react';
import { Animated } from 'react-native';

export default function FadeInView({ children, delay = 0, duration = 400, style }) {
  const anim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    Animated.timing(anim, {
      toValue: 1,
      duration,
      delay,
      useNativeDriver: false,
    }).start();
  }, []);

  return (
    <Animated.View
      style={[
        {
          opacity: anim,
          transform: [{
            translateY: anim.interpolate({
              inputRange: [0, 1],
              outputRange: [20, 0],
            }),
          }],
        },
        style,
      ]}
    >
      {children}
    </Animated.View>
  );
}
