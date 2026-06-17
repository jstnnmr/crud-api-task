import React, { useState, useRef } from 'react';
import { View, Text, StyleSheet, TextInput, TouchableOpacity } from 'react-native';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import { spacing, borderRadius } from '../../theme/colors';
import Button from '../../components/Button';
import { authApi } from '../../api/auth';

export default function VerifyEmailScreen() {
  const { verifyEmail, user } = useAuth();
  const { colors } = useTheme();
  const [code, setCode] = useState(['', '', '', '', '', '']);
  const [loading, setLoading] = useState(false);
  const [resending, setResending] = useState(false);
  const inputRefs = useRef([]);

  const handleVerify = async () => {
    const full = code.join('');
    if (full.length !== 6) return;
    setLoading(true);
    try {
      await verifyEmail(full);
    } catch (e) {
      alert(e.response?.data?.message || 'Invalid code');
    } finally {
      setLoading(false);
    }
  };

  const resendCode = async () => {
    setResending(true);
    try {
      await authApi.resendCode(user?.email);
      alert('Verification code resent!');
    } catch (e) {
      alert(e.response?.data?.message || 'Failed to resend');
    } finally {
      setResending(false);
    }
  };

  const handleChange = (text, index) => {
    const newCode = [...code];
    newCode[index] = text;
    setCode(newCode);
    if (text && index < 5) {
      inputRefs.current[index + 1]?.focus();
    }
  };

  const handleKeyPress = (e, index) => {
    if (e.nativeEvent.key === 'Backspace' && !code[index] && index > 0) {
      inputRefs.current[index - 1]?.focus();
    }
  };

  return (
    <View style={[styles.container, { backgroundColor: colors.bg }]}>
      <View style={styles.content}>
        <Text style={[styles.title, { color: colors.text }]}>Verify Email</Text>
        <Text style={[styles.subtitle, { color: colors.textSecondary }]}>
          Enter the 6-digit code sent to {user?.email || 'your email'}
        </Text>

        <View style={styles.codeRow}>
          {code.map((digit, i) => (
            <TextInput
              key={i}
              ref={(ref) => (inputRefs.current[i] = ref)}
              value={digit}
              onChangeText={(t) => handleChange(t, i)}
              onKeyPress={(e) => handleKeyPress(e, i)}
              style={[
                styles.codeInput,
                {
                  backgroundColor: colors.bgInput,
                  borderColor: colors.border,
                  color: colors.text,
                },
                digit && { borderColor: colors.primary },
              ]}
              keyboardType="number-pad"
              maxLength={1}
              selectTextOnFocus
            />
          ))}
        </View>

        <Button
          title="Verify"
          onPress={handleVerify}
          loading={loading}
          size="lg"
          disabled={code.join('').length !== 6}
        />

        <TouchableOpacity
          onPress={resendCode}
          disabled={resending}
          style={styles.resend}
        >
          <Text style={{ color: colors.primary, fontSize: 14 }}>
            {resending ? 'Resending...' : "Didn't get code? Resend"}
          </Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  content: {
    flex: 1,
    justifyContent: 'center',
    padding: spacing.lg,
  },
  title: {
    fontSize: 28,
    fontWeight: '700',
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 15,
    textAlign: 'center',
    marginTop: 8,
    marginBottom: 32,
  },
  codeRow: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: 10,
    marginBottom: 32,
  },
  codeInput: {
    width: 48,
    height: 56,
    borderRadius: borderRadius.md,
    borderWidth: 2,
    textAlign: 'center',
    fontSize: 22,
    fontWeight: '700',
  },
  resend: {
    alignItems: 'center',
    marginTop: 20,
  },
});
