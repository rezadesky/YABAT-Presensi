import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  RefreshControl,
  TouchableOpacity,
  ActivityIndicator
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import { useAuth } from '../context/AuthContext';
import MobileHeader from '../components/MobileHeader';
import api from '../api/client';
import { formatTimeWIB, formatDateID } from '../utils/helpers';

export default function BerandaScreen({ navigation }) {
  const { employee, updateEmployee } = useAuth();
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [todayAttendance, setTodayAttendance] = useState(null);
  const [workSchedule, setWorkSchedule] = useState(null);
  const [currentTime, setCurrentTime] = useState('');
  const [currentDate, setCurrentDate] = useState('');

  // 1. Digital Clock & Date Update
  useEffect(() => {
    const updateTime = () => {
      const now = new Date();
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');
      setCurrentTime(`${hours}:${minutes}:${seconds}`);
      setCurrentDate(formatDateID(now));
    };

    updateTime();
    const timer = setInterval(updateTime, 1000);
    return () => clearInterval(timer);
  }, []);

  // 2. Fetch Beranda Data
  const fetchData = async () => {
    try {
      const res = await api.get('/mobile/beranda');
      if (res.data.success) {
        if (res.data.employee) {
          updateEmployee(res.data.employee);
        }
        setTodayAttendance(res.data.todayAttendance);
        setWorkSchedule(res.data.workSchedule);
      }
    } catch (err) {
      console.log('Error fetching beranda data:', err);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  // Otomatis refresh data kehadiran setiap kali tab Beranda dibuka / di-focus
  useFocusEffect(
    useCallback(() => {
      fetchData();
    }, [])
  );

  const onRefresh = () => {
    setRefreshing(true);
    fetchData();
  };

  return (
    <View style={styles.container}>
      {/* Top Header Pegawai */}
      <MobileHeader employee={employee} />

      <ScrollView
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#2563eb']} />
        }
        showsVerticalScrollIndicator={false}
      >
        {/* 1. Hari, Tanggal & Real-Time Clock */}
        <View style={styles.card}>
          <View style={styles.cardHeaderRow}>
            <View>
              <Text style={styles.cardLabel}>WAKTU OPERASIONAL</Text>
              <Text style={styles.dateText}>{currentDate || 'Memuat hari & tanggal...'}</Text>
            </View>
            <View style={styles.liveBadge}>
              <View style={styles.liveDot} />
              <Text style={styles.liveBadgeText}>Live WIB</Text>
            </View>
          </View>

          <View style={styles.clockBox}>
            <Text style={styles.clockText}>{currentTime || '--:--:--'}</Text>
            <Text style={styles.clockSubtext}>Waktu Indonesia Barat (Kutacane, Aceh Tenggara)</Text>
          </View>
        </View>

        {/* 2. Ringkasan Status Kehadiran Hari Ini */}
        <View style={styles.card}>
          <View style={styles.cardHeaderRow}>
            <Text style={styles.sectionTitle}>STATUS KEHADIRAN HARI INI</Text>
            <View 
              style={[
                styles.statusBadge, 
                todayAttendance ? styles.statusBadgePresent : styles.statusBadgeEmpty
              ]}
            >
              <Text 
                style={[
                  styles.statusBadgeText,
                  todayAttendance ? styles.statusBadgeTextPresent : styles.statusBadgeTextEmpty
                ]}
              >
                {todayAttendance ? (todayAttendance.status?.toUpperCase() || 'HADIR') : 'Belum Presensi'}
              </Text>
            </View>
          </View>

          {/* 2 Metric Cards */}
          <View style={styles.metricGrid}>
            {/* Presensi Masuk */}
            <View style={[styles.metricCard, todayAttendance?.time_in ? styles.metricCardActive : styles.metricCardInactive]}>
              <View style={styles.metricCardHeader}>
                <Text style={styles.metricLabel}>Presensi Masuk</Text>
                <View style={[styles.statusDot, todayAttendance?.time_in ? styles.statusDotActive : styles.statusDotInactive]} />
              </View>
              <Text style={[styles.metricTime, todayAttendance?.time_in ? styles.metricTimeActive : styles.metricTimeInactive]}>
                {formatTimeWIB(todayAttendance?.time_in)}
              </Text>
              <Text style={styles.metricDesc}>
                {todayAttendance?.time_in ? 'Tercatat valid di sistem' : 'Belum tercatat'}
              </Text>
            </View>

            {/* Presensi Pulang */}
            <View style={[styles.metricCard, todayAttendance?.time_out ? styles.metricCardActive : styles.metricCardInactive]}>
              <View style={styles.metricCardHeader}>
                <Text style={styles.metricLabel}>Presensi Pulang</Text>
                <View style={[styles.statusDot, todayAttendance?.time_out ? styles.statusDotActive : styles.statusDotInactive]} />
              </View>
              <Text style={[styles.metricTime, todayAttendance?.time_out ? styles.metricTimeActive : styles.metricTimeInactive]}>
                {formatTimeWIB(todayAttendance?.time_out)}
              </Text>
              <Text style={styles.metricDesc}>
                {todayAttendance?.time_out ? 'Telah checkout sore' : 'Tersedia jam pulang'}
              </Text>
            </View>
          </View>

          {/* Tombol Cepat Menuju Halaman Absen (Mencolok & Ramah Orang Tua) */}
          <TouchableOpacity
            style={styles.ctaButtonLarge}
            onPress={() => navigation.navigate('Presensi')}
            activeOpacity={0.85}
          >
            <View style={styles.ctaIconCircle}>
              <Ionicons name="finger-print" size={26} color="#ffffff" />
            </View>
            <View style={{ flex: 1 }}>
              <Text style={styles.ctaButtonLargeTitle}>
                {!todayAttendance?.time_in 
                  ? 'ABSEN MASUK SEKARANG' 
                  : !todayAttendance?.time_out 
                    ? 'ABSEN PULANG SEKARANG' 
                    : 'LIHAT STATUS PRESENSI'}
              </Text>
              <Text style={styles.ctaButtonLargeSub}>
                {!todayAttendance?.time_in 
                  ? 'Tekan di sini untuk absen masuk kerja' 
                  : !todayAttendance?.time_out 
                    ? 'Tekan di sini jika jam kerja telah usai' 
                    : 'Absensi hari ini telah lengkap'}
              </Text>
            </View>
            <Ionicons name="chevron-forward-circle" size={24} color="#ffffff" />
          </TouchableOpacity>
        </View>

        {/* 3. Jadwal Kerja Resmi Unit */}
        <View style={styles.card}>
          <View style={styles.cardHeaderRow}>
            <View>
              <Text style={styles.cardLabel}>JADWAL TUGAS RESMI</Text>
              <Text style={styles.scheduleName}>
                {workSchedule?.name || 'Jadwal Kerja Reguler'}
              </Text>
            </View>
            <View style={styles.scheduleBadge}>
              <Text style={styles.scheduleBadgeText}>
                {workSchedule?.day_of_week || 'Senin - Sabtu'}
              </Text>
            </View>
          </View>

          <View style={styles.scheduleGrid}>
            <View style={styles.scheduleBox}>
              <Text style={styles.scheduleBoxLabel}>Jam Masuk Kerja</Text>
              <Text style={styles.scheduleBoxTime}>
                {formatTimeWIB(workSchedule?.time_in) || '07:30 WIB'}
              </Text>
              <Text style={styles.scheduleBoxSub}>
                Toleransi: {workSchedule?.late_tolerance_minutes || 15} menit
              </Text>
            </View>
            <View style={styles.scheduleBox}>
              <Text style={styles.scheduleBoxLabel}>Jam Pulang Kerja</Text>
              <Text style={styles.scheduleBoxTime}>
                {formatTimeWIB(workSchedule?.time_out) || '16:00 WIB'}
              </Text>
              <Text style={styles.scheduleBoxSub}>
                Presensi sebelum batas alpa
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
    paddingBottom: 24,
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
  dateText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#1e293b',
    marginTop: 2,
  },
  liveBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#eff6ff',
    borderColor: '#bfdbfe',
    borderWidth: 1,
    borderRadius: 12,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  liveDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: '#3b82f6',
    marginRight: 5,
  },
  liveBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    color: '#1d4ed8',
  },
  clockBox: {
    backgroundColor: 'rgba(248, 250, 252, 0.8)',
    borderColor: '#f1f5f9',
    borderWidth: 1,
    borderRadius: 14,
    alignItems: 'center',
    paddingVertical: 10,
  },
  clockText: {
    fontSize: 32,
    fontWeight: '800',
    color: '#0f172a',
    letterSpacing: 1,
    fontFamily: 'monospace',
  },
  clockSubtext: {
    fontSize: 10,
    color: '#94a3b8',
    marginTop: 2,
  },
  sectionTitle: {
    fontSize: 11,
    fontWeight: '700',
    color: '#334155',
    letterSpacing: 0.6,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  statusBadgePresent: {
    backgroundColor: '#ecfdf5',
    borderColor: '#a7f3d0',
    borderWidth: 1,
  },
  statusBadgeEmpty: {
    backgroundColor: '#f1f5f9',
  },
  statusBadgeText: {
    fontSize: 10,
    fontWeight: '700',
  },
  statusBadgeTextPresent: {
    color: '#047857',
  },
  statusBadgeTextEmpty: {
    color: '#64748b',
  },
  metricGrid: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 12,
  },
  metricCard: {
    flex: 1,
    borderRadius: 14,
    borderWidth: 1,
    padding: 12,
  },
  metricCardActive: {
    backgroundColor: 'rgba(236, 253, 245, 0.5)',
    borderColor: 'rgba(167, 243, 208, 0.7)',
  },
  metricCardInactive: {
    backgroundColor: 'rgba(248, 250, 252, 0.6)',
    borderColor: 'rgba(226, 232, 240, 0.8)',
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
  ctaButtonLarge: {
    backgroundColor: '#059669', // Hijau Tegas & Ramah
    borderRadius: 16,
    paddingVertical: 14,
    paddingHorizontal: 16,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    shadowColor: '#059669',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 4,
  },
  ctaIconCircle: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  ctaButtonLargeTitle: {
    color: '#ffffff',
    fontSize: 14,
    fontWeight: '800',
    letterSpacing: 0.3,
  },
  ctaButtonLargeSub: {
    color: 'rgba(255, 255, 255, 0.9)',
    fontSize: 10.5,
    marginTop: 2,
    fontWeight: '500',
  },
  scheduleName: {
    fontSize: 12,
    fontWeight: '700',
    color: '#1e293b',
    marginTop: 2,
  },
  scheduleBadge: {
    backgroundColor: '#eff6ff',
    borderColor: '#bfdbfe',
    borderWidth: 1,
    borderRadius: 6,
    paddingHorizontal: 8,
    paddingVertical: 2,
  },
  scheduleBadgeText: {
    fontSize: 10,
    fontWeight: '600',
    color: '#2563eb',
  },
  scheduleGrid: {
    flexDirection: 'row',
    gap: 8,
  },
  scheduleBox: {
    flex: 1,
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#f1f5f9',
    borderRadius: 12,
    padding: 10,
  },
  scheduleBoxLabel: {
    fontSize: 9.5,
    color: '#94a3b8',
    fontWeight: '600',
  },
  scheduleBoxTime: {
    fontSize: 13,
    fontWeight: '800',
    color: '#0f172a',
    fontFamily: 'monospace',
    marginTop: 2,
  },
  scheduleBoxSub: {
    fontSize: 9,
    color: '#94a3b8',
    marginTop: 2,
  },
});
