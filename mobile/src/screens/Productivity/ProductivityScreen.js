import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, ScrollView, RefreshControl } from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { spacing, borderRadius, typography } from '../../theme/colors';
import { authApi } from '../../api/auth';
import Card from '../../components/Card';
import Loading from '../../components/Loading';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';

export default function ProductivityScreen({ navigation }) {
  const { colors } = useTheme();
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    try {
      const res = await authApi.getStats();
      setStats(res.data.data || res.data);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  }, []);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  if (loading) return <Loading />;

  const completed = stats?.tasks_completed || 0;
  const pending = stats?.tasks_pending || 0;
  const total = completed + pending;
  const points = stats?.total_points || 0;
  const pct = total > 0 ? Math.round((completed / total) * 100) : 0;

  return (
    <View style={[styles.container, { backgroundColor: colors.bg }]}>
      <LinearGradient colors={[colors.primary, colors.primaryDark]} style={styles.header}>
        <Text style={styles.title}>Productivity</Text>
        <Text style={styles.subtitle}>Your progress at a glance</Text>
      </LinearGradient>

      <ScrollView contentContainerStyle={styles.scroll}>
        <Card>
          <Text style={[typography.h3, { color: colors.text, marginBottom: 12 }]}>Completion Rate</Text>
          <View style={[styles.progressBar, { backgroundColor: colors.bgInput }]}>
            <View style={[styles.progressFill, { width: `${pct}%`, backgroundColor: colors.success }]} />
          </View>
          <Text style={{ color: colors.textSecondary, fontSize: 14, marginTop: 8 }}>
            {completed} of {total} tasks completed ({pct}%)
          </Text>
        </Card>

        <View style={styles.grid}>
          <Card style={styles.gridCard}>
            <Ionicons name="checkmark-circle" size={32} color={colors.success} />
            <Text style={[styles.statValue, { color: colors.text }]}>{completed}</Text>
            <Text style={styles.statLabel}>Completed</Text>
          </Card>
          <Card style={styles.gridCard}>
            <Ionicons name="time" size={32} color={colors.warning} />
            <Text style={[styles.statValue, { color: colors.text }]}>{pending}</Text>
            <Text style={styles.statLabel}>Pending</Text>
          </Card>
          <Card style={styles.gridCard}>
            <Ionicons name="star" size={32} color={colors.accent} />
            <Text style={[styles.statValue, { color: colors.text }]}>{points}</Text>
            <Text style={styles.statLabel}>Points</Text>
          </Card>
          <Card style={styles.gridCard}>
            <Ionicons name="flame" size={32} color={colors.danger} />
            <Text style={[styles.statValue, { color: colors.text }]}>{stats?.streak || 0}</Text>
            <Text style={styles.statLabel}>Day Streak</Text>
          </Card>
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  header: { padding: spacing.lg, paddingTop: 60, paddingBottom: 30 },
  title: { fontSize: 28, fontWeight: '700', color: '#fff' },
  subtitle: { fontSize: 15, color: 'rgba(255,255,255,0.7)', marginTop: 2 },
  scroll: { padding: spacing.md, paddingBottom: 20 },
  progressBar: {
    height: 12, borderRadius: 6, overflow: 'hidden',
  },
  progressFill: {
    height: '100%', borderRadius: 6,
  },
  grid: {
    flexDirection: 'row', flexWrap: 'wrap', gap: 12,
  },
  gridCard: {
    width: '47%', alignItems: 'center',
  },
  statValue: {
    fontSize: 28, fontWeight: '700', marginTop: 8,
  },
  statLabel: {
    fontSize: 13, color: '#a0a0c0', marginTop: 2,
  },
});
