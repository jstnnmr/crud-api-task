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
import { spacing, borderRadius } from '../../theme/colors';
import { notesApi } from '../../api/note';
import NoteCard from '../../components/NoteCard';
import Loading from '../../components/Loading';
import EmptyState from '../../components/EmptyState';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';

export default function NotesScreen({ navigation }) {
  const { colors } = useTheme();
  const { showToast } = useToast();
  const [notes, setNotes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');

  const loadNotes = useCallback(async () => {
    setError('');
    try {
      const res = await notesApi.list();
      const raw = res.data.data || res.data.notes || res.data || [];
      const data = Array.isArray(raw) ? raw : raw?.data || [];
      setNotes(data.filter((n) => {
        const t = (n.title || '').trim();
        const c = (n.content || '').replace(/<[^>]*>/g, '').replace(/[*~`\s]/g, '').trim();
        return t || c;
      }));
    } catch (e) {
      const msg = e.response?.data?.message || e.message || 'Failed to load notes';
      setError(msg);
      console.error(e);
    } finally {
      setLoading(false);
    }
  }, []);

  useFocusEffect(useCallback(() => { loadNotes(); }, [loadNotes]));

  const onRefresh = async () => {
    setRefreshing(true);
    await loadNotes();
    setRefreshing(false);
  };

  const handleDelete = (note) => {
    Alert.alert('Delete Note', `Delete this note?`, [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Delete',
        style: 'destructive',
        onPress: async () => {
          try {
            await notesApi.destroy(note.id);
            showToast('Note deleted', 'info');
            loadNotes();
          } catch (e) {
            showToast('Failed to delete', 'error');
          }
        },
      },
    ]);
  };

  if (loading) return <Loading />;

  return (
    <LinearGradient
      colors={['#0f0c29', '#1a1638', '#0f0c29']}
      style={styles.container}
    >
      <LinearGradient colors={[colors.primary, colors.primaryDark]} style={styles.header}>
        <Text style={styles.title}>Notes</Text>
        <Text style={styles.subtitle}>{notes.length} note{notes.length !== 1 ? 's' : ''}</Text>
      </LinearGradient>

      <ScrollView
        contentContainerStyle={styles.scroll}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />
        }
      >
        {error ? (
          <View style={[styles.errorBox, { backgroundColor: colors.danger + '20' }]}>
            <Text style={{ color: colors.danger, fontSize: 13 }}>{error}</Text>
          </View>
        ) : notes.length === 0 ? (
          <EmptyState icon="document-text-outline" title="No Notes Yet" message="Tap + to create your first note" />
        ) : (
          <View style={styles.list}>
            {notes.map((note) => (
              <NoteCard
                key={note.id}
                note={note}
                onPress={(n) => navigation.navigate('NoteDetail', { noteId: n.id })}
                onDelete={handleDelete}
              />
            ))}
          </View>
        )}
      </ScrollView>

      <TouchableOpacity
        style={[styles.fab, { backgroundColor: colors.primary }]}
        onPress={() => navigation.navigate('NoteDetail', {})}
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
  title: {
    fontSize: 28, fontWeight: '700', color: '#fff', 
  },
  subtitle: {
    fontSize: 15, color: 'rgba(255,255,255,0.7)', marginTop: 2,
  },
  scroll: { padding: spacing.md, paddingBottom: 100 },
  list: {
    gap: 2,
  },
  errorBox: {
    padding: 12,
    borderRadius: borderRadius.sm,
    marginBottom: 16,
  },
  fab: {
    position: 'absolute', bottom: 20, right: 20,
    width: 56, height: 56, borderRadius: 28,
    alignItems: 'center', justifyContent: 'center',
    elevation: 8, shadowColor: '#8e7dff',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3, shadowRadius: 8,
  },
});
