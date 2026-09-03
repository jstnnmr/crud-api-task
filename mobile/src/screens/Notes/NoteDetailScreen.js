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
import RichEditor from '../../components/RichEditor';
import { Ionicons } from '@expo/vector-icons';

const NOTE_COLORS = [
  '#fff9c4', '#ffcccb', '#c8e6c9', '#bbdefb',
  '#e1bee7', '#ffecb3', '#b2dfdb', '#f8bbd0',
];

const TOOLS = [
  { icon: 'code-slash', command: 'formatBlock', value: 'h1', label: 'H1' },
  { icon: 'code-slash', command: 'formatBlock', value: 'h2', label: 'H2' },
  { icon: 'code-slash', command: 'formatBlock', value: 'h3', label: 'H3' },
  { type: 'divider' },
  { icon: 'bold', command: 'bold', label: 'Bold' },
  { icon: 'italic', command: 'italic', label: 'Italic' },
  { icon: 'underline', command: 'underline', label: 'Underline' },
  { icon: 'remove', command: 'strikeThrough', label: 'Strike' },
  { type: 'divider' },
  { icon: 'list', command: 'insertUnorderedList', label: 'Bullet' },
  { icon: 'list', command: 'insertOrderedList', label: 'Ordered' },
  { icon: 'code-working', command: 'insertHTML', value: '<code>code</code>', label: 'Code' },
  { type: 'divider' },
  { icon: 'link', command: 'link', label: 'Link' },
  { type: 'divider' },
  { icon: 'close-circle', command: 'removeFormat', label: 'Clear' },
];

function mdToHtml(text) {
  if (!text) return '';
  if (/<[a-z][\s\S]*>/i.test(text)) return text;
  let html = text
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/^### (.+)$/gm, '<h3>$1</h3>')
    .replace(/^## (.+)$/gm, '<h2>$1</h2>')
    .replace(/^# (.+)$/gm, '<h1>$1</h1>')
    .replace(/\*\*(.+?)\*\*/g, '<b>$1</b>')
    .replace(/\*(.+?)\*/g, '<i>$1</i>')
    .replace(/~~(.+?)~~/g, '<s>$1</s>')
    .replace(/<u>(.+?)<\/u>/g, '<u>$1</u>')
    .replace(/`(.+?)`/g, '<code>$1</code>')
    .replace(/^- (.+)$/gm, '<li>$1</li>')
    .replace(/^\d+\. (.+)$/gm, '<li>$1</li>')
    .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>')
    .replace(/\n\n/g, '</p><p>')
    .replace(/\n/g, '<br>');
  html = '<p>' + html + '</p>';
  html = html.replace(/<li>.*?<\/li>/g, (m) => '<ul>' + m + '</ul>');
  html = html.replace(/<\/ul>\s*<ul>/g, '');
  return html;
}

function stripHtml(html) {
  return html.replace(/<[^>]*>/g, '').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').trim();
}

export default function NoteDetailScreen({ route, navigation }) {
  const { colors } = useTheme();
  const { showToast } = useToast();
  const noteId = route.params?.noteId;
  const isNew = !noteId;

  const [title, setTitle] = useState('');
  const [content, setContent] = useState('');
  const [color, setColor] = useState(NOTE_COLORS[0]);
  const [saving, setSaving] = useState(false);
  const editorRef = useRef(null);
  const isLoadedRef = useRef(false);

  useEffect(() => {
    if (noteId) loadNote();
  }, [noteId]);

  const loadNote = async () => {
    try {
      const res = await notesApi.show(noteId);
      const note = res.data.data || res.data.note || res.data;
      setTitle(note.title || '');
      setColor(note.color || NOTE_COLORS[0]);
      const raw = note.content || '';
      const html = mdToHtml(raw);
      setContent(html);
      isLoadedRef.current = true;
    } catch (e) {
      showToast('Failed to load note', 'error');
      navigation.goBack();
    }
  };

  const handleFormat = (tool) => {
    if (tool.command === 'link') {
      handleLink();
      return;
    }
    editorRef.current?.exec(tool.command, tool.value);
  };

  const handleLink = () => {
    const insertLink = (url) => {
      if (!url) return;
      editorRef.current?.exec('createLink', url);
    };
    if (Platform.OS === 'web') {
      const url = prompt('Enter URL:', 'https://');
      insertLink(url);
    } else {
      Alert.prompt('Insert Link', 'Enter URL:', [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Insert', onPress: insertLink },
      ], 'plain-text', 'https://');
    }
  };

  const handleSave = async () => {
    const trimmedTitle = title.trim();
    const strippedContent = stripHtml(content).trim();

    if (!trimmedTitle && !strippedContent) {
      showToast('Note is empty — add a title or content', 'error');
      return;
    }

    setSaving(true);
    try {
      const payload = { title: trimmedTitle, content, color };
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
        {TOOLS.map((tool, i) =>
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
        <RichEditor
          ref={editorRef}
          initialContent={content}
          onContentChange={setContent}
          style={styles.editor}
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
  editor: {
    minHeight: 300,
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
