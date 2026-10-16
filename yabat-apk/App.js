import React, { useEffect } from 'react';
import { StatusBar, Platform, View, StyleSheet, Text, ActivityIndicator } from 'react-native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { 
  useFonts,
  PlusJakartaSans_400Regular,
  PlusJakartaSans_500Medium,
  PlusJakartaSans_600SemiBold,
  PlusJakartaSans_700Bold,
  PlusJakartaSans_800ExtraBold
} from '@expo-google-fonts/plus-jakarta-sans';
import { AuthProvider } from './src/context/AuthContext';
import AppNavigator from './src/navigation/AppNavigator';

export default function App() {
  const [fontsLoaded] = useFonts({
    'PlusJakartaSans-Regular': PlusJakartaSans_400Regular,
    'PlusJakartaSans-Medium': PlusJakartaSans_500Medium,
    'PlusJakartaSans-SemiBold': PlusJakartaSans_600SemiBold,
    'PlusJakartaSans-Bold': PlusJakartaSans_700Bold,
    'PlusJakartaSans-ExtraBold': PlusJakartaSans_800ExtraBold,
  });

  // Terapkan font default ke seluruh komponen Text (Android, iOS & Web)
  useEffect(() => {
    if (Text.defaultProps == null) {
      Text.defaultProps = {};
    }
    Text.defaultProps.style = { fontFamily: 'PlusJakartaSans-Regular', ...(Text.defaultProps.style || {}) };
  }, []);

  if (!fontsLoaded) {
    return (
      <View style={{ flex: 1, backgroundColor: '#090d16', alignItems: 'center', justifyContent: 'center' }}>
        <ActivityIndicator size="large" color="#2563eb" />
      </View>
    );
  }

  const content = (
    <SafeAreaProvider>
      <StatusBar 
        barStyle="light-content" 
        backgroundColor="#090d16" 
        translucent={true} 
      />
      <AuthProvider>
        <AppNavigator />
      </AuthProvider>
    </SafeAreaProvider>
  );

  // Jika dibuka di browser web, kunci layar tepat seukuran HP (Mockup Mobile Viewport)
  if (Platform.OS === 'web') {
    return (
      <View style={styles.webOuter}>
        <View style={styles.webContainer}>
          {content}
        </View>
      </View>
    );
  }

  return content;
}

const styles = StyleSheet.create({
  webOuter: {
    flex: 1,
    backgroundColor: '#05070c',
    alignItems: 'center',
    justifyContent: 'center',
    width: '100%',
    height: '100%',
    minHeight: '100vh',
  },
  webContainer: {
    width: '100%',
    maxWidth: 430, // Standar lebar layar smartphone (iPhone / Android modern)
    height: '100%',
    maxHeight: 932,
    backgroundColor: '#090d16',
    overflow: 'hidden',
    boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.7)',
    borderLeftWidth: 1,
    borderRightWidth: 1,
    borderColor: '#1e293b',
  },
});
