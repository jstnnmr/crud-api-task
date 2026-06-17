# Mobile App Testing Guide (Laptop Only)

Test the EaseTask Expo/React Native app without a physical device.

---

## Prerequisites

```bash
cd mobile
npm install
```

Start the backend API (Laravel Vite dev server):

run herd and mysql


Update the API base URL in the app's config if needed.

---

## 1. Expo Web (Browser — Easiest)

Run the app directly in your browser. `react-native-web` is already included.

```bash
npm run web --prefix "C:\Users\Justinne Marie Namoc\crud-api-task\mobile"
```

Opens at `http://localhost:8081`.

**Pros:** Zero setup, instant hot reload.
**Cons:** Native-only features (DateTimePicker, image picker, gestures) may not work.

---

## 2. Android Emulator

### Setup
1. Install [Android Studio](https://developer.android.com/studio).
2. Open **AVD Manager** → **Create Virtual Device** → pick a device (e.g., Pixel 6).
3. Select a system image (e.g., API 35) and finish.
4. Start the emulator from AVD Manager.

### Run
```bash
npm run android
```

Expo will detect the running emulator and install the app automatically.

**Pros:** Full native behavior.
**Cons:** Requires ~8GB disk space, heavier on RAM.

---

## 3. iOS Simulator (macOS Only)

### Setup
Xcode includes the iOS Simulator. Install from the Mac App Store if not present.

### Run
```bash
npm run ios
```

Expo will launch the iOS Simulator and install the app.

**Pros:** Full native behavior.
**Cons:** macOS only.

---

## Notes

- The backend must be running for API calls to work.
- If using a different API host (e.g., emulator accessing `10.0.2.2` instead of `localhost`), update the API base URL in `src/` config.
- For emulators, ensure the backend is accessible from the emulator's network.
