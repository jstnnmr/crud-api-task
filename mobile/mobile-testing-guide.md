# Mobile App Testing Guide

Test the EaseTask Expo/React Native app on emulator, simulator, or a physical device.

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

## 3. Android Physical Device

### Setup
1. On your Android device, go to **Settings → About Phone** and tap **Build Number** 7 times to enable Developer Options.
2. Go to **Settings → Developer Options** and enable **USB Debugging**.
3. Connect the device via USB and run:
   ```bash
   adb devices
   ```
   Accept the debugging prompt on the device if shown. Verify the device is listed.
4. Install the **Expo Go** app from the Google Play Store.

### Run (USB)
```bash
npm run android
```

Expo will install the app on the connected device automatically.

### Run (QR Code / LAN)
```bash
npx expo start
```

Open Expo Go on the device and scan the QR code shown in the terminal. Both devices must be on the same Wi-Fi network.

### Run (Tunnel — No LAN Required)
If devices are on different networks, use Expo's tunnel:
```bash
npx expo start --tunnel
```

This routes through Expo's servers, so it works over the internet but may be slower.

**Pros:** Real device behavior (camera, GPS, sensors, performance), no emulator overhead.
**Cons:** Requires a physical Android device and USB cable (or LAN).

> **Tip:** If the device doesn't show up with `adb devices`, install the appropriate USB driver for your device manufacturer.

---

## 4. iOS Simulator (macOS Only)

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
