import React, { useRef, useState } from 'react';
import { View, TextInput, Text, StyleSheet, Animated } from 'react-native';
import { useTheme } from '../context/ThemeContext';
import { borderRadius, spacing, fonts, shadows } from '../theme/colors';

export default function Input({
  label,
  value,
  onChangeText,
  placeholder,
  secureTextEntry,
  multiline,
  numberOfLines,
  icon,
  error,
  keyboardType,
  autoCapitalize,
  style,
  textContentType,
  autoComplete,
}) {
  const { colors } = useTheme();
  const [focused, setFocused] = useState(false);
  const borderAnim = useRef(new Animated.Value(0)).current;

  const handleFocus = () => {
    setFocused(true);
    Animated.timing(borderAnim, { toValue: 1, duration: 160, useNativeDriver: false }).start();
  };

  const handleBlur = () => {
    setFocused(false);
    Animated.timing(borderAnim, { toValue: 0, duration: 160, useNativeDriver: false }).start();
  };

  const borderColor = borderAnim.interpolate({
    inputRange: [0, 1],
    outputRange: [error ? colors.danger : colors.border, error ? colors.danger : colors.primary],
  });

  return (
    <View style={[styles.container, style]}>
      {label && (
        <Text style={[styles.label, { color: error ? colors.danger : focused ? colors.primary : colors.textSecondary }]}>
          {label}
        </Text>
      )}
      <Animated.View
        style={[
          styles.inputWrapper,
          {
            backgroundColor: colors.bgInput,
            borderColor,
          },
          focused && { shadowColor: colors.primary, shadowOffset: { width: 0, height: 0 }, shadowOpacity: 0.18, shadowRadius: 8, elevation: 2 },
          multiline && { minHeight: 104 },
        ]}
      >
        {icon && <View style={styles.icon}>{icon}</View>}
        <TextInput
          value={value}
          onChangeText={onChangeText}
          placeholder={placeholder}
          placeholderTextColor={colors.textMuted}
          secureTextEntry={secureTextEntry}
          multiline={multiline}
          numberOfLines={numberOfLines}
          keyboardType={keyboardType}
          autoCapitalize={autoCapitalize}
          textContentType={textContentType}
          autoComplete={autoComplete}
          onFocus={handleFocus}
          onBlur={handleBlur}
          style={[
            styles.input,
            { color: colors.text },
            multiline && { textAlignVertical: 'top', paddingTop: 12 },
          ]}
        />
      </Animated.View>
      {error && (
        <Text style={[styles.error, { color: colors.danger }]}>{error}</Text>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    marginBottom: spacing.md,
  },
  label: {
    fontFamily: fonts.uiMedium,
    fontSize: 13,
    marginBottom: 6,
    marginLeft: 2,
  },
  inputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: borderRadius.md,
    borderWidth: 1.5,
    minHeight: 52,
    paddingHorizontal: 14,
  },
  input: {
    flex: 1,
    fontFamily: fonts.uiRegular,
    fontSize: 15,
    paddingVertical: 13,
  },
  icon: {
    marginRight: 10,
  },
  error: {
    fontFamily: fonts.uiRegular,
    fontSize: 12,
    marginTop: 4,
    marginLeft: 2,
  },
});
