import React, { useState, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  RefreshControl,
  TouchableOpacity,
} from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { spacing, borderRadius, typography } from '../../theme/colors';
import { subjectsApi } from '../../api/subjects';
import Card from '../../components/Card';
import Loading from '../../components/Loading';
import EmptyState from '../../components/EmptyState';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';

export default function SubjectsScreen({ navigation }) {
  const { colors } = useTheme();
  const [subjects, setSubjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');

  const loadSubjects = useCallback(async () => {
    setError('');
    try {
      const res = await subjectsApi.list();
      const raw = res.data.data || res.data.subjects || res.data || [];
      const data = Array.isArray(raw) ? raw : raw?.data || [];
      setSubjects(data);
    } catch (e) {
      const msg = e.response?.data?.message || e.message || 'Failed to load subjects';
      setError(msg);
      console.error(e);
    } finally {
      setLoading(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadSubjects();
    }, [loadSubjects])
  );

  const onRefresh = async () => {
    setRefreshing(true);
    await loadSubjects();
    setRefreshing(false);
  };

  if (loading) return <Loading />;

  return (
    <LinearGradient
      colors={['#0f0c29', '#1a1638', '#0f0c29']}
      style={styles.container}
    >
      <ScrollView
        contentContainerStyle={styles.scroll}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={colors.primary}
          />
        }
      >
        <LinearGradient
          colors={[colors.primary, colors.primaryDark]}
          style={styles.header}
        >
          <Text style={styles.title}>Subjects</Text>
          <Text style={styles.subtitle}>
            {subjects.length} subject{subjects.length !== 1 ? 's' : ''}
          </Text>
        </LinearGradient>

        <View style={styles.list}>
          {error ? (
            <View style={[styles.errorBox, { backgroundColor: colors.danger + '20' }]}>
              <Text style={{ color: colors.danger, fontSize: 13 }}>{error}</Text>
            </View>
          ) : subjects.length === 0 ? (
            <Card>
              <EmptyState
                icon="folder-open-outline"
                title="No Subjects Yet"
                message="Create your first subject to organize tasks"
              />
            </Card>
          ) : (
            subjects.map((subject) => {
              const taskCount =
                subject.tasks_count ?? subject.tasks?.length ?? 0;
              return (
                <Card
                  key={subject.id}
                  onPress={() =>
                    navigation.navigate('SubjectDetail', {
                      subjectId: subject.id,
                      subjectName: subject.name,
                    })
                  }
                >
                  <View style={styles.subjectRow}>
                    <View
                      style={[
                        styles.colorDot,
                        { backgroundColor: subject.color || colors.primary },
                      ]}
                    />
                    <View style={{ flex: 1 }}>
                      <Text
                        style={[typography.h3, { color: colors.text }]}
                      >
                        {subject.name}
                      </Text>
                      <Text style={{ color: colors.textMuted, fontSize: 13 }}>
                        {taskCount} task{taskCount !== 1 ? 's' : ''}
                      </Text>
                    </View>
                    <Ionicons
                      name="chevron-forward"
                      size={20}
                      color={colors.textMuted}
                    />
                  </View>
                </Card>
              );
            })
          )}
        </View>
      </ScrollView>

      <TouchableOpacity
        style={[styles.fab, { backgroundColor: colors.primary }]}
        onPress={() => navigation.navigate('SubjectForm', {})}
        activeOpacity={0.8}
      >
        <Ionicons name="add" size={28} color="#fff" />
      </TouchableOpacity>
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  scroll: { paddingBottom: 80 },
  header: {
    padding: spacing.lg,
    paddingTop: 60,
    paddingBottom: 30,
  },
  title: {
    fontSize: 28,
    fontWeight: '700',
    color: '#fff',

  },
  subtitle: {
    fontSize: 15,
    color: 'rgba(255,255,255,0.7)',
    marginTop: 2,
  },
  list: {
    padding: spacing.md,
  },
  subjectRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  colorDot: {
    width: 16,
    height: 16,
    borderRadius: 8,
  },
  errorBox: {
    padding: 12,
    borderRadius: borderRadius.sm,
    marginBottom: 16,
  },
  fab: {
    position: 'absolute',
    bottom: 20,
    right: 20,
    width: 56,
    height: 56,
    borderRadius: 28,
    alignItems: 'center',
    justifyContent: 'center',
    elevation: 8,
    shadowColor: '#8e7dff',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
  },
});
