import React, { useState, useRef, forwardRef, useImperativeHandle } from 'react';
import { TextInput, StyleSheet } from 'react-native';

function stripTags(html) {
  return html.replace(/<[^>]*>/g, '').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').trim();
}

const RichEditor = forwardRef(({ initialContent, onContentChange, style }, ref) => {
  const [text, setText] = useState(stripTags(initialContent));
  const selRef = useRef({ start: 0, end: 0 });

  useImperativeHandle(ref, () => ({
    exec: (command, value = null) => {
      const before = text.substring(0, selRef.current.start);
      const selected = text.substring(selRef.current.start, selRef.current.end);
      const after = text.substring(selRef.current.end);

      let newText;
      if (command === 'formatBlock') {
        const prefix = value === 'h1' ? '# ' : value === 'h2' ? '## ' : '### ';
        newText = before + prefix + (selected || '') + after;
      } else if (command === 'bold') {
        newText = before + '**' + (selected || 'bold') + '**' + after;
      } else if (command === 'italic') {
        newText = before + '*' + (selected || 'italic') + '*' + after;
      } else if (command === 'underline') {
        newText = before + '<u>' + (selected || 'text') + '</u>' + after;
      } else if (command === 'strikeThrough') {
        newText = before + '~~' + (selected || 'text') + '~~' + after;
      } else if (command === 'insertUnorderedList') {
        newText = before + '\n- ' + (selected || '') + after;
      } else if (command === 'insertOrderedList') {
        newText = before + '\n1. ' + (selected || '') + after;
      } else if (command === 'insertHTML') {
        newText = before + '`' + (selected || 'code') + '`' + after;
      } else if (command === 'createLink') {
        const url = value || 'https://';
        newText = before + '[' + (selected || 'link') + '](' + url + ')' + after;
      } else if (command === 'removeFormat') {
        newText = text
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
          .replace(/\[([^\]]+)\]\([^)]+\)/g, '$1');
      } else {
        return;
      }
      setText(newText);
      onContentChange?.(newText);
    },
    setContent: (html) => {
      setText(stripTags(html));
    },
    focus: () => {},
  }));

  const handleChange = (val) => {
    setText(val);
    onContentChange?.(val);
  };

  return (
    <TextInput
      value={text}
      onChangeText={handleChange}
      onSelectionChange={(e) => { selRef.current = e.nativeEvent.selection; }}
      placeholder="Start writing..."
      placeholderTextColor="rgba(26,26,46,0.3)"
      style={[styles.input, style]}
      multiline
      textAlignVertical="top"
    />
  );
});

const styles = StyleSheet.create({
  input: { fontSize: 16, color: '#1a1a2e', lineHeight: 24, minHeight: 300 },
});

export default RichEditor;
