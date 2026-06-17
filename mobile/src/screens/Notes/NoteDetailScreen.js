import React, { useState, useEffect, useRef } from 'react';
import {
  View, Text, StyleSheet, ScrollView,
  TouchableOpacity, TextInput, Alert, Platform,
} from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { useToast } from '../../context/ToastContext';
import { spacing, borderRadius } from '../../theme/colors';
import { notesApi } from '../../api/note';
import Button from '../../components/Button';
import { Ionicons } from '@expo/vector-icons';

const NOTE_COLORS = [
  '#fff9c4', '#ffcccb', '#c8e6c9', '#bbdefb',
  '#e1bee7', '#ffecb3', '#b2dfdb', '#f8bbd0',
];

const MARKDOWN_TOOLS = [
  { icon: 'code-slash', open: '# ', close: '', label: 'H1', block: true },
  { icon: 'code-slash', open: '## ', close: '', label: 'H2', block: true },
  { icon: 'code-slash', open: '### ', close: '', label: 'H3', block: true },
  { type: 'divider' },
  { icon: 'bold', open: '**', close: '**', label: 'Bold' },
  { icon: 'italic', open: '*', close: '*', label: 'Italic' },
  { icon: 'underline', open: '<u>', close: '</u>', label: 'Underline' },
  { icon: 'remove', open: '~~', close: '~~', label: 'Strike' },
  { type: 'divider' },
  { icon: 'list', open: '\n- ', close: '', label: 'Bullet', block: true },
  { icon: 'list', open: '\n1. ', close: '', label: 'Ordered', block: true },
  { icon: 'code-working', open: '`', close: '`', label: 'Code' },
  { type: 'divider' },
  { icon: 'link', open: '[', close: '](url)', label: 'Link' },
  { type: 'divider' },
  { icon: 'close-circle', label: 'Clear', action: 'clean' },
];

