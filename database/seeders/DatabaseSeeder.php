<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Admin Default
        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@stkip-us.ac.id'],
            [
                'name' => 'Administrator YABAT',
                'password' => bcrypt('stkipus2026'),
                'role' => 'admin',
                'phone' => '0812-6482-9901',
            ]
        );

        // 2. Tiga Institusi Yayasan
        $stkip = \App\Models\Institution::firstOrCreate(
            ['code' => 'STKIP'],
            [
                'name' => 'STKIP Usman Safri',
                'category' => 'Perguruan Tinggi',
                'description' => 'Sekolah Tinggi Keguruan & Ilmu Pendidikan sarjana terakreditasi.',
                'latitude' => 3.48830000,
                'longitude' => 97.80850000,
                'radius_meters' => 100,
                'address' => 'Pulonas Baru, Kec. Lawe Bulan, Kabupaten Aceh Tenggara, Aceh',
                'phone' => '0812-6482-9901',
                'email' => 'info@stkip-usmansafri.ac.id',
                'head_name' => 'Dr. Rahmat Hidayat, M.Pd',
                'is_active' => true,
            ]
        );

        $thawalib = \App\Models\Institution::firstOrCreate(
            ['code' => 'THAWALIB'],
            [
                'name' => 'Pesantren Thawalib',
                'category' => 'Pesantren Asrama',
                'description' => 'Pondok pesantren terpadu dengan sistem asrama dan kajian kitab kuning.',
                'latitude' => 3.48910000,
                'longitude' => 97.80990000,
                'radius_meters' => 150,
                'address' => 'Pulonas Baru, Kec. Lawe Bulan, Kabupaten Aceh Tenggara, Aceh',
                'phone' => '0821-7734-1189',
                'email' => 'sekretariat@thawalib.sch.id',
                'head_name' => 'Ust. Faisal Akbar, Lc',
                'is_active' => true,
            ]
        );

        $smk = \App\Models\Institution::firstOrCreate(
            ['code' => 'SMK-AB'],
            [
                'name' => 'SMK Swasta Anak Bangsa',
                'category' => 'Sekolah Kejuruan',
                'description' => 'Sekolah Menengah Kejuruan fokus pada keahlian teknologi informatika.',
                'latitude' => 3.48750000,
                'longitude' => 97.80720000,
                'radius_meters' => 120,
                'address' => 'Pulonas Baru, Kec. Lawe Bulan, Kabupaten Aceh Tenggara, Aceh',
                'phone' => '0853-6112-7880',
                'email' => 'admin@smkanakbangsa.sch.id',
                'head_name' => 'Drs. Usman Safri, M.M',
                'is_active' => true,
            ]
        );

        $smp = \App\Models\Institution::firstOrCreate(
            ['code' => 'SMP-THW'],
            [
                'name' => 'SMP Thawalib',
                'category' => 'Sekolah Menengah Pertama',
                'description' => 'Sekolah Menengah Pertama Islam Terpadu naungan Yayasan Anak Bangsa.',
                'latitude' => 3.48900000,
                'longitude' => 97.80950000,
                'radius_meters' => 100,
                'address' => 'Pulonas Baru, Kec. Lawe Bulan, Kabupaten Aceh Tenggara, Aceh',
                'phone' => '0821-7734-1189',
                'email' => 'smp@thawalib.sch.id',
                'head_name' => 'Kepala Sekolah SMP Thawalib',
                'is_active' => true,
            ]
        );

        // 3. Jadwal Kerja
        \App\Models\WorkSchedule::firstOrCreate(
            ['name' => 'Jadwal Reguler STKIP', 'institution_id' => $stkip->id],
            [
                'day_of_week' => 'Senin - Sabtu',
                'time_in' => '07:45:00',
                'time_out' => '16:00:00',
                'late_tolerance_minutes' => 15,
                'is_active' => true,
            ]
        );

        \App\Models\WorkSchedule::firstOrCreate(
            ['name' => 'Shif Asrama Thawalib', 'institution_id' => $thawalib->id],
            [
                'day_of_week' => 'Setiap Hari',
                'time_in' => '07:00:00',
                'time_out' => '15:30:00',
                'late_tolerance_minutes' => 10,
                'is_active' => true,
            ]
        );

        \App\Models\WorkSchedule::firstOrCreate(
            ['name' => 'Guru SMK Full Day', 'institution_id' => $smk->id],
            [
                'day_of_week' => 'Senin - Jumat',
                'time_in' => '07:30:00',
                'time_out' => '16:15:00',
                'late_tolerance_minutes' => 15,
                'is_active' => true,
            ]
        );

        \App\Models\WorkSchedule::firstOrCreate(
            ['name' => 'Jadwal Reguler SMP Thawalib', 'institution_id' => $smp->id],
            [
                'day_of_week' => 'Senin - Sabtu',
                'time_in' => '07:30:00',
                'time_out' => '15:00:00',
                'late_tolerance_minutes' => 15,
                'is_active' => true,
            ]
        );

        // 4. Sample Employees
        $employeesData = [
            [
                'institution_id' => $stkip->id,
                'nip_nidn' => '0112088501',
                'name' => 'Dr. Rahmat Hidayat, M.Pd',
                'email' => 'rahmat@stkip-usmansafri.ac.id',
                'phone' => '0812-6482-9901',
                'gender' => 'L',
                'position' => 'Dosen Tetap (Kaprodi)',
                'employment_status' => 'tetap',
                'join_date' => '2019-03-01',
            ],
            [
                'institution_id' => $stkip->id,
                'nip_nidn' => '0125048902',
                'name' => 'Nurhalimah, S.Pd., M.Hum',
                'email' => 'nurhalimah@stkip-usmansafri.ac.id',
                'phone' => '0852-9923-4412',
                'gender' => 'P',
                'position' => 'Dosen Pendidikan Bahasa',
                'employment_status' => 'tetap',
                'join_date' => '2021-08-15',
            ],
            [
                'institution_id' => $thawalib->id,
                'nip_nidn' => 'THW-2022-042',
                'name' => 'Ust. Faisal Akbar, Lc',
                'email' => 'faisal@thawalib.sch.id',
                'phone' => '0821-7734-1189',
                'gender' => 'L',
                'position' => 'Ustadz Pengasuh Asrama',
                'employment_status' => 'tetap',
                'join_date' => '2020-01-10',
            ],
            [
                'institution_id' => $thawalib->id,
                'nip_nidn' => 'THW-2023-088',
                'name' => 'Ustazah Siti Sarah, S.Ag',
                'email' => 'siti.sarah@thawalib.sch.id',
                'phone' => '0813-9002-3312',
                'gender' => 'P',
                'position' => 'Pengajar Tahfidz Quran',
                'employment_status' => 'tetap',
                'join_date' => '2022-06-01',
            ],
            [
                'institution_id' => $smk->id,
                'nip_nidn' => '198405102008011003',
                'name' => 'Budi Pratama, S.Kom, Gr.',
                'email' => 'budi@smkanakbangsa.sch.id',
                'phone' => '0853-6112-7880',
                'gender' => 'L',
                'position' => 'Guru Produktif RPL / IT',
                'employment_status' => 'tetap',
                'join_date' => '2018-07-15',
            ],
            [
                'institution_id' => $smk->id,
                'nip_nidn' => '199203142019032004',
                'name' => 'Cut Mutia, S.Pd',
                'email' => 'cutmutia@smkanakbangsa.sch.id',
                'phone' => '0822-4412-9988',
                'gender' => 'P',
                'position' => 'Guru Matematika',
                'employment_status' => 'kontrak',
                'join_date' => '2023-01-05',
            ],
        ];

        foreach ($employeesData as $emp) {
            \App\Models\Employee::firstOrCreate(
                ['nip_nidn' => $emp['nip_nidn']],
                $emp
            );
        }

        // 5. Presensi Hari Ini & Kemarin
        $allEmployees = \App\Models\Employee::all();
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        foreach ($allEmployees as $index => $employee) {
            // Presensi Kemarin
            \App\Models\Attendance::firstOrCreate(
                ['employee_id' => $employee->id, 'date' => $yesterday],
                [
                    'institution_id' => $employee->institution_id,
                    'time_in' => '07:28:10',
                    'time_out' => '16:05:00',
                    'status' => 'hadir',
                    'latitude_in' => 3.4883,
                    'longitude_in' => 97.8085,
                    'latitude_out' => 3.4883,
                    'longitude_out' => 97.8085,
                    'notes' => 'Presensi tepat waktu',
                ]
            );

            // Presensi Hari Ini
            $statuses = ['hadir', 'hadir', 'terlambat', 'hadir', 'izin', 'hadir'];
            $status = $statuses[$index % count($statuses)];
            $timeIn = $status === 'terlambat' ? '08:12:00' : ($status === 'izin' ? null : '07:25:30');

            \App\Models\Attendance::firstOrCreate(
                ['employee_id' => $employee->id, 'date' => $today],
                [
                    'institution_id' => $employee->institution_id,
                    'time_in' => $timeIn,
                    'time_out' => null,
                    'status' => $status,
                    'latitude_in' => $status !== 'izin' ? 3.4883 : null,
                    'longitude_in' => $status !== 'izin' ? 97.8085 : null,
                    'notes' => $status === 'izin' ? 'Izin kedinasan pelatihan' : ($status === 'terlambat' ? 'Kendaraan bermasalah di jalan' : 'Hadir normal'),
                ]
            );
        }

        // 6. Settings Default
        $settings = [
            'app_name' => 'YABAT PRESENSI',
            'institution_name' => 'Yayasan Anak Bangsa Aceh Tenggara',
            'foundation_address' => 'Jl. Kutacane - Blangkejeren, Aceh Tenggara, Aceh',
            'gps_tolerance_meters' => '100',
            'allow_mock_location' => 'false',
            'auto_alpha_cutoff' => '12:00',
        ];

        foreach ($settings as $k => $v) {
            \App\Models\Setting::set($k, $v);
        }
    }
}
