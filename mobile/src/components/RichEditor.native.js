import React, { useRef, useCallback, forwardRef, useImperativeHandle } from 'react';
import { View, StyleSheet } from 'react-native';
import { WebView } from 'react-native-webview';

const EDITOR_HTML = `<!DOCTYPE html><html><head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: -apple-system, sans-serif; font-size: 16px; line-height: 1.7; color: #1a1a2e; padding: 4px; }
  #editor { min-height: 280px; outline: none; }
  #editor:empty:before { content: attr(data-placeholder); color: rgba(26,26,46,0.35); }
</style></head><body>
<div id="editor" contenteditable="true" data-placeholder="Start writing..."></div>
<script>
  var editor = document.getElementById('editor');
  editor.addEventListener('input', function() {
    window.ReactNativeWebView.postMessage(JSON.stringify({ type: 'content', html: editor.innerHTML }));
  });
  window.addEventListener('message', function(event) {
    try {
      var data = JSON.parse(event.data);
      if (data.type === 'exec') {
        document.execCommand(data.command, false, data.value || null);
        window.ReactNativeWebView.postMessage(JSON.stringify({ type: 'content', html: editor.innerHTML }));
      } else if (data.type === 'setContent') {
        editor.innerHTML = data.html;
      } else if (data.type === 'focus') {
        editor.focus();
      }
    } catch(e) {}
  });
  window.ReactNativeWebView.postMessage(JSON.stringify({ type: 'ready' }));
</script></body></html>`;

const RichEditor = forwardRef(({ initialContent, onContentChange, style }, ref) => {
  const webViewRef = useRef(null);
  const readyRef = useRef(false);
  const pendingContent = useRef(initialContent);

  useImperativeHandle(ref, () => ({
    exec: (command, value = null) => {
      webViewRef.current?.postMessage(JSON.stringify({ type: 'exec', command, value }));
    },
    setContent: (html) => {
      if (readyRef.current) {
        webViewRef.current?.postMessage(JSON.stringify({ type: 'setContent', html }));
      } else {
        pendingContent.current = html;
      }
    },
    focus: () => {
      webViewRef.current?.postMessage(JSON.stringify({ type: 'focus' }));
    },
  }));

  const handleMessage = useCallback((event) => {
    try {
      const data = JSON.parse(event.nativeEvent.data);
      if (data.type === 'ready') {
        readyRef.current = true;
        if (pendingContent.current) {
          webViewRef.current?.postMessage(JSON.stringify({ type: 'setContent', html: pendingContent.current }));
          pendingContent.current = null;
        }
      } else if (data.type === 'content') {
        onContentChange?.(data.html);
      }
    } catch (e) {}
  }, [onContentChange]);

  return (
    <View style={[styles.container, style]}>
      <WebView
        ref={webViewRef}
        source={{ html: EDITOR_HTML }}
        onMessage={handleMessage}
        style={styles.webview}
        originWhitelist={['*']}
        javaScriptEnabled={true}
        scrollEnabled={false}
        showsVerticalScrollIndicator={false}
        bounces={false}
        keyboardDisplayRequiresUserAction={false}
        hideKeyboardAccessoryView={false}
      />
    </View>
  );
});

const styles = StyleSheet.create({
  container: { flex: 1, minHeight: 280 },
  webview: { flex: 1, backgroundColor: 'transparent' },
});

export default RichEditor;
