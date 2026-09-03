import React, { useRef, useState } from 'react';
import { View, Text, StyleSheet, Pressable, Animated, Modal } from 'react-native';
import { useTheme } from '../context/ThemeContext';
import { borderRadius, spacing, fonts } from '../theme/colors';
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

  return (
    <>
      <Pressable
        onPressIn={handlePressIn}
        onPressOut={handlePressOut}
        onPress={() => onPress?.(task)}
        onLongPress={() => setMenuVisible(true)}
        delayLongPress={400}
        accessibilityLabel={task.title}
      >
        <Animated.View
          style={[
            styles.task,
            {
              backgroundColor: colors.bgCard,
              borderColor: colors.border,
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
                  borderColor: isCompleted ? colors.success : colors.border,
                  backgroundColor: isCompleted ? colors.success + '22' : 'transparent',
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
                { color: isCompleted ? colors.textMuted : colors.text },
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
              <View
                style={[
                  styles.priorityDot,
                  { backgroundColor: priorityColor },
                ]}
              />
              <Text style={[styles.priorityLabel, { color: priorityColor }]}>
                {task.priority}
              </Text>
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
                +{task.points_earned} pts
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
    borderRadius: borderRadius.lg,
    padding: spacing.md,
    borderWidth: 1,
    marginBottom: spacing.sm,
  },
  checkWrap: {
    padding: 6,
    marginRight: spacing.xs,
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
    fontFamily: fonts.uiSemiBold,
    fontSize: 15,
  },
  completedText: {
    textDecorationLine: 'line-through',
  },
  desc: {
    fontFamily: fonts.uiRegular,
    fontSize: 13,
    marginTop: 2,
    lineHeight: 18,
  },
  meta: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 6,
    gap: 6,
  },
  priorityDot: {
    width: 7,
    height: 7,
    borderRadius: 3.5,
  },
  priorityLabel: {
    fontFamily: fonts.uiMedium,
    fontSize: 11,
    textTransform: 'capitalize',
  },
  date: {
    fontFamily: fonts.uiRegular,
    fontSize: 11,
    marginLeft: 6,
  },
  right: {
    alignItems: 'flex-end',
    marginLeft: spacing.sm,
    gap: 4,
  },
  points: {
    fontFamily: fonts.uiSemiBold,
    fontSize: 11,
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
    fontFamily: fonts.uiBold,
    fontSize: 16,
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
    fontFamily: fonts.uiMedium,
    fontSize: 15,
  },
});
