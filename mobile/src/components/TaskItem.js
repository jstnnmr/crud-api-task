import React, { useRef, useState } from 'react';
import { View, Text, StyleSheet, Pressable, Animated, Modal } from 'react-native';
import { useTheme } from '../context/ThemeContext';
import { borderRadius, spacing } from '../theme/colors';
import Badge from './Badge';
import { Ionicons } from '@expo/vector-icons';

export default function TaskItem({ task, onPress, onComplete, compact }) {
  const { colors } = useTheme();
  const scaleAnim = useRef(new Animated.Value(1)).current;
  const [menuVisible, setMenuVisible] = useState(false);

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

  const isCompleted = task.status === 'completed';

  const handlePressIn = () => {
    Animated.spring(scaleAnim, { toValue: 0.98, useNativeDriver: false, friction: 8 }).start();
  };

  const handlePressOut = () => {
    Animated.spring(scaleAnim, { toValue: 1, useNativeDriver: false, friction: 8 }).start();
  };

  const handleLongPress = () => {
    setMenuVisible(true);
  };

  return (
    <>
      <Pressable
        onPressIn={handlePressIn}
        onPressOut={handlePressOut}
        onPress={() => onPress?.(task)}
        onLongPress={handleLongPress}
        delayLongPress={400}
      >
        <Animated.View
          style={[
            styles.task,
            {
              backgroundColor: colors.bgCard,
              borderColor: colors.border,
              borderLeftColor: priorityColor,
              transform: [{ scale: scaleAnim }],
            },
            isCompleted && { opacity: 0.55 },
          ]}
        >
          <View style={styles.checkWrap}>
            <View
              style={[
                styles.check,
                {
                  borderColor: isCompleted ? colors.success : colors.textMuted + '60',
                  backgroundColor: isCompleted ? colors.success + '20' : 'transparent',
                },
              ]}
            >
              {isCompleted ? (
                <Ionicons name="checkmark" size={16} color={colors.success} />
              ) : null}
            </View>
          </View>
          <View style={styles.content}>
            <Text
              style={[
                styles.title,
                { color: colors.text },
                isCompleted && styles.completedText,
              ]}
              numberOfLines={compact ? 1 : 2}
            >
              {task.title}
            </Text>
            {!compact && task.description && (
              <Text
                style={[styles.desc, { color: colors.textMuted }]}
                numberOfLines={2}
              >
                {task.description}
              </Text>
            )}
            <View style={styles.meta}>
              <Badge label={task.priority} color={priorityColor} size="sm" />
              {task.due_date && (
                <Text style={[styles.date, { color: colors.textMuted }]}>
                  {new Date(task.due_date).toLocaleDateString()}
                </Text>
              )}
            </View>
          </View>
          <View style={styles.right}>
            <Badge
              label={isCompleted ? 'done' : task.status.replace('_', ' ')}
              color={statusColor}
              size="sm"
            />
            {task.points_earned > 0 && (
              <Text style={[styles.points, { color: colors.accent }]}>
                +{task.points_earned}pts
              </Text>
            )}
          </View>
        </Animated.View>
      </Pressable>

      <Modal visible={menuVisible} transparent animationType="fade">
        <Pressable style={styles.overlay} onPress={() => setMenuVisible(false)}>
          <View
            style={[
              styles.menu,
              { backgroundColor: colors.bgCard, borderColor: colors.border },
            ]}
          >
            <Text style={[styles.menuTitle, { color: colors.text }]} numberOfLines={1}>
              {task.title}
            </Text>
            <View style={[styles.menuDivider, { backgroundColor: colors.border }]} />
            <Pressable
              style={styles.menuItem}
              onPress={() => { setMenuVisible(false); onPress?.(task); }}
            >
              <Ionicons name="eye-outline" size={20} color={colors.textSecondary} />
              <Text style={[styles.menuItemText, { color: colors.text }]}>View Details</Text>
            </Pressable>
            {!isCompleted && (
              <Pressable
                style={styles.menuItem}
                onPress={() => { setMenuVisible(false); onComplete?.(task); }}
              >
                <Ionicons name="checkmark-circle-outline" size={20} color={colors.success} />
                <Text style={[styles.menuItemText, { color: colors.success }]}>Mark Complete</Text>
              </Pressable>
            )}
          </View>
        </Pressable>
      </Modal>
    </>
  );
}

const styles = StyleSheet.create({
  task: {
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: borderRadius.md,
    padding: spacing.md,
    borderWidth: 1,
    borderLeftWidth: 4,
    marginBottom: spacing.sm,
  },
  checkWrap: {
    padding: 6,
    marginRight: 8,
  },
  check: {
    width: 28,
    height: 28,
    borderRadius: 14,
    borderWidth: 2,
    alignItems: 'center',
    justifyContent: 'center',
  },
  content: {
    flex: 1,
  },
  title: {
    fontSize: 15,
    fontWeight: '600',
  },
  completedText: {
    textDecorationLine: 'line-through',
  },
  desc: {
    fontSize: 13,
    marginTop: 2,
  },
  meta: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 6,
    gap: 8,
  },
  date: {
    fontSize: 11,
  },
  right: {
    alignItems: 'flex-end',
    marginLeft: 8,
    gap: 4,
  },
  points: {
    fontSize: 11,
    fontWeight: '700',
  },
  overlay: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(0,0,0,0.5)',
  },
  menu: {
    width: 260,
    borderRadius: borderRadius.lg,
    borderWidth: 1,
    padding: spacing.md,
  },
  menuTitle: {
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 4,
  },
  menuDivider: {
    height: 1,
    marginVertical: 10,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    paddingVertical: 12,
    paddingHorizontal: 4,
  },
  menuItemText: {
    fontSize: 15,
    fontWeight: '500',
  },
});
