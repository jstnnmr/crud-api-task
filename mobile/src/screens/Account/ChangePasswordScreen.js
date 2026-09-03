import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView } from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { spacing, borderRadius } from '../../theme/colors';
import { authApi } from '../../api/auth';
import Input from '../../components/Input';
import Button from '../../components/Button';
import { Ionicons } from '@expo/vector-icons';

export default function ChangePasswordScreen({ navigation }) {
  const { colors } = useTheme();
  const [email, setEmail] = useState('');
  const [codeSent, setCodeSent] = useState(false);
  const [code, setCode] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSendCode = async () => {
    if (!email.trim()) return;
    setLoading(true);
    try {
      await authApi.updatePassword({ email: email.trim() });
      setCodeSent(true);
    } catch (e) {
      alert(e.response?.data?.message || 'Failed');
    } finally {
      setLoading(false);
    }
  };

  const handleConfirm = async () => {
    if (!code || !password) return;
    setLoading(true);
    try {
      await authApi.confirmPasswordChange({ code, password, password_confirmation: passwordConfirmation });
      alert('Password changed!');
      navigation.goBack();
    } catch (e) {
      alert(e.response?.data?.message || 'Failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={[styles.container, { backgroundColor: colors.bg }]}>
      <View style={[styles.header, { backgroundColor: colors.primary }]}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.back}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.title}>Change Password</Text>
      </View>

      <ScrollView contentContainerStyle={styles.form}>
        {!codeSent ? (
          <>
            <Input label="Email" value={email} onChangeText={setEmail} keyboardType="email-address" autoCapitalize="none" placeholder="Your email" />
            <Button title="Send Code" onPress={handleSendCode} loading={loading} size="lg" />
          </>
        ) : (
          <>
            <Input label="Verification Code" value={code} onChangeText={setCode} placeholder="6-digit code" keyboardType="number-pad" />
            <Input label="New Password" value={password} onChangeText={setPassword} secureTextEntry placeholder="Min 8 characters" />
            <Input label="Confirm Password" value={passwordConfirmation} onChangeText={setPasswordConfirmation} secureTextEntry placeholder="Repeat password" />
            <Button title="Change Password" onPress={handleConfirm} loading={loading} size="lg" />
          </>
        )}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  header: { padding: spacing.lg, paddingTop: 60, paddingBottom: 30 },
  back: { marginBottom: 12, width: 40, height: 40, borderRadius: 20, backgroundColor: 'rgba(255,255,255,0.15)', alignItems: 'center', justifyContent: 'center' },
  title: { fontSize: 26, fontWeight: '700', color: '#fff' },
  form: { padding: spacing.lg },
});
