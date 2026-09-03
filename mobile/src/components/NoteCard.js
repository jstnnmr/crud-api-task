import React, { useRef } from 'react';
import { View, Text, StyleSheet, Pressable, Animated } from 'react-native';
import { borderRadius, spacing, fonts } from '../theme/colors';
import { useTheme } from '../context/ThemeContext';
import { Ionicons } from '@expo/vector-icons';

function stripMarkdown(text) {
  return text
    .replace(/<[^>]*>/g, '')
    .replace(/\*\*(.*?)\*\*/g, '$1')
    .replace(/\*(.*?)\*/g, '$1')
    .replace(/~~(.*?)~~/g, '$1')
    .replace(/`(.*?)`/g, '$1')
    .replace(/###\s/g, '')
    .replace(/##\s/g, '')
    .replace(/#\s/g, '')
    .replace(/^- /gm, '')
    .replace(/^\d+\. /gm, '')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .trim();
}

function getPreview(content) {
  const clean = stripMarkdown(content);
  const lines = clean.split(/\n+/).filter((l) => l.trim());
  return lines.slice(0, 2).join('  ');
}

export default function NoteCard({ note, onPress, onDelete }) {
  const { isDark, colors } = useTheme();
  const index = Math.max(0, (note.colorIndex ?? 0) % (colors.noteColors?.length || 1));
  const bgColor = note.color || colors.noteColors?.[index] || (isDark ? '#2A2415' : '#FBF4E2');
  const isDarkNote = isDark;
  const textColor = isDarkNote ? 'rgba(238,241,238,0.92)' : '#1a1a2e';
  const mutedColor = isDarkNote ? 'rgba(238,241,238,0.55)' : 'rgba(26,26,46,0.55)';
  const scaleAnim = useRef(new Animated.Value(1)).current;

  const handlePressIn = () => {
    Animated.timing(scaleAnim, {
      toValue: 0.99,
      duration: 90,
      useNativeDriver: true,
    }).start();
  };

  const handlePressOut = () => {
    Animated.timing(scaleAnim, {
      toValue: 1,
      duration: 140,
      useNativeDriver: true,
    }).start();
  };

  const preview = getPreview(note.content || '');

  return (
    <Pressable
      onPressIn={handlePressIn}
      onPressOut={handlePressOut}
      onPress={() => onPress?.(note)}
    >
      <Animated.View
        style={[
          styles.card,
          { backgroundColor: bgColor, transform: [{ scale: scaleAnim }] },
        ]}
      >
        <View style={styles.row}>
          <View style={styles.textCol}>
            {note.title ? (
              <Text style={[styles.title, { color: textColor }]} numberOfLines={1}>
                {note.title}
              </Text>
            ) : null}
            {preview ? (
              <Text style={[styles.preview, { color: mutedColor }]} numberOfLines={2}>
                {preview}
              </Text>
            ) : (
              <Text style={[styles.empty, { color: mutedColor }]}>No content</Text>
            )}
          </View>
          <Text style={[styles.date, { color: mutedColor }]}>
            {new Date(note.updated_at || note.created_at).toLocaleDateString(undefined, {
              month: 'short',
              day: 'numeric',
            })}
          </Text>
          {onDelete && (
            <Pressable onPress={() => onDelete(note)} style={styles.deleteBtn} hitSlop={8}>
              <Ionicons name="close-circle" size={18} color={mutedColor} />
            </Pressable>
          )}
        </View>
      </Animated.View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  card: {
    borderRadius: borderRadius.sm,
    paddingVertical: spacing.sm + 2,
    paddingHorizontal: spacing.md,
    width: '100%',
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: 'rgba(0,0,0,0.06)',
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  textCol: {
    flex: 1,
    marginRight: spacing.sm,
  },
  title: {
    fontFamily: fonts.uiSemiBold,
    fontSize: 16,
  },
  preview: {
    fontFamily: fonts.uiRegular,
    fontSize: 13,
    lineHeight: 18,
    marginTop: 2,
  },
  empty: {
    fontFamily: fonts.uiRegular,
    fontSize: 13,
    fontStyle: 'italic',
    marginTop: 2,
  },
  date: {
    fontFamily: fonts.uiRegular,
    fontSize: 11,
    marginRight: spacing.xs,
  },
  deleteBtn: {
    padding: 4,
  },
});
