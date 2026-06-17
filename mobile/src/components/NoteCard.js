import React, { useRef } from 'react';
import { View, Text, StyleSheet, Pressable, Animated } from 'react-native';
import { borderRadius, spacing } from '../theme/colors';
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
    .trim();
}

export default function NoteCard({ note, onPress, onDelete }) {
  const bgColor = note.color || '#fff9c4';
  const textColor = '#1a1a2e';
  const scaleAnim = useRef(new Animated.Value(1)).current;

  const handlePressIn = () => {
    Animated.spring(scaleAnim, { toValue: 0.98, useNativeDriver: false, friction: 8 }).start();
  };

  const handlePressOut = () => {
    Animated.spring(scaleAnim, { toValue: 1, useNativeDriver: false, friction: 8 }).start();
  };

  const rawContent = note.content || '';
  const cleanContent = stripMarkdown(rawContent);
  const lines = cleanContent.split('\n').filter(l => l.trim());
  const preview = lines.slice(0, 2).join('  ');

  return (
    <Pressable
      onPressIn={handlePressIn}
      onPressOut={handlePressOut}
      onPress={() => onPress?.(note)}
    >
      <Animated.View style={[styles.card, { backgroundColor: bgColor, transform: [{ scale: scaleAnim }] }]}>
        <View style={styles.row}>
          <View style={styles.textCol}>
            {note.title ? (
              <Text style={styles.title} numberOfLines={1}>
                {note.title}
              </Text>
            ) : null}
            {preview ? (
              <Text style={styles.preview} numberOfLines={2}>
                {preview}
              </Text>
            ) : (
              <Text style={styles.empty}>No content</Text>
            )}
          </View>
          <Text style={styles.date}>
            {new Date(note.updated_at || note.created_at).toLocaleDateString(undefined, {
              month: 'short',
              day: 'numeric',
            })}
          </Text>
          {onDelete && (
            <Pressable onPress={() => onDelete(note)} style={styles.deleteBtn}>
              <Ionicons name="close-circle" size={18} color="rgba(0,0,0,0.25)" />
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
    fontSize: 16,
    fontWeight: '600',
    color: '#1a1a2e',
  },
  preview: {
    fontSize: 13,
    color: 'rgba(26,26,46,0.6)',
    lineHeight: 18,
    marginTop: 2,
  },
  empty: {
    fontSize: 13,
    color: 'rgba(26,26,46,0.35)',
    fontStyle: 'italic',
    marginTop: 2,
  },
  date: {
    fontSize: 11,
    color: 'rgba(26,26,46,0.35)',
    marginRight: spacing.xs,
  },
  deleteBtn: {
    padding: 4,
  },
});
