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
import { useAuth } from '../../context/AuthContext';
import { useToast } from '../../context/ToastContext';
import { spacing, borderRadius, typography } from '../../theme/colors';
import { authApi } from '../../api/auth';
import { tasksApi } from '../../api/tasks';
import StatCard from '../../components/StatCard';
import TaskItem from '../../components/TaskItem';
import Card from '../../components/Card';
import Loading from '../../components/Loading';
import MiniCalendar from '../../components/MiniCalendar';
import EmptyState from '../../components/EmptyState';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';

export default function DashboardScreen({ navigation }) {
  const { colors } = useTheme();
  const { user } = useAuth();
  const { showToast } = useToast();
  const [stats, setStats] = useState(null);
  const [allTasks, setAllTasks] = useState([]);
  const [myTasks, setMyTasks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = useCallback(async () => {
    try {
      const [statsRes, tasksRes] = await Promise.all([
        authApi.getStats(),
        tasksApi.myTasks(),
      ]);
      setStats(statsRes.data.data || statsRes.data);
      const tasks = tasksRes.data.data || tasksRes.data.tasks || tasksRes.data || [];
      const taskList = Array.isArray(tasks) ? tasks : [];
      setAllTasks(taskList);
      setMyTasks(taskList.filter((t) => t.status !== 'completed').slice(0, 10));
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadData();
    }, [loadData])
  );

  const onRefresh = async () => {
    setRefreshing(true);
    await loadData();
    setRefreshing(false);
  };

  const handleComplete = async (task) => {
    try {
      const res = await tasksApi.complete(task.id);
      const pts = res.data?.points_earned || 0;
      showToast(pts > 0 ? `Task completed! +${pts}pts` : 'Task completed!', 'success');
      loadData();
    } catch (e) {
      showToast('Failed to complete task', 'error');
    }
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
          <View style={styles.headerTop}>
            <View>
              <Text style={styles.greeting}>
                Hello, {user?.name?.split(' ')[0] || 'there'}!
              </Text>
              <Text style={styles.subtitle}>Here's your day</Text>
            </View>
            <TouchableOpacity
              onPress={() => navigation.navigate('AccountTab')}
              style={[styles.avatar, { backgroundColor: colors.bgLight }]}
            >
              <Text style={[styles.avatarText, { color: colors.primary }]}>
                {user?.name?.[0]?.toUpperCase() || 'U'}
              </Text>
            </TouchableOpacity>
          </View>
        </LinearGradient>

        {stats && (
          <View style={styles.statsRow}>
            <StatCard
              icon="layers-outline"
              label="Subjects"
              value={stats.subjects_count ?? stats.total_subjects ?? 0}
              color={colors.primary}
            />
            <StatCard
              icon="checkmark-circle-outline"
              label="Completed"
              value={stats.tasks_completed ?? 0}
              color={colors.success}
            />
            <StatCard
              icon="time-outline"
              label="Pending"
              value={stats.tasks_pending ?? 0}
              color={colors.warning}
            />
            <StatCard
              icon="star-outline"
              label="Points"
              value={stats.total_points ?? 0}
              color={colors.accent}
            />
          </View>
        )}

        <View style={{ marginTop: spacing.md }}>
          <MiniCalendar
            tasks={allTasks}
            onDatePress={(date) =>
              navigation.navigate('TaskTab', {
                screen: 'TasksHome',
                params: { dueDate: date },
              })
            }
          />
        </View>

        <View style={[styles.section, { marginTop: spacing.md }]}>
          <View style={styles.sectionHeader}>
            <Text style={[typography.h3, { color: colors.text }]}>
              Today's Tasks
            </Text>
            <TouchableOpacity onPress={() => navigation.navigate('SubjectsTab')}>
              <Text style={{ color: colors.primary, fontSize: 14 }}>
                See All
              </Text>
            </TouchableOpacity>
          </View>

          {myTasks.length === 0 ? (
            <Card>
              <EmptyState
                icon="checkmark-done-outline"
                title="All clear!"
                message="You have no pending tasks"
              />
            </Card>
          ) : (
            myTasks.map((task) => (
              <TaskItem
                key={task.id}
                task={task}
                onPress={(t) =>
                  navigation.navigate('TaskDetail', { taskId: t.id })
                }
                onComplete={handleComplete}
                compact
              />
            ))
          )}
        </View>

        <LinearGradient
          colors={['#6366f130', '#a855f710']}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={[
            styles.aiCard,
            {
              borderColor: colors.primary + '40',
              marginHorizontal: spacing.md,
              borderRadius: borderRadius.lg,
              borderWidth: 1,
              overflow: 'hidden',
            },
          ]}
        >
          <TouchableOpacity
            onPress={() => navigation.navigate('AITab')}
            activeOpacity={0.8}
            style={{ padding: spacing.md }}
          >
            <View style={styles.aiRow}>
              <View
                style={[
                  styles.aiIcon,
                  {
                    backgroundColor: colors.primary + '25',
                    borderWidth: 1,
                    borderColor: colors.primary + '30',
                  },
                ]}
              >
                <Ionicons name="sparkles" size={22} color={colors.primary} />
              </View>
              <View style={{ flex: 1 }}>
                <Text style={[typography.h3, { color: colors.text }]}>
                  AI Assistant
                </Text>
                <Text style={{ color: colors.textMuted, fontSize: 13 }}>
                  Ask AI to help with your tasks
                </Text>
              </View>
              <Ionicons
                name="chevron-forward"
                size={20}
                color={colors.textMuted}
              />
            </View>
          </TouchableOpacity>
        </LinearGradient>
      </ScrollView>
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  scroll: { paddingBottom: 20 },
  header: {
    padding: spacing.lg,
    paddingTop: 60,
    paddingBottom: 30,
  },
  headerTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  greeting: {
    fontSize: 28,
    fontWeight: '700',
    color: '#fff',

  },
  subtitle: {
    fontSize: 15,
    color: 'rgba(255,255,255,0.7)',
    marginTop: 2,
  },
  avatar: {
    width: 44,
    height: 44,
    borderRadius: 22,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: {
    fontSize: 18,
    fontWeight: '700',
  },
  statsRow: {
    flexDirection: 'row',
    paddingHorizontal: spacing.md,
    marginTop: spacing.md,
  },
  section: {
    padding: spacing.md,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  aiCard: {
    marginHorizontal: spacing.md,
    borderWidth: 1,
  },
  aiRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  aiIcon: {
    width: 48,
    height: 48,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
});
