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
import { useToast } from '../../context/ToastContext';
import { spacing, borderRadius, typography } from '../../theme/colors';
import { tasksApi } from '../../api/tasks';
import TaskItem from '../../components/TaskItem';
import Loading from '../../components/Loading';
import EmptyState from '../../components/EmptyState';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';
import FadeInView from '../../components/FadeInView';

const STATUS_FILTERS = ['all', 'pending', 'in_progress', 'completed'];

export default function TasksScreen({ navigation }) {
  const { colors } = useTheme();
  const { showToast } = useToast();
  const [tasks, setTasks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');

  const loadTasks = useCallback(async () => {
    setError('');
    try {
      const res = await tasksApi.myTasks();
      const raw = res.data.data || res.data.tasks || res.data || [];
      const data = Array.isArray(raw) ? raw : raw?.data || [];
      setTasks(data);
    } catch (e) {
      const msg = e.response?.data?.message || e.message || 'Failed to load tasks';
      setError(msg);
    } finally {
      setLoading(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadTasks();
    }, [loadTasks])
  );

  const onRefresh = async () => {
    setRefreshing(true);
    await loadTasks();
    setRefreshing(false);
  };

  const handleComplete = async (task) => {
    try {
      const res = await tasksApi.complete(task.id);
      const pts = res.data?.points_earned || 0;
      showToast(
        pts > 0 ? `Task completed! +${pts}pts` : 'Task completed!',
        'success'
      );
      loadTasks();
    } catch (e) {
      showToast('Failed to complete task', 'error');
    }
  };

  if (loading) return <Loading />;

  const filteredTasks = statusFilter === 'all'
    ? tasks
    : tasks.filter((t) => t.status === statusFilter);

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
          <Text style={styles.title}>My Tasks</Text>
          <Text style={styles.subtitle}>
            {tasks.length} task{tasks.length !== 1 ? 's' : ''}
          </Text>
          <View style={styles.quickLinks}>
            <TouchableOpacity
              style={[styles.quickLink, { backgroundColor: 'rgba(255,255,255,0.15)' }]}
              onPress={() => navigation.navigate('SubjectForm', {})}
            >
              <Ionicons name="folder-open-outline" size={18} color="#fff" />
              <Text style={styles.quickLinkText}>Subjects</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.quickLink, { backgroundColor: 'rgba(255,255,255,0.15)' }]}
              onPress={() => navigation.navigate('TeamHome')}
            >
              <Ionicons name="people-outline" size={18} color="#fff" />
              <Text style={styles.quickLinkText}>Collab</Text>
            </TouchableOpacity>
          </View>
        </LinearGradient>

        <View style={styles.filterRow}>
          {STATUS_FILTERS.map((f) => (
            <TouchableOpacity
              key={f}
              onPress={() => setStatusFilter(f)}
              style={[
                styles.filterChip,
                {
                  backgroundColor: statusFilter === f ? colors.primary + '30' : colors.bgInput,
                  borderColor: statusFilter === f ? colors.primary : colors.border,
                },
              ]}
            >
              <Text
                style={{
                  color: statusFilter === f ? colors.primary : colors.textMuted,
                  fontSize: 12,
                  fontWeight: '600',
                  textTransform: 'capitalize',
                }}
              >
                {f === 'in_progress' ? 'In Progress' : f === 'all' ? 'All' : f.replace('_', ' ')}
              </Text>
            </TouchableOpacity>
          ))}
        </View>

        <View style={styles.list}>
          {error ? (
            <View style={[styles.errorBox, { backgroundColor: colors.danger + '20' }]}>
              <Text style={{ color: colors.danger, fontSize: 13 }}>{error}</Text>
            </View>
          ) : filteredTasks.length === 0 ? (
            <EmptyState
              icon="checkbox-outline"
              title={statusFilter === 'all' ? 'No Tasks Yet' : `No ${statusFilter.replace('_', ' ')} tasks`}
              message="Tap + to create your first task"
            />
          ) : (
            filteredTasks.map((task, i) => (
              <FadeInView key={task.id} delay={i * 60}>
                <TaskItem
                  task={task}
                  onPress={(t) =>
                    navigation.navigate('TaskDetail', { taskId: t.id })
                  }
                  onComplete={handleComplete}
                />
              </FadeInView>
            ))
          )}
        </View>
      </ScrollView>

      <TouchableOpacity
        activeOpacity={0.85}
        onPress={() => navigation.navigate('TaskForm', {})}
        style={[styles.fab, { backgroundColor: colors.primary }]}
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
  quickLinks: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 16,
  },
  quickLink: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingVertical: 8,
    paddingHorizontal: 14,
    borderRadius: borderRadius.md,
  },
  quickLinkText: {
    color: '#fff',
    fontSize: 13,
    fontWeight: '600',
  },
  filterRow: {
    flexDirection: 'row',
    gap: 8,
    paddingHorizontal: spacing.md,
    paddingTop: spacing.md,
    paddingBottom: 4,
  },
  filterChip: {
    paddingHorizontal: 14,
    paddingVertical: 7,
    borderRadius: borderRadius.full,
    borderWidth: 1,
  },
  list: {
    padding: spacing.md,
  },
  errorBox: {
    padding: 12,
    borderRadius: borderRadius.sm,
    marginBottom: 16,
  },
  fab: {
    position: 'absolute',
    right: 20,
    bottom: 24,
    width: 56,
    height: 56,
    borderRadius: 28,
    alignItems: 'center',
    justifyContent: 'center',
    elevation: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
  },
});
