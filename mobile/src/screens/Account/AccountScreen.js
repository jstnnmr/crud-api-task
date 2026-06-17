import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
} from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { useAuth } from '../../context/AuthContext';
import { spacing, borderRadius, typography } from '../../theme/colors';
import Card from '../../components/Card';
import Button from '../../components/Button';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';

const menuItems = [
  { icon: 'person-outline', label: 'Edit Profile', screen: 'EditProfile' },
  { icon: 'lock-closed-outline', label: 'Change Password', screen: 'ChangePassword' },
  { icon: 'bar-chart-outline', label: 'Productivity Stats', screen: 'Productivity' },
  { icon: 'sparkles-outline', label: 'AI Assistant', screen: 'AITab' },
];

export default function AccountScreen({ navigation }) {
  const { colors, isDark, toggleTheme } = useTheme();
  const { user, logout } = useAuth();

  const handleLogout = () => {
    logout();
  };

  return (
    <LinearGradient
      colors={['#0f0c29', '#1a1638', '#0f0c29']}
      style={styles.container}
    >
      <ScrollView contentContainerStyle={styles.scroll}>
        <LinearGradient colors={[colors.primary, colors.primaryDark]} style={styles.header}>
          <View style={[styles.avatar, { backgroundColor: colors.bgLight }]}>
            <Text style={[styles.avatarText, { color: colors.primary }]}>
              {user?.name?.[0]?.toUpperCase() || 'U'}
            </Text>
          </View>
          <Text style={styles.name}>{user?.name || 'User'}</Text>
          <Text style={styles.email}>{user?.email || ''}</Text>
        </LinearGradient>

        <View style={styles.menu}>
          {menuItems.map((item) => (
            <Card key={item.screen} onPress={() => navigation.navigate(item.screen)}>
              <View style={styles.menuRow}>
                <View style={[styles.menuIcon, { backgroundColor: colors.primary + '15' }]}>
                  <Ionicons name={item.icon} size={20} color={colors.primary} />
                </View>
                <Text style={[styles.menuLabel, { color: colors.text }]}>{item.label}</Text>
                <Ionicons name="chevron-forward" size={18} color={colors.textMuted} />
              </View>
            </Card>
          ))}

          <Card>
            <TouchableOpacity onPress={toggleTheme} style={styles.menuRow}>
              <View style={[styles.menuIcon, { backgroundColor: colors.warning + '15' }]}>
                <Ionicons name={isDark ? 'sunny-outline' : 'moon-outline'} size={20} color={colors.warning} />
              </View>
              <Text style={[styles.menuLabel, { color: colors.text }]}>
                {isDark ? 'Light Mode' : 'Dark Mode'}
              </Text>
              <View style={[styles.toggle, { backgroundColor: isDark ? colors.primary : colors.textMuted }]}>
                <View style={[styles.toggleDot, { alignSelf: isDark ? 'flex-end' : 'flex-start' }]} />
              </View>
            </TouchableOpacity>
          </Card>

          <Button
            title="Sign Out"
            onPress={handleLogout}
            variant="danger"
            size="lg"
            style={{ marginTop: 12 }}
          />
        </View>
      </ScrollView>
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  scroll: { paddingBottom: 20 },
  header: {
    alignItems: 'center',
    padding: spacing.xl,
    paddingTop: 80,
    paddingBottom: 40,
  },
  avatar: {
    width: 80, height: 80, borderRadius: 40,
    alignItems: 'center', justifyContent: 'center',
    marginBottom: 12,
  },
  avatarText: { fontSize: 32, fontWeight: '700' },
  name: { fontSize: 24, fontWeight: '700', color: '#fff' },
  email: { fontSize: 14, color: 'rgba(255,255,255,0.7)', marginTop: 4 },
  menu: { padding: spacing.md },
  menuRow: {
    flexDirection: 'row', alignItems: 'center',
  },
  menuIcon: {
    width: 40, height: 40, borderRadius: 12,
    alignItems: 'center', justifyContent: 'center', marginRight: 12,
  },
  menuLabel: { flex: 1, fontSize: 15, fontWeight: '500' },
  toggle: {
    width: 48, height: 28, borderRadius: 14,
    padding: 3, justifyContent: 'center',
  },
  toggleDot: {
    width: 22, height: 22, borderRadius: 11, backgroundColor: '#fff',
  },
});
