import React from 'react';
import { View, TouchableOpacity, StyleSheet } from 'react-native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { useTheme } from '../context/ThemeContext';
import { Ionicons } from '@expo/vector-icons';

import DashboardScreen from '../screens/Dashboard/DashboardScreen';
import SubjectsScreen from '../screens/Subjects/SubjectsScreen';
import SubjectDetailScreen from '../screens/Subjects/SubjectDetailScreen';
import SubjectFormScreen from '../screens/Subjects/SubjectFormScreen';
import TaskFormScreen from '../screens/Subjects/TaskFormScreen';
import TaskDetailScreen from '../screens/TaskDetail/TaskDetailScreen';
import NotesScreen from '../screens/Notes/NotesScreen';
import NoteDetailScreen from '../screens/Notes/NoteDetailScreen';
import TasksScreen from '../screens/Tasks/TasksScreen';
import TeamScreen from '../screens/Team/TeamScreen';
import AccountScreen from '../screens/Account/AccountScreen';
import EditProfileScreen from '../screens/Account/EditProfileScreen';
import ChangePasswordScreen from '../screens/Account/ChangePasswordScreen';
import ProductivityScreen from '../screens/Productivity/ProductivityScreen';
import AiAssistantScreen from '../screens/AI/AiAssistantScreen';

const Tab = createBottomTabNavigator();
const Stack = createNativeStackNavigator();

function DashboardStack() {
  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      <Stack.Screen name="DashboardHome" component={DashboardScreen} />
      <Stack.Screen name="TaskDetail" component={TaskDetailScreen} />
      <Stack.Screen name="TaskForm" component={TaskFormScreen} />
    </Stack.Navigator>
  );
}

function NotesStack() {
  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      <Stack.Screen name="NotesHome" component={NotesScreen} />
      <Stack.Screen name="NoteDetail" component={NoteDetailScreen} />
    </Stack.Navigator>
  );
}

function AccountStack() {
  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      <Stack.Screen name="AccountHome" component={AccountScreen} />
      <Stack.Screen name="EditProfile" component={EditProfileScreen} />
      <Stack.Screen name="ChangePassword" component={ChangePasswordScreen} />
      <Stack.Screen name="Productivity" component={ProductivityScreen} />
    </Stack.Navigator>
  );
}

function TaskStack() {
  return (
    <Stack.Navigator screenOptions={{ headerShown: false }}>
      <Stack.Screen name="TasksHome" component={TasksScreen} />
      <Stack.Screen name="SubjectDetail" component={SubjectDetailScreen} />
      <Stack.Screen name="SubjectForm" component={SubjectFormScreen} />
      <Stack.Screen name="TaskDetail" component={TaskDetailScreen} />
      <Stack.Screen name="TaskForm" component={TaskFormScreen} />
      <Stack.Screen name="TeamHome" component={TeamScreen} />
    </Stack.Navigator>
  );
}

function AITabButton({ children, onPress, accessibilityState }) {
  const focused = accessibilityState?.selected;
  const { colors } = useTheme();

  return (
    <TouchableOpacity
      onPress={onPress}
      activeOpacity={0.8}
      style={styles.aiButtonWrap}
    >
      <View
        style={[
          styles.aiButton,
          { backgroundColor: colors.primary },
          focused && styles.aiButtonFocused,
        ]}
      >
        {children}
      </View>
    </TouchableOpacity>
  );
}

export default function TabNavigator() {
  const { colors } = useTheme();

  const getTabIcon = (routeName, focused, size) => {
    const icons = {
      DashboardTab: { focused: 'home', unfocused: 'home-outline' },
      TaskTab: { focused: 'checkbox', unfocused: 'checkbox-outline' },
      AITab: { focused: 'sparkles', unfocused: 'sparkles' },
      NotesTab: { focused: 'document-text', unfocused: 'document-text-outline' },
      ProfileTab: { focused: 'person', unfocused: 'person-outline' },
    };
    const icon = icons[routeName];
    const iconName = focused ? icon.focused : icon.unfocused;
    return (
      <Ionicons
        name={iconName}
        size={routeName === 'AITab' ? 28 : size}
        color={routeName === 'AITab' ? '#fff' : focused ? colors.primary : colors.textMuted}
      />
    );
  };

  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        headerShown: false,
        tabBarIcon: ({ focused, size }) => getTabIcon(route.name, focused, size),
        tabBarActiveTintColor: colors.primary,
        tabBarInactiveTintColor: colors.textMuted,
        tabBarStyle: {
          backgroundColor: colors.bgLight + 'dd',
          borderTopColor: colors.border,
          borderTopWidth: 1,
          paddingTop: 8,
          paddingBottom: 10,
          height: 65,
          elevation: 8,
          shadowColor: '#000',
          shadowOffset: { width: 0, height: -4 },
          shadowOpacity: 0.3,
          shadowRadius: 12,
        },
        tabBarLabelStyle: {
          fontSize: 10,
          fontWeight: '700',
          letterSpacing: 0.3,
        },
      })}
    >
      <Tab.Screen
        name="DashboardTab"
        component={DashboardStack}
        options={{ tabBarLabel: 'Dashboard' }}
      />
      <Tab.Screen
        name="TaskTab"
        component={TaskStack}
        options={{ tabBarLabel: 'Tasks' }}
      />
      <Tab.Screen
        name="AITab"
        component={AiAssistantScreen}
        options={{
          tabBarLabel: '',
          tabBarButton: (props) => <AITabButton {...props} />,
        }}
      />
      <Tab.Screen
        name="NotesTab"
        component={NotesStack}
        options={{ tabBarLabel: 'Notes' }}
      />
      <Tab.Screen
        name="ProfileTab"
        component={AccountStack}
        options={{ tabBarLabel: 'Profile' }}
      />
    </Tab.Navigator>
  );
}

const styles = StyleSheet.create({
  aiButtonWrap: {
    top: -14,
    justifyContent: 'center',
    alignItems: 'center',
  },
  aiButton: {
    width: 56,
    height: 56,
    borderRadius: 28,
    alignItems: 'center',
    justifyContent: 'center',
    elevation: 8,
    shadowColor: '#8e7dff',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.4,
    shadowRadius: 8,
  },
  aiButtonFocused: {
    transform: [{ scale: 1.08 }],
  },
});
