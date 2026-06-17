import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { useToast } from '../../context/ToastContext';
import { spacing, borderRadius, typography } from '../../theme/colors';
import { tasksApi } from '../../api/tasks';
import Card from '../../components/Card';
import Badge from '../../components/Badge';
import Button from '../../components/Button';
import { Ionicons } from '@expo/vector-icons';

export default function TaskDetailScreen({ route, navigation }) {
  const { colors } = useTheme();
  const { showToast } = useToast();
  const { taskId } = route.params;
  const [task, setTask] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadTask();
  }, [taskId]);

  const loadTask = async () => {
    try {
      const res = await tasksApi.show(taskId);
      const data = res.data.data || res.data.task || res.data;
      setTask(data);
    } catch (e) {
      alert('Failed to load task');
      navigation.goBack();
    } finally {
      setLoading(false);
    }
  };

  const handleComplete = async () => {
    try {
      const res = await tasksApi.complete(taskId);
      const pts = res.data?.points_earned || 0;
      showToast(
        pts > 0 ? `Task completed! +${pts}pts` : 'Task completed!',
        'success'
      );
      loadTask();
    } catch (e) {
      showToast('Failed to complete', 'error');
    }
  };

  const handleDelete = () => {
    Alert.alert('Delete Task', 'Are you sure?', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Delete',
        style: 'destructive',
        onPress: async () => {
          try {
            await tasksApi.destroy(taskId);
            showToast('Task deleted', 'info');
            navigation.goBack();
          } catch (e) {
            showToast('Failed to delete', 'error');
          }
        },
      },
    ]);
  };

  if (loading) {
    return (
      <View style={[styles.container, { backgroundColor: colors.bg, justifyContent: 'center' }]}>
        <ActivityIndicator size="large" color={colors.primary} />
      </View>
    );
  }

  if (!task) return null;

  const priorityColor = {
    low: colors.priority.low,
    medium: colors.priority.medium,
    high: colors.priority.high,
  }[task.priority];

  const statusColor = {
    pending: colors.status.pending,
    in_progress: colors.status.in_progress,
    completed: colors.status.completed,
  }[task.status];

  return (
    <View style={[styles.container, { backgroundColor: colors.bg }]}>
      <View style={[styles.header, { backgroundColor: priorityColor }]}>
        <View style={styles.headerTop}>
          <TouchableOpacity onPress={() => navigation.goBack()} style={styles.back}>
            <Ionicons name="arrow-back" size={24} color="#fff" />
          </TouchableOpacity>
          <TouchableOpacity onPress={() => navigation.navigate('TaskForm', { task })} style={styles.editBtn}>
            <Ionicons name="create-outline" size={22} color="#fff" />
          </TouchableOpacity>
        </View>
        <Text style={styles.title}>{task.title}</Text>
      </View>

      <ScrollView contentContainerStyle={styles.content}>
        {task.description ? (
          <Card>
            <Text style={[styles.sectionTitle, { color: colors.textSecondary }]}>Description</Text>
            <Text style={{ color: colors.text, fontSize: 15, lineHeight: 22 }}>{task.description}</Text>
          </Card>
        ) : null}

        <Card>
          <Text style={[styles.sectionTitle, { color: colors.textSecondary }]}>Details</Text>
          <View style={styles.detailRow}>
            <Text style={{ color: colors.textMuted, fontSize: 14 }}>Status</Text>
            <Badge label={task.status.replace('_', ' ')} color={statusColor} />
          </View>
          <View style={styles.detailRow}>
            <Text style={{ color: colors.textMuted, fontSize: 14 }}>Priority</Text>
            <Badge label={task.priority} color={priorityColor} />
          </View>
          {task.subject && (
            <View style={styles.detailRow}>
              <Text style={{ color: colors.textMuted, fontSize: 14 }}>Subject</Text>
              <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                <View style={[styles.dot, { backgroundColor: task.subject.color || colors.primary }]} />
                <Text style={{ color: colors.text, fontSize: 14 }}>{task.subject.name}</Text>
              </View>
            </View>
          )}
          {task.category && (
            <View style={styles.detailRow}>
              <Text style={{ color: colors.textMuted, fontSize: 14 }}>Category</Text>
              <Text style={{ color: colors.text, fontSize: 14 }}>{task.category.name}</Text>
            </View>
          )}
          {task.due_date && (
            <View style={styles.detailRow}>
              <Text style={{ color: colors.textMuted, fontSize: 14 }}>Due Date</Text>
              <Text style={{ color: colors.text, fontSize: 14 }}>
                {new Date(task.due_date).toLocaleDateString()}
              </Text>
            </View>
          )}
          {task.points_earned > 0 && (
            <View style={styles.detailRow}>
              <Text style={{ color: colors.textMuted, fontSize: 14 }}>Points</Text>
              <Text style={{ color: colors.accent, fontWeight: '700', fontSize: 14 }}>
                +{task.points_earned}
              </Text>
            </View>
          )}
        </Card>

        <View style={styles.actions}>
          {task.status !== 'completed' && (
            <Button title="Mark Complete" onPress={handleComplete} variant="secondary" size="lg" />
          )}
          <Button title="Delete Task" onPress={handleDelete} variant="danger" size="lg" />
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  header: {
    padding: spacing.lg,
    paddingTop: 60,
    paddingBottom: 30,
  },
  headerTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  back: {
    width: 40, height: 40, borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.15)',
    alignItems: 'center', justifyContent: 'center',
  },
  editBtn: {
    width: 40, height: 40, borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.15)',
    alignItems: 'center', justifyContent: 'center',
  },
  title: {
    fontSize: 26, fontWeight: '700', color: '#fff', 
  },
  content: { padding: spacing.md, paddingBottom: 40 },
  sectionTitle: { fontSize: 13, fontWeight: '600', marginBottom: 8, textTransform: 'uppercase', letterSpacing: 0.5 },
  detailRow: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: '#3a3a55',
  },
  dot: { width: 10, height: 10, borderRadius: 5 },
  actions: { gap: 12, marginTop: 16 },
});
