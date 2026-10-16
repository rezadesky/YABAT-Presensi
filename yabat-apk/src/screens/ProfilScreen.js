import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  Platform,
  ActivityIndicator
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../context/AuthContext';
import MobileHeader from '../components/MobileHeader';
import { getInitials } from '../utils/helpers';

export default function ProfilScreen() {
  const { employee, user, logout } = useAuth();
  const [loggingOut, setLoggingOut] = useState(false);

  const initials = getInitials(employee?.name || user?.name);

  const performLogout = async () => {
    setLoggingOut(true);
    try {
      await logout();
    } finally {
      setLoggingOut(false);
    }
  };

  const handleLogout = () => {
    // Pada browser web, gunakan window.confirm standar browser
    if (Platform.OS === 'web') {
      const confirmed = window.confirm('Apakah Anda yakin ingin keluar dari akun ini?');
      if (confirmed) {
        performLogout();
      }
      return;
    }

    // Pada HP Android/iOS native
    Alert.alert(
      'Konfirmasi Keluar',
      'Apakah Anda yakin ingin keluar dari akun presensi ini?',
      [
        { text: 'Batal', style: 'cancel' },
        {
          text: 'Keluar',
          style: 'destructive',
          onPress: performLogout,
        },
      ]
    );
  };

  const maxRadius = employee?.institution?.radius_meters || 100;

  return (
    <View style={styles.container}>
      <MobileHeader employee={employee} />

      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* 1. Hero Card Profil Pegawai */}
        <View style={styles.card}>
          <View style={styles.heroAvatarContainer}>
            <Text style={styles.heroAvatarText}>{initials}</Text>
          </View>

          <Text style={styles.heroName}>{employee?.name || user?.name || 'Pegawai Yayasan'}</Text>
          <Text style={styles.heroPosition}>{employee?.position || 'Tenaga Pendidik / Staf'}</Text>
          <View style={styles.nipPill}>
            <Text style={styles.nipText}>NIP/NIDN: {employee?.nip_nidn || '-'}</Text>
          </View>

          <View style={styles.heroFooter}>
            <View style={styles.heroFooterCol}>
              <Text style={styles.heroFooterLabel}>Status Kerja</Text>
              <Text style={styles.heroFooterValueEmerald}>
                {employee?.employment_status || 'Tetap'}
              </Text>
            </View>
            <View style={styles.heroFooterDivider} />
            <View style={styles.heroFooterCol}>
              <Text style={styles.heroFooterLabel}>Bergabung Sejak</Text>
              <Text style={styles.heroFooterValueMono}>
                {employee?.join_date ? new Date(employee.join_date).getFullYear() : '2020'}
              </Text>
            </View>
          </View>
        </View>

        {/* 2. Informasi Detail Institusi & Kontak */}
        <View style={styles.card}>
          <View style={styles.cardSectionHeader}>
            <Text style={styles.cardLabel}>LEMBAGA NAUNGAN</Text>
            <Text style={styles.institutionTitle}>
              {employee?.institution?.name || 'Yayasan Anak Bangsa Aceh Tenggara'}
            </Text>
          </View>

          <View style={styles.infoList}>
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Kode Unit:</Text>
              <Text style={styles.infoValueMono}>{employee?.institution?.code || '-'}</Text>
            </View>
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Kategori:</Text>
              <Text style={styles.infoValue}>{employee?.institution?.category || '-'}</Text>
            </View>
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Radius Presensi:</Text>
              <Text style={[styles.infoValueMono, { color: '#2563eb' }]}>{maxRadius} Meter</Text>
            </View>
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Email Pegawai:</Text>
              <Text style={styles.infoValue}>{employee?.email || user?.email || '-'}</Text>
            </View>
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Nomor Telepon:</Text>
              <Text style={styles.infoValueMono}>{employee?.phone || '-'}</Text>
            </View>
            <View style={[styles.infoRow, { flexDirection: 'column', alignItems: 'flex-start', borderBottomWidth: 0 }]}>
              <Text style={[styles.infoLabel, { marginBottom: 2 }]}>Alamat Kampus / Sekolah:</Text>
              <Text style={[styles.infoValue, { lineHeight: 16 }]}>
                {employee?.institution?.address || 'Pulonas Baru, Kec. Lawe Bulan, Kabupaten Aceh Tenggara, Aceh'}
              </Text>
            </View>
          </View>
        </View>

        {/* 3. Pengaturan Akun & Akses Sistem */}
        <View style={styles.card}>
          <View style={styles.cardSectionHeader}>
            <Text style={styles.cardLabel}>KEAMANAN & AKUN</Text>
            <Text style={styles.institutionTitle}>Preferensi & Perangkat</Text>
          </View>

          <View style={styles.securityList}>
            <View style={styles.securityBox}>
              <View>
                <Text style={styles.securityTitle}>Sensor Lokasi GPS</Text>
                <Text style={styles.securityDesc}>Wajib aktif saat presensi</Text>
              </View>
              <View style={styles.activeBadge}>
                <Text style={styles.activeBadgeText}>Aktif</Text>
              </View>
            </View>

            <View style={styles.securityBox}>
              <View>
                <Text style={styles.securityTitle}>Mode Validasi</Text>
                <Text style={styles.securityDesc}>Geofencing Titik Radius GPS</Text>
              </View>
              <View style={styles.shieldBadge}>
                <Ionicons name="shield-checkmark" size={12} color="#2563eb" style={{ marginRight: 3 }} />
                <Text style={styles.shieldBadgeText}>Sistem Resmi</Text>
              </View>
            </View>
          </View>
        </View>

        {/* 4. Tombol Logout */}
        <TouchableOpacity
          style={styles.logoutButton}
          onPress={handleLogout}
          disabled={loggingOut}
          activeOpacity={0.85}
        >
          {loggingOut ? (
            <ActivityIndicator color="#e11d48" size="small" />
          ) : (
            <>
              <Ionicons name="log-out-outline" size={18} color="#e11d48" />
              <Text style={styles.logoutButtonText}>Keluar Akun</Text>
            </>
          )}
        </TouchableOpacity>
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
    padding: 18,
    borderWidth: 1,
    borderColor: 'rgba(226, 232, 240, 0.8)',
    marginBottom: 14,
    shadowColor: '#64748b',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 6,
    elevation: 2,
  },
  heroAvatarContainer: {
    width: 66,
    height: 66,
    borderRadius: 20,
    backgroundColor: '#1d4ed8',
    alignItems: 'center',
    justifyContent: 'center',
    alignSelf: 'center',
    marginBottom: 10,
    shadowColor: '#2563eb',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 3,
  },
  heroAvatarText: {
    color: '#ffffff',
    fontSize: 22,
    fontWeight: '800',
  },
  heroName: {
    fontSize: 16,
    fontWeight: '800',
    color: '#0f172a',
    textAlign: 'center',
  },
  heroPosition: {
    fontSize: 12,
    fontWeight: '600',
    color: '#2563eb',
    textAlign: 'center',
    marginTop: 2,
  },
  nipPill: {
    backgroundColor: '#f1f5f9',
    borderRadius: 12,
    paddingHorizontal: 10,
    paddingVertical: 3,
    alignSelf: 'center',
    marginTop: 6,
  },
  nipText: {
    fontSize: 10,
    fontWeight: '600',
    color: '#475569',
    fontFamily: 'monospace',
  },
  heroFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-around',
    borderTopWidth: 1,
    borderTopColor: '#f1f5f9',
    paddingTop: 14,
    marginTop: 14,
  },
  heroFooterCol: {
    alignItems: 'center',
  },
  heroFooterDivider: {
    width: 1,
    height: 24,
    backgroundColor: '#e2e8f0',
  },
  heroFooterLabel: {
    fontSize: 9.5,
    color: '#94a3b8',
  },
  heroFooterValueEmerald: {
    fontSize: 12,
    fontWeight: '700',
    color: '#059669',
    marginTop: 2,
  },
  heroFooterValueMono: {
    fontSize: 12,
    fontWeight: '800',
    color: '#334155',
    fontFamily: 'monospace',
    marginTop: 2,
  },
  cardSectionHeader: {
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
    paddingBottom: 8,
    marginBottom: 8,
  },
  cardLabel: {
    fontSize: 9.5,
    fontWeight: '700',
    color: '#94a3b8',
    letterSpacing: 0.8,
  },
  institutionTitle: {
    fontSize: 12,
    fontWeight: '700',
    color: '#1e293b',
    marginTop: 2,
  },
  infoList: {},
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 7,
    borderBottomWidth: 1,
    borderBottomColor: '#f8fafc',
  },
  infoLabel: {
    fontSize: 11,
    color: '#94a3b8',
  },
  infoValue: {
    fontSize: 11,
    fontWeight: '600',
    color: '#1e293b',
  },
  infoValueMono: {
    fontSize: 11,
    fontWeight: '700',
    color: '#1e293b',
    fontFamily: 'monospace',
  },
  securityList: {
    gap: 8,
  },
  securityBox: {
    backgroundColor: '#f8fafc',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(226, 232, 240, 0.7)',
    padding: 10,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  securityTitle: {
    fontSize: 11,
    fontWeight: '700',
    color: '#1e293b',
  },
  securityDesc: {
    fontSize: 9.5,
    color: '#94a3b8',
    marginTop: 1,
  },
  activeBadge: {
    backgroundColor: '#ecfdf5',
    borderColor: '#a7f3d0',
    borderWidth: 1,
    borderRadius: 6,
    paddingHorizontal: 8,
    paddingVertical: 2,
  },
  activeBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    color: '#047857',
  },
  shieldBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#eff6ff',
    borderColor: '#bfdbfe',
    borderWidth: 1,
    borderRadius: 6,
    paddingHorizontal: 8,
    paddingVertical: 2,
  },
  shieldBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    color: '#2563eb',
  },
  logoutButton: {
    backgroundColor: '#fff1f2',
    borderColor: '#fecdd3',
    borderWidth: 1,
    borderRadius: 14,
    paddingVertical: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    marginTop: 4,
    marginBottom: 8,
  },
  logoutButtonText: {
    color: '#e11d48',
    fontSize: 12,
    fontWeight: '700',
  },
});
