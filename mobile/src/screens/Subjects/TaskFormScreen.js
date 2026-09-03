import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Platform,
} from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { useToast } from '../../context/ToastContext';
import { spacing, borderRadius, typography } from '../../theme/colors';
import { tasksApi } from '../../api/tasks';
import { categoriesApi } from '../../api/categories';
import { subjectsApi } from '../../api/subjects';
import Input from '../../components/Input';
import Button from '../../components/Button';
import { Ionicons } from '@expo/vector-icons';
import DateTimePicker from '@react-native-community/datetimepicker';
import { LinearGradient } from 'expo-linear-gradient';

const PRIORITIES = ['low', 'medium', 'high'];

export default function TaskFormScreen({ route, navigation }) {
  const { colors } = useTheme();
  const { showToast } = useToast();
  const { subjectId, task: existing } = route.params || {};
  const isEdit = !!existing;

  const [title, setTitle] = useState(existing?.title || '');
  const [description, setDescription] = useState(existing?.description || '');
  const [priority, setPriority] = useState(existing?.priority || 'medium');
  const [dueDate, setDueDate] = useState(
    existing?.due_date ? new Date(existing.due_date) : null
  );
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [categories, setCategories] = useState([]);
  const [categoryId, setCategoryId] = useState(existing?.category_id || null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const [subjects, setSubjects] = useState([]);
  const [selectedSubject, setSelectedSubject] = useState(
    subjectId ? { id: subjectId } : null
  );
  const [showSubjectPicker, setShowSubjectPicker] = useState(false);
  const [invitedEmail, setInvitedEmail] = useState('');

  useEffect(() => {
    loadCategories();
    if (!subjectId) loadSubjects();
  }, []);

  const loadSubjects = async () => {
    try {
      const res = await subjectsApi.list();
      const raw = res.data.data || res.data.subjects || res.data || [];
      const data = Array.isArray(raw) ? raw : raw?.data || [];
      setSubjects(data);
    } catch (e) {}
  };

  const loadCategories = async () => {
    try {
      const res = await categoriesApi.list();
      const raw = res.data.data || res.data.categories || res.data || [];
      const data = Array.isArray(raw) ? raw : raw?.data || [];
      setCategories(data);
    } catch (e) {}
  };

  const handleSubmit = async () => {
    if (!title.trim()) {
      setError('Title is required');
      return;
    }
    if (!subjectId && !selectedSubject) {
      setError('Please select a subject');
      return;
    }
    setLoading(true);
    setError('');
    const payload = {
      subject_id: subjectId || selectedSubject.id,
      title: title.trim(),
      description: description.trim(),
      priority,
      status: 'pending',
      category_id: categoryId,
      due_date: dueDate ? dueDate.toISOString().split('T')[0] : null,
      invited_email: invitedEmail.trim() || null,
    };

    try {
      if (isEdit) {
        await tasksApi.update(existing.id, payload);
        showToast('Task updated!', 'success');
      } else {
        await tasksApi.store(payload);
        showToast('Task created!', 'success');
      }
      navigation.goBack();
    } catch (e) {
      setError(e.response?.data?.message || 'Failed to save task');
      showToast('Failed to save task', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <LinearGradient
      colors={['#0f0c29', '#1a1638', '#0f0c29']}
      style={styles.container}
    >
      <View style={[styles.header, { backgroundColor: colors.primary }]}>
        <TouchableOpacity
          onPress={() => navigation.goBack()}
          style={styles.back}
        >
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.title}>
          {isEdit ? 'Edit Task' : 'New Task'}
        </Text>
      </View>

      <ScrollView contentContainerStyle={styles.form}>
        {error ? (
          <View style={[styles.errorBox, { backgroundColor: colors.danger + '20' }]}>
            <Text style={{ color: colors.danger, fontSize: 13 }}>{error}</Text>
          </View>
        ) : null}

        {!subjectId && (
          <>
            <Text style={[styles.label, { color: colors.textSecondary }]}>Subject</Text>
            <TouchableOpacity
              onPress={() => setShowSubjectPicker(!showSubjectPicker)}
              style={[
                styles.selector,
                { backgroundColor: colors.bgInput, borderColor: colors.border },
              ]}
            >
              <Ionicons
                name="folder-outline"
                size={18}
                color={selectedSubject ? colors.primary : colors.textMuted}
              />
              <Text
                style={{
                  flex: 1,
                  color: selectedSubject ? colors.text : colors.textMuted,
                  marginLeft: 8,
                }}
              >
                {selectedSubject ? selectedSubject.name : 'Choose a subject'}
              </Text>
              <Ionicons
                name={showSubjectPicker ? 'chevron-up' : 'chevron-down'}
                size={18}
                color={colors.textMuted}
              />
            </TouchableOpacity>

            {showSubjectPicker && (
              <View
                style={[
                  styles.pickerDropdown,
                  { backgroundColor: colors.bgCard, borderColor: colors.border },
                ]}
              >
                {subjects.length === 0 ? (
                  <Text style={{ color: colors.textMuted, padding: 12, textAlign: 'center' }}>
                    No subjects found. Create one first.
                  </Text>
                ) : (
                  subjects.map((subj) => (
                    <TouchableOpacity
                      key={subj.id}
                      onPress={() => {
                        setSelectedSubject(subj);
                        setShowSubjectPicker(false);
                      }}
                      style={[
                        styles.pickerItem,
                        selectedSubject?.id === subj.id && {
                          backgroundColor: colors.primary + '20',
                        },
                      ]}
                    >
                      <View
                        style={[styles.colorDot, { backgroundColor: subj.color || colors.primary }]}
                      />
                      <Text style={{ color: colors.text, flex: 1 }}>{subj.name}</Text>
                      {selectedSubject?.id === subj.id && (
                        <Ionicons name="checkmark" size={18} color={colors.primary} />
                      )}
                    </TouchableOpacity>
                  ))
                )}
              </View>
            )}
          </>
        )}

        <Input label="Title" value={title} onChangeText={setTitle} placeholder="Task title" />
        <Input
          label="Description"
          value={description}
          onChangeText={setDescription}
          placeholder="Optional details..."
          multiline
          numberOfLines={3}
        />

        <Text style={[styles.label, { color: colors.textSecondary }]}>Priority</Text>
        <View style={styles.priorityRow}>
          {PRIORITIES.map((p) => (
            <TouchableOpacity
              key={p}
              onPress={() => setPriority(p)}
              style={[
                styles.priorityBtn,
                {
                  backgroundColor: priority === p ? colors.priority[p] + '30' : colors.bgInput,
                  borderColor: priority === p ? colors.priority[p] : colors.border,
                },
              ]}
            >
              <Text
                style={{
                  color: priority === p ? colors.priority[p] : colors.textMuted,
                  fontWeight: '600',
                  textTransform: 'capitalize',
                }}
              >
                {p}
              </Text>
            </TouchableOpacity>
          ))}
        </View>

        {categories.length > 0 && (
          <>
            <Text style={[styles.label, { color: colors.textSecondary }]}>Category</Text>
            <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.catScroll}>
              <TouchableOpacity
                onPress={() => setCategoryId(null)}
                style={[
                  styles.catChip,
                  {
                    backgroundColor: !categoryId ? colors.primary + '30' : colors.bgInput,
                    borderColor: !categoryId ? colors.primary : colors.border,
                  },
                ]}
              >
                <Text style={{ color: !categoryId ? colors.primary : colors.textMuted, fontSize: 13 }}>
                  None
                </Text>
              </TouchableOpacity>
              {categories.map((cat) => (
                <TouchableOpacity
                  key={cat.id}
                  onPress={() => setCategoryId(cat.id)}
                  style={[
                    styles.catChip,
                    {
                      backgroundColor: categoryId === cat.id ? colors.primary + '30' : colors.bgInput,
                      borderColor: categoryId === cat.id ? colors.primary : colors.border,
                    },
                  ]}
                >
                  <Text
                    style={{
                      color: categoryId === cat.id ? colors.primary : colors.textMuted,
                      fontSize: 13,
                    }}
                  >
                    {cat.name}
                  </Text>
                </TouchableOpacity>
              ))}
            </ScrollView>
          </>
        )}

        <Text style={[styles.label, { color: colors.textSecondary }]}>Due Date</Text>
        <View
          style={[
            styles.dateBtn,
            { backgroundColor: colors.bgInput, borderColor: colors.border },
          ]}
        >
          <Ionicons name="calendar-outline" size={18} color={colors.textMuted} />
          {Platform.OS === 'web' ? (
            <TextInput
              value={dueDate ? dueDate.toISOString().split('T')[0] : ''}
              onChangeText={(text) => {
                setDueDate(text ? new Date(text + 'T00:00:00') : null);
              }}
              placeholder="YYYY-MM-DD"
              placeholderTextColor={colors.textMuted}
              style={{ flex: 1, marginLeft: 8, color: dueDate ? colors.text : colors.textMuted, fontSize: 15, paddingVertical: 0 }}
            />
          ) : (
            <TouchableOpacity
              onPress={() => setShowDatePicker(true)}
              style={{ flex: 1, flexDirection: 'row', alignItems: 'center' }}
            >
              <Text style={{ color: dueDate ? colors.text : colors.textMuted, marginLeft: 8 }}>
                {dueDate ? dueDate.toLocaleDateString() : 'Pick a date'}
              </Text>
            </TouchableOpacity>
          )}
          {dueDate && (
            <TouchableOpacity
              onPress={() => setDueDate(null)}
              style={{ marginLeft: 'auto' }}
            >
              <Ionicons name="close-circle" size={18} color={colors.textMuted} />
            </TouchableOpacity>
          )}
        </View>

        {showDatePicker && Platform.OS !== 'web' && (
          <DateTimePicker
            value={dueDate || new Date()}
            mode="date"
            display={Platform.OS === 'ios' ? 'spinner' : 'default'}
            onChange={(event, date) => {
              setShowDatePicker(false);
              if (date) setDueDate(date);
            }}
          />
        )}

        <Input
          label="Invite Collaborator (optional)"
          value={invitedEmail}
          onChangeText={setInvitedEmail}
          placeholder="email@example.com"
          keyboardType="email-address"
          autoCapitalize="none"
        />

        <Button
          title={isEdit ? 'Update Task' : 'Create Task'}
          onPress={handleSubmit}
          loading={loading}
          size="lg"
          style={{ marginTop: 16 }}
        />
      </ScrollView>
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
  form: { padding: spacing.lg, paddingBottom: 40 },
  errorBox: {
    padding: 12,
    borderRadius: borderRadius.sm,
    marginBottom: 16,
  },
  label: {
    fontSize: 13,
    fontWeight: '600',
    marginBottom: 8,
  },
  priorityRow: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 20,
  },
  priorityBtn: {
    flex: 1,
    paddingVertical: 10,
    borderRadius: borderRadius.sm,
    borderWidth: 1.5,
    alignItems: 'center',
  },
  catScroll: {
    marginBottom: 20,
  },
  catChip: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: borderRadius.full,
    borderWidth: 1.5,
    marginRight: 8,
  },
  dateBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 14,
    borderRadius: borderRadius.md,
    borderWidth: 1,
    marginBottom: 20,
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
  colorDot: {
    width: 12,
    height: 12,
    borderRadius: 6,
  },
});