export default function NoteDetailScreen({ route, navigation }) {
  const { colors } = useTheme();
  const { showToast } = useToast();
  const noteId = route.params?.noteId;
  const isNew = !noteId;

  const [title, setTitle] = useState('');
  const [content, setContent] = useState('');
  const [color, setColor] = useState(NOTE_COLORS[0]);
  const [saving, setSaving] = useState(false);
  const contentRef = useRef(null);
  const [selection, setSelection] = useState({ start: 0, end: 0 });

  useEffect(() => {
    if (noteId) loadNote();
  }, [noteId]);

  const loadNote = async () => {
    try {
      const res = await notesApi.show(noteId);
      const note = res.data.data || res.data.note || res.data;
      setTitle(note.title || '');
      setContent(note.content || '');
      setColor(note.color || NOTE_COLORS[0]);
    } catch (e) {
      showToast('Failed to load note', 'error');
      navigation.goBack();
    }
  };

  const insertMarkdown = (open, close, isBlock) => {
    const selected = content.substring(selection.start, selection.end);
    const before = content.substring(0, selection.start);
    const after = content.substring(selection.end);

    if (isBlock) {
      const prefix = open.startsWith('\n') ? open : '\n' + open;
      setContent(before + prefix + (selected || '') + after);
    } else if (selected) {
      setContent(before + open + selected + close + after);
    } else {
      setContent(before + open + close + after);
    }
  };

  const handleLink = () => {
    const selected = content.substring(selection.start, selection.end);
    if (Platform.OS === 'web') {
      const url = prompt('Enter URL:', 'https://');
      if (url) {
        const text = selected || 'link';
        insertMarkdown('[' + text + '](', url + ')', false);
      }
    } else {
      Alert.prompt('Insert Link', 'Enter URL:', [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Insert',
          onPress: (url) => {
            const text = selected || 'link';
            const before = content.substring(0, selection.start);
            const after = content.substring(selection.end);
            setContent(before + '[' + text + '](' + (url || 'https://') + ')' + after);
          },
        },
      ], 'plain-text', 'https://');
    }
  };

  const handleFormat = (tool) => {
    if (tool.action === 'clean') {
      setContent(
        content
          .replace(/\*\*(.*?)\*\*/g, '$1')
          .replace(/\*(.*?)\*/g, '$1')
          .replace(/~~(.*?)~~/g, '$1')
          .replace(/<u>(.*?)<\/u>/g, '$1')
          .replace(/`(.*?)`/g, '$1')
          .replace(/^### /gm, '')
          .replace(/^## /gm, '')
          .replace(/^# /gm, '')
          .replace(/^- /gm, '')
          .replace(/^\d+\. /gm, '')
          .replace(/\[([^\]]+)\]\([^)]+\)/g, '$1')
      );
      return;
    }
    if (tool.label === 'Link') {
      handleLink();
      return;
    }
    insertMarkdown(tool.open, tool.close, tool.block);
  };

  const handleSave = async () => {
    const trimmedTitle = title.trim();
    const trimmedContent = content.trim();

    if (!trimmedTitle && !trimmedContent.replace(/[*~`\s]/g, '')) {
      showToast('Note is empty — add a title or content', 'error');
      return;
    }

    setSaving(true);
    try {
      const payload = { title: trimmedTitle, content: trimmedContent, color };
      if (isNew) {
        await notesApi.store(payload);
        showToast('Note created!', 'success');
      } else {
        await notesApi.update(noteId, payload);
        showToast('Note saved!', 'success');
      }
      navigation.goBack();
    } catch (e) {
      showToast('Failed to save', 'error');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = () => {
    Alert.alert('Delete Note', 'Are you sure?', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Delete', style: 'destructive',
        onPress: async () => {
          try {
            await notesApi.destroy(noteId);
            showToast('Note deleted', 'info');
            navigation.goBack();
          } catch (e) { showToast('Failed to delete', 'error'); }
        },
      },
    ]);
  };

  return (
    <View style={[styles.container, { backgroundColor: color }]}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.headerBtn}>
          <Ionicons name="arrow-back" size={24} color="#1a1a2e" />
        </TouchableOpacity>
        <View style={{ flex: 1 }} />
        {!isNew && (
          <TouchableOpacity onPress={handleDelete} style={[styles.headerBtn, { marginRight: 8 }]}>
            <Ionicons name="trash-outline" size={22} color="#1a1a2e" />
          </TouchableOpacity>
        )}
      </View>

      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        style={styles.toolbarScroll}
        contentContainerStyle={styles.toolbar}
      >
        {MARKDOWN_TOOLS.map((tool, i) =>
          tool.type === 'divider' ? (
            <View key={i} style={[styles.divider, { backgroundColor: 'rgba(0,0,0,0.12)' }]} />
          ) : (
            <TouchableOpacity
              key={tool.icon + (tool.label || '')}
              onPress={() => handleFormat(tool)}
              style={styles.toolBtn}
            >
              <Ionicons name={tool.icon} size={17} color="#1a1a2e" />
            </TouchableOpacity>
          )
        )}
      </ScrollView>

      <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
        <TextInput
          value={title}
          onChangeText={setTitle}
          placeholder="Note title"
          placeholderTextColor="rgba(26,26,46,0.35)"
          style={styles.titleInput}
        />
        <TextInput
          ref={contentRef}
          value={content}
          onChangeText={setContent}
          onSelectionChange={(e) => setSelection(e.nativeEvent.selection)}
          placeholder="Start writing..."
          placeholderTextColor="rgba(26,26,46,0.3)"
          style={styles.contentInput}
          multiline
          textAlignVertical="top"
        />
      </ScrollView>

      <View style={styles.bottomBar}>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.colorRow}>
          {NOTE_COLORS.map((c) => (
            <TouchableOpacity
              key={c}
              onPress={() => setColor(c)}
              style={[
                styles.colorDot,
                { backgroundColor: c },
                color === c && styles.colorDotSelected,
              ]}
            />
          ))}
        </ScrollView>
        <Button title="Save" onPress={handleSave} loading={saving} size="sm" />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  header: {
    flexDirection: 'row',
    paddingTop: 60,
    paddingHorizontal: spacing.md,
    paddingBottom: spacing.xs,
    alignItems: 'center',
  },
  headerBtn: {
    width: 40, height: 40, borderRadius: 20,
    backgroundColor: 'rgba(0,0,0,0.08)',
    alignItems: 'center', justifyContent: 'center',
  },
  toolbarScroll: {
    maxHeight: 48,
  },
  toolbar: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.md,
    paddingVertical: 8,
    gap: 4,
  },
  toolBtn: {
    width: 34, height: 34, borderRadius: 8,
    alignItems: 'center', justifyContent: 'center',
    backgroundColor: 'rgba(0,0,0,0.06)',
  },
  divider: {
    width: 1, height: 18, marginHorizontal: 3,
  },
  content: { padding: spacing.md, flexGrow: 1 },
  titleInput: {
    fontSize: 26, fontWeight: '700', color: '#1a1a2e', marginBottom: 12,
  },
  contentInput: {
    fontSize: 16, color: '#1a1a2e', lineHeight: 24, minHeight: 300,
  },
  bottomBar: {
    flexDirection: 'row', alignItems: 'center',
    padding: spacing.md, gap: 12,
  },
  colorRow: { flex: 1 },
  colorDot: {
    width: 32, height: 32, borderRadius: 16, marginRight: 10,
    borderWidth: 2, borderColor: 'transparent',
  },
  colorDotSelected: { borderColor: '#1a1a2e' },
});
