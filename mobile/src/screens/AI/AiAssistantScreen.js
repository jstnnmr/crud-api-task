import React, { useState, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  FlatList,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { spacing, borderRadius, typography } from '../../theme/colors';
import Card from '../../components/Card';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';

const SUGGESTIONS = [
  'What tasks are due today?',
  'Help me organize my subjects',
  'What should I prioritize?',
  'Give me productivity tips',
];

export default function AiAssistantScreen() {
  const { colors } = useTheme();
  const [messages, setMessages] = useState([
    { role: 'assistant', text: "Hi! I'm your AI assistant. How can I help you with your tasks today?" },
  ]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(false);
  const flatRef = useRef(null);

  const handleSend = async () => {
    if (!input.trim() || loading) return;
    const userMsg = { role: 'user', text: input.trim() };
    setMessages((prev) => [...prev, userMsg]);
    setInput('');
    setLoading(true);

    setTimeout(() => {
      setMessages((prev) => [
        ...prev,
        {
          role: 'assistant',
          text: "I'm processing your request. This is a demo response from the mobile app. Connect to the /api/ai/chat endpoint for full AI functionality.",
        },
      ]);
      setLoading(false);
    }, 1000);
  };

  const renderMsg = ({ item }) => (
    <View
      style={[
        styles.msgRow,
        item.role === 'user' && styles.userRow,
      ]}
    >
      {item.role === 'assistant' && (
        <View style={[styles.aiAvatar, { backgroundColor: colors.primary }]}>
          <Ionicons name="sparkles" size={18} color="#fff" />
        </View>
      )}
      <View
        style={[
          styles.msgBubble,
          item.role === 'user'
            ? { backgroundColor: colors.primary, borderBottomRightRadius: 4 }
            : { backgroundColor: colors.bgCard, borderBottomLeftRadius: 4 },
        ]}
      >
        <Text style={{ color: item.role === 'user' ? '#fff' : colors.text, fontSize: 15, lineHeight: 22 }}>
          {item.text}
        </Text>
      </View>
    </View>
  );

  return (
    <KeyboardAvoidingView
      style={[styles.container, { backgroundColor: colors.bg }]}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      keyboardVerticalOffset={90}
    >
      <LinearGradient colors={[colors.primary, colors.primaryDark]} style={styles.header}>
        <Text style={styles.title}>AI Assistant</Text>
        <Text style={styles.subtitle}>Ask me anything about your tasks</Text>
      </LinearGradient>

      <FlatList
        ref={flatRef}
        data={messages}
        renderItem={renderMsg}
        keyExtractor={(_, i) => String(i)}
        contentContainerStyle={styles.chat}
        onContentSizeChange={() => flatRef.current?.scrollToEnd()}
        ListHeaderComponent={
          messages.length === 1 ? (
            <View style={styles.suggestions}>
              <Text style={[typography.bodySmall, { color: colors.textMuted, marginBottom: 8 }]}>
                Try asking:
              </Text>
              {SUGGESTIONS.map((s, i) => (
                <TouchableOpacity
                  key={i}
                  onPress={() => setInput(s)}
                  style={[styles.suggestionChip, { backgroundColor: colors.bgInput, borderColor: colors.border }]}
                >
                  <Text style={{ color: colors.textSecondary, fontSize: 13 }}>{s}</Text>
                </TouchableOpacity>
              ))}
            </View>
          ) : null
        }
      />

      {loading && (
        <View style={[styles.typing, { backgroundColor: colors.bgCard }]}>
          <Text style={{ color: colors.textMuted, fontSize: 13 }}>AI is thinking...</Text>
        </View>
      )}

      <View style={[styles.inputBar, { backgroundColor: colors.bgLight, borderTopColor: colors.border }]}>
        <TextInput
          value={input}
          onChangeText={setInput}
          placeholder="Ask something..."
          placeholderTextColor={colors.textMuted}
          style={[
            styles.chatInput,
            { backgroundColor: colors.bgInput, borderColor: colors.border, color: colors.text },
          ]}
          multiline
        />
        <TouchableOpacity
          onPress={handleSend}
          disabled={!input.trim() || loading}
          style={[styles.sendBtn, { backgroundColor: input.trim() ? colors.primary : colors.textMuted }]}
        >
          <Ionicons name="send" size={18} color="#fff" />
        </TouchableOpacity>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  header: { padding: spacing.lg, paddingTop: 60, paddingBottom: 30 },
  title: { fontSize: 28, fontWeight: '700', color: '#fff' },
  subtitle: { fontSize: 15, color: 'rgba(255,255,255,0.7)', marginTop: 2 },
  chat: { padding: spacing.md, flexGrow: 1 },
  msgRow: { flexDirection: 'row', marginBottom: 16, alignItems: 'flex-end' },
  userRow: { justifyContent: 'flex-end' },
  aiAvatar: {
    width: 32, height: 32, borderRadius: 16,
    alignItems: 'center', justifyContent: 'center', marginRight: 8,
  },
  msgBubble: {
    maxWidth: '80%',
    padding: 12,
    borderRadius: 16,
  },
  suggestions: {
    marginBottom: 16,
  },
  suggestionChip: {
    paddingHorizontal: 16, paddingVertical: 10,
    borderRadius: borderRadius.full,
    borderWidth: 1,
    marginBottom: 8,
  },
  typing: {
    paddingHorizontal: spacing.md,
    paddingVertical: 8,
  },
  inputBar: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    padding: spacing.sm,
    borderTopWidth: 1,
    gap: 8,
  },
  chatInput: {
    flex: 1,
    borderRadius: borderRadius.md,
    borderWidth: 1,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: 15,
    maxHeight: 100,
  },
  sendBtn: {
    width: 44, height: 44, borderRadius: 22,
    alignItems: 'center', justifyContent: 'center',
  },
});
