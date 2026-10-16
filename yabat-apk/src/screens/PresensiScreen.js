import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  Animated
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import * as Location from 'expo-location';
import { useAuth } from '../context/AuthContext';
import MobileHeader from '../components/MobileHeader';
import api from '../api/client';
import { calculateHaversineDistance, formatTimeWIB } from '../utils/helpers';

export default function PresensiScreen() {
  const { employee } = useAuth();
  const [loading, setLoading] = useState(true);
  const [checkingGps, setCheckingGps] = useState(false);
  const [submittingIn, setSubmittingIn] = useState(false);
  const [submittingOut, setSubmittingOut] = useState(false);
  
  const [todayAttendance, setTodayAttendance] = useState(null);
  const [institution, setInstitution] = useState(null);

  // GPS State
  const [userCoords, setUserCoords] = useState(null);
  const [accuracy, setAccuracy] = useState(null);
  const [distance, setDistance] = useState(null);
  const [isWithinRadius, setIsWithinRadius] = useState(false);
  const [gpsError, setGpsError] = useState(null);

  // Toast feedback state
  const [toastMessage, setToastMessage] = useState(null);
  const toastAnim = useRef(new Animated.Value(0)).current;

  // Real-time Clock
  const [currentTime, setCurrentTime] = useState('');
  useEffect(() => {
    const timer = setInterval(() => {
      const now = new Date();
      setCurrentTime(
        `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`
      );
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  const showToast = (title, message, isSuccess = true) => {
    setToastMessage({ title, message, isSuccess });
    Animated.spring(toastAnim, {
      toValue: 1,
      useNativeDriver: true,
      tension: 60,
      friction: 8,
    }).start();

    setTimeout(() => {
      Animated.timing(toastAnim, {
        toValue: 0,
        duration: 250,
        useNativeDriver: true,
      }).start(() => setToastMessage(null));
    }, 3500);
  };

  // Fetch Attendance status from server
  const fetchStatus = async () => {
    try {
      const res = await api.get('/mobile/presensi-status');
      if (res.data.success) {
        setTodayAttendance(res.data.todayAttendance);
        setInstitution(res.data.institution);
      }
    } catch (err) {
      console.log('Error fetching presensi status:', err);
    } finally {
      setLoading(false);
    }
  };

  // Native GPS Check via expo-location
  const checkCurrentLocation = async () => {
    setCheckingGps(true);
    setGpsError(null);

    try {
      // 1. Cek / Minta Izin
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        setGpsError('Izin akses lokasi ditolak. Silakan izinkan akses lokasi di pengaturan HP Anda.');
        setCheckingGps(false);
        return;
      }

      // 2. Cek apakah layanan GPS aktif di HP. Jika mati, paksa munculkan dialog aktifkan GPS bawaan Android!
      const isEnabled = await Location.hasServicesEnabledAsync();
      if (!isEnabled) {
        try {
          await Location.enableNetworkProviderAsync();
        } catch (provErr) {
          Alert.alert(
            'GPS Wajib Aktif',
            'Sistem presensi memerlukan GPS aktif. Harap nyalakan Lokasi / GPS di pengaturan HP Anda.',
            [{ text: 'Buka Pengaturan', onPress: () => Location.enableNetworkProviderAsync().catch(() => {}) }]
          );
          setGpsError('GPS perangkat sedang tidak aktif. Harap aktifkan GPS / Lokasi perangkat Anda.');
          setCheckingGps(false);
          return;
        }
      }

      // 3. Ambil posisi GPS Native Akurasi Tinggi (dengan fallback balanced)
      let position;
      try {
        position = await Location.getCurrentPositionAsync({
          accuracy: Location.Accuracy.Highest,
          mayShowUserSettingsDialog: true,
          timeInterval: 1000,
        });
      } catch (highErr) {
        position = await Location.getLastKnownPositionAsync() || await Location.getCurrentPositionAsync({
          accuracy: Location.Accuracy.Balanced,
        });
      }

      if (!position || !position.coords) {
        setGpsError('Gagal memperoleh titik koordinat. Pastikan GPS menyala dan sinyal tersedia.');
        setCheckingGps(false);
        return;
      }

      const { latitude, longitude, accuracy: posAccuracy } = position.coords;
      const isMocked = position.mocked === true || position.coords.isMocked === true;

      // Deteksi Fake GPS langsung di perangkat
      if (isMocked) {
        setGpsError('Peringatan Keamanan: Terdeteksi penggunaan Fake GPS / Lokasi Tiruan! Harap nonaktifkan aplikasi Fake GPS.');
        setUserCoords(null);
        setCheckingGps(false);
        return;
      }

      setUserCoords({ latitude, longitude, isMocked });
      setAccuracy(posAccuracy ? Math.round(posAccuracy) : 10);

      // 4. Hitung Jarak ke Unit Institusi Pegawai
      const instLat = parseFloat(institution?.latitude ?? employee?.institution?.latitude);
      const instLng = parseFloat(institution?.longitude ?? employee?.institution?.longitude);
      const maxRadius = parseInt(institution?.radius_meters ?? employee?.institution?.radius_meters ?? 100, 10);

      if (isNaN(instLat) || isNaN(instLng)) {
        setGpsError('Titik koordinat unit kerja Anda belum diatur oleh Administrator. Harap hubungi Admin.');
        setIsWithinRadius(false);
        setDistance(null);
        setCheckingGps(false);
        return;
      }

      const calculatedDistance = calculateHaversineDistance(latitude, longitude, instLat, instLng);
      setDistance(calculatedDistance);

      if (calculatedDistance <= maxRadius) {
        setIsWithinRadius(true);
      } else {
        setIsWithinRadius(false);
      }
    } catch (err) {
      console.log('GPS error:', err);
      try {
        await Location.enableNetworkProviderAsync();
      } catch (e) {}
      setGpsError('Gagal mendeteksi sinyal GPS. Silakan pastikan izin Lokasi sudah diaktifkan.');
    } finally {
      setCheckingGps(false);
    }
  };

  // Selalu perbarui status dan cek lokasi otomatis tiap kali tab Presensi dibuka
  useFocusEffect(
    useCallback(() => {
      fetchStatus();
      checkCurrentLocation();
    }, [institution?.id])
  );

  // Handle Presensi Masuk
  const handleCheckIn = async () => {
    if (!userCoords) {
      Alert.alert('Periksa Lokasi', 'Silakan periksa titik GPS Anda terlebih dahulu.');
      return;
    }
    if (userCoords.isMocked) {
      Alert.alert('Akses Ditolak', 'Aplikasi mendeteksi lokasi tiruan (Fake GPS). Nonaktifkan Mock Location untuk presensi.');
      return;
    }
    if (!isWithinRadius) {
      Alert.alert('Di Luar Jangkauan', `Anda berada ${distance}m dari unit kerja. Batas radius adalah ${institution?.radius_meters || 100}m.`);
      return;
    }

    setSubmittingIn(true);
    try {
      const res = await api.post('/mobile/checkin', {
        latitude: userCoords.latitude,
        longitude: userCoords.longitude,
        is_mocked: userCoords.isMocked || false,
        notes: 'Presensi masuk dari React Native App',
      });

      if (res.data.success) {
        setTodayAttendance(res.data.data);
        showToast('Presensi Masuk Berhasil', 'Data kehadiran Anda telah tercatat valid di server yayasan.', true);
      } else {
        showToast('Presensi Gagal', res.data.message || 'Terjadi kesalahan sistem.', false);
      }
    } catch (err) {
      const msg = err.response?.data?.message || 'Gagal mengirim presensi masuk.';
      showToast('Presensi Gagal', msg, false);
    } finally {
      setSubmittingIn(false);
    }
  };

  // Handle Presensi Pulang
  const handleCheckOut = async () => {
    if (!userCoords) {
      Alert.alert('Periksa Lokasi', 'Silakan periksa titik GPS Anda terlebih dahulu.');
      return;
    }
    if (userCoords.isMocked) {
      Alert.alert('Akses Ditolak', 'Aplikasi mendeteksi lokasi tiruan (Fake GPS). Nonaktifkan Mock Location untuk presensi.');
      return;
    }
    if (!isWithinRadius) {
      Alert.alert('Di Luar Jangkauan', `Anda berada ${distance}m dari unit kerja. Batas radius adalah ${institution?.radius_meters || 100}m.`);
      return;
    }

    setSubmittingOut(true);
    try {
      const res = await api.post('/mobile/checkout', {
        latitude: userCoords.latitude,
        longitude: userCoords.longitude,
        is_mocked: userCoords.isMocked || false,
      });

      if (res.data.success) {
        setTodayAttendance(res.data.data);
        showToast('Presensi Pulang Berhasil', 'Data checkout kepulangan telah tersimpan.', true);
      } else {
        showToast('Presensi Gagal', res.data.message || 'Terjadi kesalahan sistem.', false);
      }
    } catch (err) {
      const msg = err.response?.data?.message || 'Gagal mengirim presensi pulang.';
      showToast('Presensi Gagal', msg, false);
    } finally {
      setSubmittingOut(false);
    }
  };

  const hasCheckedIn = !!todayAttendance?.time_in;
  const hasCheckedOut = !!todayAttendance?.time_out;
  const maxRadius = institution?.radius_meters || employee?.institution?.radius_meters || 100;

  return (
    <View style={styles.container}>
      <MobileHeader employee={employee} />

      {/* Floating Modern Toast */}
      {toastMessage && (
        <Animated.View
          style={[
            styles.toast,
            {
              opacity: toastAnim,
              transform: [
                {
                  translateY: toastAnim.interpolate({
                    inputRange: [0, 1],
                    outputRange: [-20, 0],
                  }),
                },
              ],
            },
          ]}
        >
          <View style={[styles.toastIconBox, toastMessage.isSuccess ? styles.toastIconSuccess : styles.toastIconDanger]}>
            <Ionicons
              name={toastMessage.isSuccess ? 'checkmark-circle' : 'alert-circle'}
              size={20}
              color={toastMessage.isSuccess ? '#10b981' : '#ef4444'}
            />
          </View>
          <View style={styles.toastTextBox}>
            <Text style={styles.toastTitle}>{toastMessage.title}</Text>
            <Text style={styles.toastMessage}>{toastMessage.message}</Text>
          </View>
        </Animated.View>
      )}

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* 1. STATUS LOKASI GPS (SEDERHANA & JELAS) */}
        <View style={styles.simpleStatusCard}>
          <View style={styles.statusHeader}>
            <Ionicons 
              name={checkingGps ? 'sync' : userCoords ? (isWithinRadius ? 'checkmark-circle' : 'close-circle') : 'location'} 
              size={30} 
              color={checkingGps ? '#3b82f6' : userCoords ? (isWithinRadius ? '#10b981' : '#ef4444') : '#f59e0b'} 
            />
            <View style={{ flex: 1, marginLeft: 12 }}>
              <Text style={styles.statusTitle}>
                {checkingGps 
                  ? 'Mencari Posisi Anda...' 
                  : userCoords 
                    ? (isWithinRadius ? 'Lokasi Valid (Di Lokasi Kerja)' : 'Di Luar Lokasi Kampus/Sekolah') 
                    : 'Menunggu Sinyal Lokasi'}
              </Text>
              <Text style={styles.statusSubtitle}>
                {institution?.name || employee?.institution?.name || 'Unit Yayasan'}
              </Text>
            </View>
            <TouchableOpacity 
              onPress={checkCurrentLocation} 
              disabled={checkingGps}
              style={styles.refreshIconBtn}
            >
              <Ionicons name="refresh" size={20} color="#2563eb" />
            </TouchableOpacity>
          </View>

          {/* Indikator Sederhana Jarak */}
          <View style={[styles.distancePill, isWithinRadius ? styles.distancePillValid : styles.distancePillInvalid]}>
            <Text style={[styles.distancePillText, isWithinRadius ? styles.distancePillTextValid : styles.distancePillTextInvalid]}>
              {distance !== null ? `Jarak Anda: ${distance} meter (Batas: ${maxRadius}m)` : 'Sedang mendeteksi jarak...'}
            </Text>
          </View>

          {gpsError && (
            <View style={styles.errorNotice}>
              <Ionicons name="alert-circle" size={18} color="#dc2626" />
              <Text style={styles.errorNoticeText}>{gpsError}</Text>
            </View>
          )}
        </View>

        {/* 2. TOMBOL ABSEN RAKSASA (SATU SENTUHAN - COCOK UNTUK ORANG TUA) */}
        <View style={styles.bigButtonSection}>
          {!hasCheckedIn ? (
            /* BELUM ABSEN MASUK -> TOMBOL HIJAU BESAR */
            <TouchableOpacity
              style={[
                styles.hugeButton,
                isWithinRadius ? styles.hugeButtonIn : styles.hugeButtonDisabled,
              ]}
              onPress={handleCheckIn}
              disabled={!isWithinRadius || submittingIn}
              activeOpacity={0.8}
            >
              {submittingIn ? (
                <ActivityIndicator size="large" color="#ffffff" />
              ) : (
                <>
                  <View style={styles.hugeIconCircleIn}>
                    <Ionicons name="finger-print" size={54} color="#ffffff" />
                  </View>
                  <Text style={styles.hugeButtonTitle}>TEKAN UNTUK ABSEN MASUK</Text>
                  <Text style={styles.hugeButtonSubtitle}>
                    {isWithinRadius 
                      ? 'Posisi sudah sesuai, tekan tombol ini' 
                      : 'Mendekatlah ke lokasi sekolah/kampus'}
                  </Text>
                </>
              )}
            </TouchableOpacity>
          ) : !hasCheckedOut ? (
            /* SUDAH MASUK, BELUM PULANG -> TOMBOL ORANYE BESAR */
            <TouchableOpacity
              style={[
                styles.hugeButton,
                isWithinRadius ? styles.hugeButtonOut : styles.hugeButtonDisabled,
              ]}
              onPress={handleCheckOut}
              disabled={!isWithinRadius || submittingOut}
              activeOpacity={0.8}
            >
              {submittingOut ? (
                <ActivityIndicator size="large" color="#ffffff" />
              ) : (
                <>
                  <View style={styles.hugeIconCircleOut}>
                    <Ionicons name="log-out" size={54} color="#ffffff" />
                  </View>
                  <Text style={styles.hugeButtonTitle}>TEKAN UNTUK ABSEN PULANG</Text>
                  <Text style={styles.hugeButtonSubtitle}>
                    {isWithinRadius 
                      ? 'Tekan tombol ini saat jam kerja selesai' 
                      : 'Mendekatlah ke lokasi sekolah/kampus'}
                  </Text>
                </>
              )}
            </TouchableOpacity>
          ) : (
            /* SUDAH MASUK DAN SUDAH PULANG -> KARTU SELESAI */
            <View style={styles.completedCard}>
              <Ionicons name="checkmark-done-circle" size={64} color="#10b981" />
              <Text style={styles.completedTitle}>Absensi Hari Ini Selesai!</Text>
              <Text style={styles.completedSub}>Terima kasih atas dedikasi dan kerja keras Anda hari ini.</Text>
            </View>
          )}
        </View>

        {/* 3. RINGKASAN CATATAN HARI INI */}
        <View style={styles.simpleRecordCard}>
          <Text style={styles.recordHeader}>CATATAN ABSEN HARI INI</Text>
          <View style={styles.recordRow}>
            <View style={styles.recordCol}>
              <Text style={styles.recordLabel}>Jam Masuk</Text>
              <Text style={styles.recordValue}>
                {todayAttendance?.time_in ? todayAttendance.time_in.substring(0, 5) + ' WIB' : 'Belum Absen'}
              </Text>
            </View>
            <View style={styles.recordDivider} />
            <View style={styles.recordCol}>
              <Text style={styles.recordLabel}>Jam Pulang</Text>
              <Text style={styles.recordValue}>
                {todayAttendance?.time_out ? todayAttendance.time_out.substring(0, 5) + ' WIB' : 'Belum Absen'}
              </Text>
            </View>
          </View>
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 28,
  },
  card: {
    backgroundColor: '#ffffff',
    borderRadius: 18,
    padding: 16,
    borderWidth: 1,
    borderColor: 'rgba(226, 232, 240, 0.8)',
    marginBottom: 14,
    shadowColor: '#64748b',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 6,
    elevation: 2,
  },
  cardHeaderRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  cardLabel: {
    fontSize: 9.5,
    fontWeight: '700',
    color: '#94a3b8',
    letterSpacing: 0.8,
  },
  activeShiftBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ecfdf5',
    borderColor: '#a7f3d0',
    borderWidth: 1,
    borderRadius: 12,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  activeShiftDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: '#10b981',
    marginRight: 5,
  },
  activeShiftText: {
    fontSize: 10,
    fontWeight: '700',
    color: '#047857',
  },
  clockBox: {
    alignItems: 'center',
    paddingVertical: 6,
  },
  clockText: {
    fontSize: 34,
    fontWeight: '800',
    color: '#0f172a',
    fontFamily: 'monospace',
    letterSpacing: 1,
  },
  clockSubtext: {
    fontSize: 10,
    color: '#94a3b8',
    marginTop: 2,
  },
  shiftGrid: {
    flexDirection: 'row',
    gap: 10,
    borderTopWidth: 1,
    borderTopColor: '#f1f5f9',
    paddingTop: 10,
    marginTop: 8,
  },
  shiftBox: {
    flex: 1,
    backgroundColor: '#f8fafc',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#f1f5f9',
    padding: 10,
  },
  shiftBoxTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
  },
  dotCircle: {
    width: 6,
    height: 6,
    borderRadius: 3,
  },
  shiftBoxLabel: {
    fontSize: 9.5,
    color: '#64748b',
    fontWeight: '600',
  },
  shiftBoxTime: {
    fontSize: 13,
    fontWeight: '800',
    color: '#0f172a',
    fontFamily: 'monospace',
    marginTop: 2,
  },
  shiftBoxSub: {
    fontSize: 9,
    color: '#94a3b8',
    marginTop: 1,
  },
  summaryBadge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  summaryBadgePresent: {
    backgroundColor: '#ecfdf5',
    borderColor: '#a7f3d0',
    borderWidth: 1,
  },
  summaryBadgeEmpty: {
    backgroundColor: '#f1f5f9',
  },
  summaryBadgeText: {
    fontSize: 10,
    fontWeight: '700',
  },
  summaryBadgeTextPresent: {
    color: '#047857',
  },
  summaryBadgeTextEmpty: {
    color: '#64748b',
  },
  metricGrid: {
    flexDirection: 'row',
    gap: 10,
  },
  metricCard: {
    flex: 1,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(226, 232, 240, 0.8)',
    backgroundColor: 'rgba(248, 250, 252, 0.6)',
    padding: 12,
  },
  metricCardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 4,
  },
  metricLabel: {
    fontSize: 10.5,
    fontWeight: '600',
    color: '#64748b',
  },
  statusDot: {
    width: 7,
    height: 7,
    borderRadius: 3.5,
  },
  statusDotActive: {
    backgroundColor: '#10b981',
  },
  statusDotInactive: {
    backgroundColor: '#cbd5e1',
  },
  metricTime: {
    fontSize: 16,
    fontWeight: '800',
    fontFamily: 'monospace',
    marginBottom: 2,
  },
  metricTimeActive: {
    color: '#059669',
  },
  metricTimeInactive: {
    color: '#334155',
  },
  metricDesc: {
    fontSize: 9.5,
    color: '#94a3b8',
  },
  gpsTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  gpsIconContainer: {
    width: 28,
    height: 28,
    borderRadius: 8,
    backgroundColor: '#eff6ff',
    alignItems: 'center',
    justifyContent: 'center',
  },
  gpsTitle: {
    fontSize: 12,
    fontWeight: '700',
    color: '#0f172a',
  },
  gpsSubtitle: {
    fontSize: 9.5,
    color: '#94a3b8',
  },
  gpsBadge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 12,
    borderWidth: 1,
  },
  gpsBadgeGood: {
    backgroundColor: '#ecfdf5',
    borderColor: '#a7f3d0',
  },
  gpsBadgeWarning: {
    backgroundColor: '#fffbeb',
    borderColor: '#fde68a',
  },
  gpsBadgeDefault: {
    backgroundColor: '#f1f5f9',
    borderColor: '#e2e8f0',
  },
  gpsBadgeText: {
    fontSize: 10,
    fontWeight: '700',
  },
  gpsBadgeTextGood: {
    color: '#047857',
  },
  gpsBadgeTextWarning: {
    color: '#b45309',
  },
  gpsBadgeTextDefault: {
    color: '#64748b',
  },
  coordsBox: {
    backgroundColor: '#f8fafc',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#f1f5f9',
    padding: 10,
    marginBottom: 10,
  },
  coordRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 2,
  },
  coordLabel: {
    fontSize: 11,
    color: '#94a3b8',
  },
  coordValue: {
    fontSize: 11,
    fontWeight: '700',
    color: '#1e293b',
    fontFamily: 'monospace',
  },
  errorBox: {
    backgroundColor: '#fff1f2',
    borderColor: '#fecdd3',
    borderWidth: 1,
    borderRadius: 12,
    padding: 10,
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 10,
  },
  errorTitle: {
    color: '#9f1239',
    fontSize: 11,
    fontWeight: '700',
  },
  errorText: {
    color: '#e11d48',
    fontSize: 10,
    lineHeight: 14,
    marginTop: 1,
  },
  refreshGpsButton: {
    backgroundColor: '#2563eb',
    borderRadius: 12,
    paddingVertical: 11,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
  },
  refreshGpsButtonText: {
    color: '#ffffff',
    fontSize: 12,
    fontWeight: '700',
  },
  institutionName: {
    fontSize: 12,
    fontWeight: '700',
    color: '#1e293b',
    marginTop: 2,
  },
  radiusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 12,
    borderWidth: 1,
  },
  radiusBadgeValid: {
    backgroundColor: '#ecfdf5',
    borderColor: '#a7f3d0',
  },
  radiusBadgeInvalid: {
    backgroundColor: '#fff1f2',
    borderColor: '#fecdd3',
  },
  radiusBadgeWait: {
    backgroundColor: '#f1f5f9',
    borderColor: '#e2e8f0',
  },
  radiusBadgeText: {
    fontSize: 10,
    fontWeight: '700',
  },
  radiusBadgeTextValid: {
    color: '#047857',
  },
  radiusBadgeTextInvalid: {
    color: '#e11d48',
  },
  radiusBadgeTextWait: {
    color: '#64748b',
  },
  distanceGrid: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 2,
  },
  distanceBox: {
    flex: 1,
    backgroundColor: '#f8fafc',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#f1f5f9',
    padding: 10,
  },
  distanceBoxLabel: {
    fontSize: 9.5,
    color: '#94a3b8',
    fontWeight: '600',
  },
  distanceBoxValue: {
    fontSize: 14,
    fontWeight: '800',
    color: '#0f172a',
    fontFamily: 'monospace',
    marginTop: 2,
  },
  distanceBoxSub: {
    fontSize: 9,
    color: '#94a3b8',
    marginTop: 1,
  },
  guidanceBox: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 10,
    borderRadius: 12,
    borderWidth: 1,
    marginTop: 10,
  },
  guidanceBoxValid: {
    backgroundColor: '#f0fdf4',
    borderColor: '#bbf7d0',
  },
  guidanceBoxInvalid: {
    backgroundColor: '#fef2f2',
    borderColor: '#fecaca',
  },
  guidanceTitle: {
    fontSize: 11,
    fontWeight: '700',
  },
  guidanceText: {
    fontSize: 10,
    lineHeight: 14,
    marginTop: 1,
  },
  actionSection: {
    marginTop: 2,
  },
  actionButtonsRow: {
    flexDirection: 'row',
    gap: 10,
  },
  actionBtn: {
    flex: 1,
    borderRadius: 16,
    paddingVertical: 14,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  actionBtnActiveIn: {
    backgroundColor: '#2563eb',
  },
  actionBtnActiveOut: {
    backgroundColor: '#ea580c',
  },
  actionBtnDisabled: {
    backgroundColor: '#e2e8f0',
  },
  actionBtnText: {
    fontSize: 12,
    fontWeight: '800',
    marginTop: 4,
  },
  actionBtnTextActive: {
    color: '#ffffff',
  },
  actionBtnTextDisabled: {
    color: '#64748b',
  },
  actionBtnSub: {
    fontSize: 9,
    color: 'rgba(255, 255, 255, 0.85)',
    marginTop: 2,
  },
  toast: {
    position: 'absolute',
    top: 14,
    left: 20,
    right: 20,
    zIndex: 9999,
    backgroundColor: 'rgba(15, 23, 42, 0.96)',
    borderRadius: 16,
    padding: 12,
    flexDirection: 'row',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.3,
    shadowRadius: 10,
    elevation: 10,
    borderWidth: 1,
    borderColor: 'rgba(51, 65, 85, 0.8)',
  },
  toastIconBox: {
    width: 32,
    height: 32,
    borderRadius: 10,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 10,
  },
  toastIconSuccess: {
    backgroundColor: 'rgba(16, 185, 129, 0.15)',
  },
  toastIconDanger: {
    backgroundColor: 'rgba(239, 68, 68, 0.15)',
  },
  toastTextBox: {
    flex: 1,
  },
  toastTitle: {
    color: '#ffffff',
    fontSize: 12,
    fontWeight: '700',
  },
  toastMessage: {
    color: '#cbd5e1',
    fontSize: 10.5,
    marginTop: 1,
  },

  /* --- STYLES RAMAH ORANG TUA (SUPER SIMPLE) --- */
  simpleStatusCard: {
    backgroundColor: '#ffffff',
    borderRadius: 20,
    padding: 16,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    shadowColor: '#64748b',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 2,
  },
  statusHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 10,
  },
  statusTitle: {
    fontSize: 14,
    fontWeight: '800',
    color: '#0f172a',
  },
  statusSubtitle: {
    fontSize: 12,
    color: '#64748b',
    marginTop: 1,
  },
  refreshIconBtn: {
    padding: 8,
    borderRadius: 12,
    backgroundColor: '#eff6ff',
  },
  distancePill: {
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 10,
    alignItems: 'center',
  },
  distancePillValid: {
    backgroundColor: '#ecfdf5',
  },
  distancePillInvalid: {
    backgroundColor: '#fef2f2',
  },
  distancePillText: {
    fontSize: 12,
    fontWeight: '700',
  },
  distancePillTextValid: {
    color: '#047857',
  },
  distancePillTextInvalid: {
    color: '#b91c1c',
  },
  errorNotice: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fff1f2',
    padding: 10,
    borderRadius: 10,
    marginTop: 10,
    gap: 8,
  },
  errorNoticeText: {
    fontSize: 11,
    color: '#b91c1c',
    flex: 1,
  },

  /* Tombol Absen Raksasa Satu Sentuhan */
  bigButtonSection: {
    marginVertical: 10,
    alignItems: 'center',
  },
  hugeButton: {
    width: '100%',
    paddingVertical: 28,
    paddingHorizontal: 16,
    borderRadius: 24,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.2,
    shadowRadius: 10,
    elevation: 6,
  },
  hugeButtonIn: {
    backgroundColor: '#059669', // Hijau Tegas
  },
  hugeButtonOut: {
    backgroundColor: '#d97706', // Oranye Tegas
  },
  hugeButtonDisabled: {
    backgroundColor: '#94a3b8',
    opacity: 0.7,
  },
  hugeIconCircleIn: {
    width: 90,
    height: 90,
    borderRadius: 45,
    backgroundColor: 'rgba(255, 255, 255, 0.25)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  hugeIconCircleOut: {
    width: 90,
    height: 90,
    borderRadius: 45,
    backgroundColor: 'rgba(255, 255, 255, 0.25)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  hugeButtonTitle: {
    fontSize: 18,
    fontWeight: '900',
    color: '#ffffff',
    letterSpacing: 0.5,
    textAlign: 'center',
  },
  hugeButtonSubtitle: {
    fontSize: 13,
    color: 'rgba(255, 255, 255, 0.9)',
    marginTop: 4,
    textAlign: 'center',
    fontWeight: '500',
  },

  completedCard: {
    width: '100%',
    backgroundColor: '#ffffff',
    borderRadius: 24,
    padding: 24,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: '#a7f3d0',
  },
  completedTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: '#065f46',
    marginTop: 10,
  },
  completedSub: {
    fontSize: 12,
    color: '#64748b',
    textAlign: 'center',
    marginTop: 4,
  },

  /* Catatan Absen Hari Ini */
  simpleRecordCard: {
    backgroundColor: '#ffffff',
    borderRadius: 18,
    padding: 16,
    marginTop: 14,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  recordHeader: {
    fontSize: 10,
    fontWeight: '800',
    color: '#94a3b8',
    letterSpacing: 0.8,
    marginBottom: 12,
    textAlign: 'center',
  },
  recordRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  recordCol: {
    flex: 1,
    alignItems: 'center',
  },
  recordLabel: {
    fontSize: 11,
    color: '#64748b',
    marginBottom: 2,
  },
  recordValue: {
    fontSize: 15,
    fontWeight: '800',
    color: '#0f172a',
  },
  recordDivider: {
    width: 1,
    height: 30,
    backgroundColor: '#e2e8f0',
  },
});
