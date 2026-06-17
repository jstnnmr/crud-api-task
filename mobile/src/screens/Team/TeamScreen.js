import React, { useState, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  RefreshControl,
  TouchableOpacity,
  TextInput,
  Modal,
} from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { useToast } from '../../context/ToastContext';
import { spacing, borderRadius, typography } from '../../theme/colors';
import { tasksApi } from '../../api/tasks';
import { teamApi } from '../../api/team';
import TaskItem from '../../components/TaskItem';
import Card from '../../components/Card';
import Button from '../../components/Button';
import Loading from '../../components/Loading';
import EmptyState from '../../components/EmptyState';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';

export default function TeamScreen({ navigation }) {
  const { colors } = useTheme();
  const { showToast } = useToast();
  const [tasks, setTasks] = useState([]);
  const [invitations, setInvitations] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [inviteModal, setInviteModal] = useState(false);
  const [inviteEmail, setInviteEmail] = useState('');
  const [selectedTaskId, setSelectedTaskId] = useState(null);
  const [showTaskPicker, setShowTaskPicker] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  const load = useCallback(async () => {
    try {
      const [tasksRes, invRes] = await Promise.all([
        tasksApi.myTasks(),
        teamApi.getInvitations(),
      ]);
      const raw = tasksRes.data.data || tasksRes.data.tasks || tasksRes.data || [];
      const t = Array.isArray(raw) ? raw : raw?.data || [];
      setTasks(t);
      const invRaw = invRes.data.data || invRes.data.invitations || invRes.data || [];
      setInvitations(Array.isArray(invRaw) ? invRaw : []);
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  }, []);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  const onRefresh = async () => {
    setRefreshing(true);
    await load();
    setRefreshing(false);
  };

  const handleInvite = async () => {
    if (!inviteEmail.trim() || !selectedTaskId) return;
    setSubmitting(true);
    try {
      await teamApi.invite({ email: inviteEmail.trim(), task_id: selectedTaskId });
      setInviteModal(false);
      setInviteEmail('');
      setSelectedTaskId(null);
      showToast('Invitation sent!', 'success');
    } catch (e) {
      showToast(e.response?.data?.message || 'Failed to invite', 'error');
    } finally {
      setSubmitting(false);
    }
  };

  const handleAccept = async (token) => {
    try {
      await teamApi.acceptInvitation(token);
      showToast('Invitation accepted!', 'success');
      load();
    } catch (e) {
      showToast(e.response?.data?.message || 'Failed', 'error');
    }
  };

  const handleDecline = async (token) => {
    try {
      await teamApi.declineInvitation(token);
      showToast('Invitation declined', 'info');
      load();
    } catch (e) {
      showToast(e.response?.data?.message || 'Failed', 'error');
    }
  };

  if (loading) return <Loading />;

  return (
    <LinearGradient
      colors={['#0f0c29', '#1a1638', '#0f0c29']}
      style={styles.container}
    >
      <LinearGradient colors={[colors.primary, colors.primaryDark]} style={styles.header}>
        <Text style={styles.title}>Team</Text>
        <Text style={styles.subtitle}>Collaborate on tasks</Text>
      </LinearGradient>

      <ScrollView
        contentContainerStyle={styles.scroll}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />
        }
      >
        {invitations.length > 0 && (
          <View style={styles.section}>
            <Text style={[typography.h3, { color: colors.text, marginBottom: 12 }]}>
              Pending Invitations ({invitations.length})
            </Text>
            {invitations.map((inv) => (
              <Card key={inv.id || inv.token}>
                <View style={styles.invRow}>
                  <View style={{ flex: 1 }}>
                    <Text style={{ color: colors.text, fontWeight: '600' }}>
                      {inv.task?.title || 'Task Invitation'}
                    </Text>
                    <Text style={{ color: colors.textMuted, fontSize: 13 }}>
                      From: {inv.invited_by_name || inv.invited_by}
                    </Text>
                  </View>
                  <View style={styles.invActions}>
                    <TouchableOpacity
                      onPress={() => handleAccept(inv.token)}
                      style={[styles.invBtn, { backgroundColor: colors.success + '20' }]}
                    >
                      <Ionicons name="checkmark" size={20} color={colors.success} />
                    </TouchableOpacity>
                    <TouchableOpacity
                      onPress={() => handleDecline(inv.token)}
                      style={[styles.invBtn, { backgroundColor: colors.danger + '20' }]}
                    >
                      <Ionicons name="close" size={20} color={colors.danger} />
                    </TouchableOpacity>
                  </View>
                </View>
              </Card>
            ))}
          </View>
        )}

        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={[typography.h3, { color: colors.text }]}>Collaborative Tasks</Text>
            <TouchableOpacity
              onPress={() => setInviteModal(true)}
              style={[styles.inviteBtn, { backgroundColor: colors.primary + '20' }]}
            >
              <Ionicons name="person-add" size={18} color={colors.primary} />
              <Text style={{ color: colors.primary, fontSize: 13, fontWeight: '600' }}>Invite</Text>
            </TouchableOpacity>
          </View>

          {(() => {
            const collabTasks = tasks.filter((x) => x.collaborators?.length > 0 || x.task_user);
            return collabTasks.length === 0 ? (
              <EmptyState icon="people-outline" title="No Team Tasks" message="Invite someone to collaborate" />
            ) : (
              collabTasks.map((task) => (
                <TaskItem
                  key={task.id}
                  task={task}
                  onPress={(t) => navigation.navigate('TaskDetail', { taskId: t.id })}
                  onComplete={async (t) => {
                    try {
                    await tasksApi.complete(t.id);
                    showToast('Task completed!', 'success');
                    load();
                  } catch (e) { showToast('Failed to complete', 'error'); }
                  }}
                />
              ))
            );
          })()}
        </View>
      </ScrollView>

      <Modal visible={inviteModal} transparent animationType="slide">
        <View style={[styles.modalOverlay, { backgroundColor: colors.overlay }]}>
          <View style={[styles.modal, { backgroundColor: colors.bgLight }]}>
            <View style={styles.modalHeader}>
              <Text style={[typography.h3, { color: colors.text }]}>Invite Collaborator</Text>
              <TouchableOpacity onPress={() => setInviteModal(false)}>
                <Ionicons name="close" size={24} color={colors.textMuted} />
              </TouchableOpacity>
            </View>
            <Text style={[styles.modalLabel, { color: colors.textSecondary }]}>Task</Text>
            <TouchableOpacity
              onPress={() => setShowTaskPicker(!showTaskPicker)}
              style={[
                styles.selector,
                { backgroundColor: colors.bgInput, borderColor: colors.border },
              ]}
            >
              <Ionicons
                name="checkbox-outline"
                size={18}
                color={selectedTaskId ? colors.primary : colors.textMuted}
              />
              <Text
                style={{
                  flex: 1,
                  color: selectedTaskId ? colors.text : colors.textMuted,
                  marginLeft: 8,
                }}
              >
                {selectedTaskId
                  ? tasks.find((t) => t.id === selectedTaskId)?.title || 'Select a task'
                  : 'Select a task'}
              </Text>
              <Ionicons
                name={showTaskPicker ? 'chevron-up' : 'chevron-down'}
                size={18}
                color={colors.textMuted}
              />
            </TouchableOpacity>

            {showTaskPicker && (
              <View
                style={[
                  styles.pickerDropdown,
                  { backgroundColor: colors.bgCard, borderColor: colors.border },
                ]}
              >
                {tasks.length === 0 ? (
                  <Text style={{ color: colors.textMuted, padding: 12, textAlign: 'center' }}>
                    No collaborative tasks available
                  </Text>
                ) : (
                  tasks.map((t) => (
                    <TouchableOpacity
                      key={t.id}
                      onPress={() => {
                        setSelectedTaskId(t.id);
                        setShowTaskPicker(false);
                      }}
                      style={[
                        styles.pickerItem,
                        selectedTaskId === t.id && { backgroundColor: colors.primary + '20' },
                      ]}
                    >
                      <Text style={{ color: colors.text, flex: 1 }}>{t.title}</Text>
                      {selectedTaskId === t.id && (
                        <Ionicons name="checkmark" size={18} color={colors.primary} />
                      )}
                    </TouchableOpacity>
                  ))
                )}
              </View>
            )}
            <TextInput
              value={inviteEmail}
              onChangeText={setInviteEmail}
              placeholder="Email address"
              placeholderTextColor={colors.textMuted}
              keyboardType="email-address"
              autoCapitalize="none"
              style={[
                styles.modalInput,
                { backgroundColor: colors.bgInput, borderColor: colors.border, color: colors.text },
              ]}
            />
            <Button
              title="Send Invitation"
              onPress={handleInvite}
              loading={submitting}
              disabled={!selectedTaskId || !inviteEmail.trim()}
            />
          </View>
        </View>
      </Modal>
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  scroll: { paddingBottom: 20 },
  header: {
    padding: spacing.lg, paddingTop: 60, paddingBottom: 30,
  },
  title: {
    fontSize: 28, fontWeight: '700', color: '#fff',
  },
  subtitle: {
    fontSize: 15, color: 'rgba(255,255,255,0.7)', marginTop: 2,
  },
  section: { padding: spacing.md },
  sectionHeader: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    marginBottom: 12,
  },
  inviteBtn: {
    flexDirection: 'row', alignItems: 'center', gap: 4,
    paddingHorizontal: 12, paddingVertical: 6, borderRadius: borderRadius.full,
  },
  invRow: {
    flexDirection: 'row', alignItems: 'center',
  },
  invActions: { flexDirection: 'row', gap: 8 },
  invBtn: {
    width: 36, height: 36, borderRadius: 18, alignItems: 'center', justifyContent: 'center',
  },
  modalOverlay: {
    flex: 1, justifyContent: 'flex-end',
  },
  modal: {
    borderTopLeftRadius: borderRadius.xl, borderTopRightRadius: borderRadius.xl,
    padding: spacing.lg, paddingBottom: 40,
  },
  modalHeader: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    marginBottom: 20,
  },
  modalLabel: {
    fontSize: 13, fontWeight: '600', marginBottom: 8,
  },
  taskChip: {
    paddingHorizontal: 16, paddingVertical: 8, borderRadius: borderRadius.full,
    borderWidth: 1.5, marginRight: 8,
  },
  modalInput: {
    borderWidth: 1, borderRadius: borderRadius.md,
    padding: 14, fontSize: 15, marginBottom: 16,
  },
  selector: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 14,
    borderRadius: borderRadius.md,
    borderWidth: 1,
    marginBottom: 16,
  },
  pickerDropdown: {
    borderRadius: borderRadius.md,
    borderWidth: 1,
    marginBottom: 16,
    overflow: 'hidden',
  },
  pickerItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 14,
    gap: 10,
  },
});
