import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
} from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { spacing, borderRadius, typography } from '../../theme/colors';
import { subjectsApi } from '../../api/subjects';
import Input from '../../components/Input';
import Button from '../../components/Button';
import { Ionicons } from '@expo/vector-icons';

const PRESET_COLORS = [
  '#8e7dff', '#ff6b9d', '#4ade80', '#60a5fa',
  '#fbbf24', '#f87171', '#a78bfa', '#34d399',
  '#f472b6', '#38bdf8',
];

export default function SubjectFormScreen({ route, navigation }) {
  const { colors } = useTheme();
  const existing = route.params?.subject;
  const isEdit = !!existing;

  const [name, setName] = useState(existing?.name || '');
  const [color, setColor] = useState(existing?.color || PRESET_COLORS[0]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async () => {
    if (!name.trim()) {
      setError('Subject name is required');
      return;
    }
    setLoading(true);
    setError('');
    try {
      if (isEdit) {
        await subjectsApi.update(existing.id, { name: name.trim(), color });
      } else {
        await subjectsApi.store({ name: name.trim(), color });
      }
      navigation.goBack();
    } catch (e) {
      setError(e.response?.data?.message || 'Failed to save');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={[styles.container, { backgroundColor: colors.bg }]}>
      <View
        style={[styles.header, { backgroundColor: colors.primary }]}
      >
        <TouchableOpacity
          onPress={() => navigation.goBack()}
          style={styles.back}
        >
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.title}>
          {isEdit ? 'Edit Subject' : 'New Subject'}
        </Text>
      </View>

      <ScrollView contentContainerStyle={styles.form}>
        {error ? (
          <View
            style={[
              styles.errorBox,
              { backgroundColor: colors.danger + '20' },
            ]}
          >
            <Text style={{ color: colors.danger, fontSize: 13 }}>{error}</Text>
          </View>
        ) : null}

        <Input
          label="Subject Name"
          value={name}
          onChangeText={setName}
          placeholder="e.g. Mathematics"
        />

        <Text
          style={[
            styles.label,
            { color: colors.textSecondary },
          ]}
        >
          Color
        </Text>
        <View style={styles.colorsRow}>
          {PRESET_COLORS.map((c) => (
            <TouchableOpacity
              key={c}
              onPress={() => setColor(c)}
              style={[
                styles.colorItem,
                { backgroundColor: c },
                color === c && styles.colorSelected,
              ]}
            />
          ))}
        </View>

        <Button
          title={isEdit ? 'Update Subject' : 'Create Subject'}
          onPress={handleSubmit}
          loading={loading}
          size="lg"
        />
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
  form: {
    padding: spacing.lg,
  },
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
  colorsRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginBottom: 24,
  },
  colorItem: {
    width: 36,
    height: 36,
    borderRadius: 18,
  },
  colorSelected: {
    borderWidth: 3,
    borderColor: '#fff',
  },
});
