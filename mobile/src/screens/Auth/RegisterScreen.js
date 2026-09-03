import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  TouchableOpacity,
} from 'react-native';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import { spacing, borderRadius, typography } from '../../theme/colors';
import Input from '../../components/Input';
import Button from '../../components/Button';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';

export default function RegisterScreen({ navigation }) {
  const { register } = useAuth();
  const { colors } = useTheme();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleRegister = async () => {
    if (!name.trim() || !email.trim() || !password) {
      setError('Please fill in all fields');
      return;
    }
    if (password !== passwordConfirmation) {
      setError('Passwords do not match');
      return;
    }
    if (password.length < 8) {
      setError('Password must be at least 8 characters');
      return;
    }
    setLoading(true);
    setError('');
    try {
      await register({ name: name.trim(), email: email.trim(), password, password_confirmation: passwordConfirmation });
    } catch (e) {
      const msg = e.response?.data?.message;
      const errs = e.response?.data?.errors;
      if (errs) {
        const first = Object.values(errs)[0];
        setError(Array.isArray(first) ? first[0] : first);
      } else {
        setError(msg || 'Registration failed');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={[styles.container, { backgroundColor: colors.bg }]}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    >
      <ScrollView
        contentContainerStyle={styles.scroll}
        keyboardShouldPersistTaps="handled"
      >
        <LinearGradient
          colors={[colors.primary, colors.primaryDark]}
          style={styles.header}
        >
          <Ionicons name="person-add" size={40} color="#fff" />
          <Text style={styles.title}>Create Account</Text>
        </LinearGradient>

        <View style={[styles.form, { backgroundColor: colors.bgLight }]}>
          {error ? (
            <View style={[styles.errorBox, { backgroundColor: colors.danger + '20' }]}>
              <Ionicons name="alert-circle" size={16} color={colors.danger} />
              <Text style={[styles.errorText, { color: colors.danger }]}>{error}</Text>
            </View>
          ) : null}

          <Input
            label="Name"
            value={name}
            onChangeText={setName}
            placeholder="Your full name"
            icon={<Ionicons name="person-outline" size={18} color={colors.textMuted} />}
          />
          <Input
            label="Email"
            value={email}
            onChangeText={setEmail}
            placeholder="your@email.com"
            keyboardType="email-address"
            autoCapitalize="none"
            icon={<Ionicons name="mail-outline" size={18} color={colors.textMuted} />}
          />
          <Input
            label="Password"
            value={password}
            onChangeText={setPassword}
            placeholder="Min 8 characters"
            secureTextEntry
            icon={<Ionicons name="lock-closed-outline" size={18} color={colors.textMuted} />}
          />
          <Input
            label="Confirm Password"
            value={passwordConfirmation}
            onChangeText={setPasswordConfirmation}
            placeholder="Repeat your password"
            secureTextEntry
            icon={<Ionicons name="lock-closed-outline" size={18} color={colors.textMuted} />}
          />

          <Button
            title="Create Account"
            onPress={handleRegister}
            loading={loading}
            size="lg"
            style={{ marginTop: 8 }}
          />

          <View style={styles.footer}>
            <Text style={{ color: colors.textMuted }}>Already have an account? </Text>
            <TouchableOpacity onPress={() => navigation.goBack()}>
              <Text style={{ color: colors.primary, fontWeight: '600' }}>Sign In</Text>
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  scroll: { flexGrow: 1 },
  header: {
    padding: spacing.xl,
    paddingTop: 80,
    alignItems: 'center',
    paddingBottom: 40,
  },
  title: {
    fontSize: 28,
    fontWeight: '700',
    color: '#fff',

    marginTop: 12,
  },
  form: {
    flex: 1,
    borderTopLeftRadius: borderRadius.xl,
    borderTopRightRadius: borderRadius.xl,
    padding: spacing.lg,
    marginTop: -20,
  },
  errorBox: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 12,
    borderRadius: borderRadius.sm,
    marginBottom: 16,
    gap: 8,
  },
  errorText: { fontSize: 13, flex: 1 },
  footer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: 24,
  },
});
