import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
import Loading from '../components/Loading';

import LoginScreen from '../screens/Auth/LoginScreen';
import RegisterScreen from '../screens/Auth/RegisterScreen';
import VerifyEmailScreen from '../screens/Auth/VerifyEmailScreen';
import ForgotPasswordScreen from '../screens/Auth/ForgotPasswordScreen';
import TabNavigator from './TabNavigator';

const Stack = createNativeStackNavigator();

export default function AppNavigator() {
  const { isAuthenticated, loading, user } = useAuth();
  const { colors, isDark } = useTheme();

  if (loading) {
    return <Loading />;
  }

  return (
    <NavigationContainer
      theme={{
        dark: isDark,
        colors: {
          primary: colors.primary,
          background: colors.bg,
          card: colors.bgLight,
          text: colors.text,
          border: colors.border,
          notification: colors.secondary,
        },
        fonts: {
          regular: { fontFamily: 'DM Sans', fontWeight: '400' },
          medium: { fontFamily: 'DM Sans', fontWeight: '500' },
          bold: { fontFamily: 'DM Sans', fontWeight: '700' },
          heavy: { fontFamily: 'DM Sans', fontWeight: '800' },
        },
      }}
    >
      <Stack.Navigator
        screenOptions={{
          headerShown: false,
          animation: 'fade',
          animationDuration: 200,
        }}
      >
        {isAuthenticated ? (
          <>
            {user && !user.email_verified_at ? (
              <Stack.Screen name="VerifyEmail" component={VerifyEmailScreen} />
            ) : (
              <Stack.Screen name="Main" component={TabNavigator} />
            )}
          </>
        ) : (
          <>
            <Stack.Screen name="Login" component={LoginScreen} />
            <Stack.Screen name="Register" component={RegisterScreen} />
            <Stack.Screen name="ForgotPassword" component={ForgotPasswordScreen} />
          </>
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
}
