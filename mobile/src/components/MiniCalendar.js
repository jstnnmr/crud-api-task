import React, { useState, useMemo } from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { useTheme } from '../context/ThemeContext';
import { borderRadius, spacing, fonts } from '../theme/colors';
import { Ionicons } from '@expo/vector-icons';

const DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

export default function MiniCalendar({ tasks = [], onDatePress }) {
  const { colors } = useTheme();
  const today = useMemo(() => new Date(), []);
  const [currentMonth, setCurrentMonth] = useState(today.getMonth());
  const [currentYear, setCurrentYear] = useState(today.getFullYear());

  const taskDates = useMemo(() => {
    const dates = {};
    (tasks || []).forEach((t) => {
      if (t.due_date) {
        const key = t.due_date.slice(0, 10);
        if (!dates[key]) dates[key] = 0;
        dates[key]++;
      }
    });
    return dates;
  }, [tasks]);

  const calendar = useMemo(() => {
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    const weeks = [];
    let week = new Array(firstDay).fill(null);
    for (let d = 1; d <= daysInMonth; d++) {
      week.push(d);
      if (week.length === 7) {
        weeks.push(week);
        week = [];
      }
    }
    if (week.length > 0) {
      while (week.length < 7) week.push(null);
      weeks.push(week);
    }
    return weeks;
  }, [currentMonth, currentYear]);

  const prevMonth = () => {
    if (currentMonth === 0) {
      setCurrentMonth(11);
      setCurrentYear((y) => y - 1);
    } else {
      setCurrentMonth((m) => m - 1);
    }
  };

  const nextMonth = () => {
    if (currentMonth === 11) {
      setCurrentMonth(0);
      setCurrentYear((y) => y + 1);
    } else {
      setCurrentMonth((m) => m + 1);
    }
  };

  const pad = (n) => String(n).padStart(2, '0');
  const todayStr = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;

  return (
    <View style={[styles.card, { backgroundColor: colors.bgCard, borderColor: colors.border }]}>
      <View style={styles.header}>
        <TouchableOpacity onPress={prevMonth} style={styles.arrow}>
          <Ionicons name="chevron-back" size={18} color={colors.textSecondary} />
        </TouchableOpacity>
        <Text style={[styles.monthLabel, { color: colors.text }]}>
          {MONTHS[currentMonth]} {currentYear}
        </Text>
        <TouchableOpacity onPress={nextMonth} style={styles.arrow}>
          <Ionicons name="chevron-forward" size={18} color={colors.textSecondary} />
        </TouchableOpacity>
      </View>

      <View style={styles.weekDays}>
        {DAYS.map((d) => (
          <Text key={d} style={[styles.weekDay, { color: colors.textMuted }]}>{d}</Text>
        ))}
      </View>

      {calendar.map((week, wi) => (
        <View key={wi} style={styles.week}>
          {week.map((day, di) => {
            if (day === null) return <View key={`e-${di}`} style={styles.dayCell} />;
            const dateStr = `${currentYear}-${pad(currentMonth + 1)}-${pad(day)}`;
            const isToday = dateStr === todayStr;
            const hasTask = taskDates[dateStr];
            const isPast = new Date(currentYear, currentMonth, day) < new Date(today.getFullYear(), today.getMonth(), today.getDate());

            return (
              <TouchableOpacity
                key={day}
                onPress={() => onDatePress?.(dateStr)}
                style={styles.dayCell}
              >
                <View
                  style={[
                    styles.dayInner,
                    isToday && {
                      backgroundColor: colors.primary + (colors.bg === '#121513' ? '30' : '20'),
                      borderColor: colors.primary,
                      borderWidth: 1.5,
                    },
                  ]}
                >
                  <Text
                    style={[
                      styles.dayText,
                      { color: isToday ? colors.primary : isPast ? colors.textMuted + '80' : colors.text },
                      isToday && { fontFamily: fonts.uiBold },
                    ]}
                  >
                    {day}
                  </Text>
                  {hasTask && (
                    <View style={[styles.dot, { backgroundColor: colors.primary }]} />
                  )}
                </View>
              </TouchableOpacity>
            );
          })}
        </View>
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    marginHorizontal: spacing.md,
    marginBottom: spacing.md,
    borderRadius: borderRadius.lg,
    borderWidth: 1,
    padding: spacing.md,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  arrow: {
    width: 32,
    height: 32,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
  },
  monthLabel: {
    fontFamily: fonts.uiBold,
    fontSize: 16,
  },
  weekDays: {
    flexDirection: 'row',
    marginBottom: 4,
  },
  weekDay: {
    flex: 1,
    textAlign: 'center',
    fontFamily: fonts.uiMedium,
    fontSize: 11,
    paddingVertical: 4,
  },
  week: {
    flexDirection: 'row',
  },
  dayCell: {
    flex: 1,
    alignItems: 'center',
    paddingVertical: 4,
  },
  dayInner: {
    width: 32,
    height: 32,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
  },
  dayText: {
    fontFamily: fonts.uiMedium,
    fontSize: 13,
  },
  dot: {
    width: 4,
    height: 4,
    borderRadius: 2,
    position: 'absolute',
    bottom: 3,
  },
});
