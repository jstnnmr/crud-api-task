import React, { useState, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  RefreshControl,
  TouchableOpacity,
  Alert,
} from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { useToast } from '../../context/ToastContext';
import { spacing, borderRadius, typography } from '../../theme/colors';
import { subjectsApi } from '../../api/subjects';
import { tasksApi } from '../../api/tasks';
import TaskItem from '../../components/TaskItem';
import Card from '../../components/Card';
import Loading from '../../components/Loading';
import EmptyState from '../../components/EmptyState';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';

export default function SubjectDetailScreen({ route, navigation }) {
  const { subjectId, subjectName } = route.params;
  const { colors } = useTheme();
  const { showToast } = useToast();
  const [subject, setSubject] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async () => {
    try {
      const res = await subjectsApi.show(subjectId);
      const data = res.data.data || res.data.subject || res.data;
      setSubject(data);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  }, [subjectId]);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  const onRefresh = async () => {
    setRefreshing(true);
    await load();
    setRefreshing(false);
  };

  const handleComplete = async (task) => {
    try {
      const res = await tasksApi.complete(task.id);
      const pts = res.data?.points_earned || 0;
      showToast(pts > 0 ? `Task completed! +${pts}pts` : 'Task completed!', 'success');
      load();
    } catch (e) {
      showToast('Failed to complete task', 'error');
    }
  };

  const handleDeleteTask = (task) => {
    Alert.alert('Delete Task', `Delete "${task.title}"?`, [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Delete',
        style: 'destructive',
        onPress: async () => {
          try {
            await tasksApi.destroy(task.id);
            load();
          } catch (e) {
            alert('Failed to delete');
          }
        },
      },
    ]);
  };

  if (loading) return <Loading />;
  if (!subject) return <Loading />;

  const tasks = subject.tasks || [];

  return (
    <LinearGradient
      colors={['#0f0c29', '#1a1638', '#0f0c29']}
      style={styles.container}
    >
      <View
        style={[
          styles.header,
          { backgroundColor: subject.color || colors.primary },
        ]}
      >
        <TouchableOpacity
          onPress={() => navigation.goBack()}
          style={styles.back}
        >
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.title}>{subjectName || subject.name}</Text>
        <Text style={styles.subtitle}>
          {tasks.length} task{tasks.length !== 1 ? 's' : ''}
        </Text>
      </View>

      <ScrollView
        contentContainerStyle={styles.scroll}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />
        }
      >
        {tasks.length === 0 ? (
          <Card style={{ margin: spacing.md }}>
            <EmptyState
              icon="document-text-outline"
              title="No Tasks"
              message="Add a task to this subject"
            />
          </Card>
        ) : (
          <View style={styles.taskList}>
            {tasks.map((task) => (
              <View key={task.id} style={styles.taskWrap}>
                <TaskItem
                  task={task}
                  onPress={(t) =>
                    navigation.navigate('TaskDetail', { taskId: t.id })
                  }
                  onComplete={handleComplete}
                />
                <TouchableOpacity
                  onPress={() => handleDeleteTask(task)}
                  style={styles.deleteBtn}
                >
                  <Ionicons name="trash-outline" size={16} color={colors.danger} />
                </TouchableOpacity>
              </View>
            ))}
          </View>
        )}
      </ScrollView>

      <TouchableOpacity
        style={[styles.fab, { backgroundColor: colors.primary }]}
        onPress={() =>
          navigation.navigate('TaskForm', { subjectId, subjectName })
        }
        activeOpacity={0.8}
      >
        <Ionicons name="add" size={28} color="#fff" />
      </TouchableOpacity>
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  header: {
    padding: spacing.lg,
    paddingTop: 60,
    paddingBottom: 30,
  },
  back: {
    marginBottom: 12,
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.15)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: {
    fontSize: 26,
    fontWeight: '700',
    color: '#fff',

  },
  subtitle: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.7)',
    marginTop: 2,
  },
  scroll: { paddingBottom: 80 },
  taskList: {
    padding: spacing.md,
  },
  taskWrap: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  deleteBtn: {
    padding: 8,
    marginLeft: 4,
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
