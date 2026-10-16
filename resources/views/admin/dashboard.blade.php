@extends('layouts.admin')

@section('title', 'Dashboard Yayasan | YABAT PRESENSI')
@section('header_title', 'Ringkasan Yayasan')

@section('content')
<div class="max-w-7xl mx-auto space-y-7 pb-12">
    
    <!-- Hero / Clean Greeting Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                Dashboard Yayasan
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Ringkasan presensi dan aktivitas pegawai seluruh unit YABAT
            </p>
        </div>
        <div class="inline-flex items-center gap-2 self-start sm:self-auto px-3 py-1 rounded-full bg-slate-100 text-[11px] font-medium text-slate-600">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            <span>Live Database: {{ date('d F Y') }}</span>
        </div>
    </div>

    @if (session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- 4 Professional Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Pegawai -->
        <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200/80 hover:border-slate-300 transition-colors">
            <div class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Total Pegawai</div>
            <div class="mt-2 text-lg sm:text-xl font-semibold text-slate-900 tracking-tight">{{ $totalEmployees }}</div>
            <div class="mt-1 text-[11px] text-slate-400">Seluruh unit yayasan</div>
        </div>

        <!-- Hadir Hari Ini -->
        <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200/80 hover:border-slate-300 transition-colors">
            <div class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Hadir Hari Ini</div>
            <div class="mt-2 text-lg sm:text-xl font-semibold text-slate-900 tracking-tight flex items-baseline gap-2">
                <span>{{ $presentCount }}</span>
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">{{ $attendanceRate }}%</span>
            </div>
            <div class="mt-1 text-[11px] text-emerald-600 font-medium">Tepat waktu &amp; hadir</div>
        </div>

        <!-- Terlambat Hari Ini -->
        <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200/80 hover:border-slate-300 transition-colors">
            <div class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Terlambat</div>
            <div class="mt-2 text-lg sm:text-xl font-semibold text-slate-900 tracking-tight flex items-baseline gap-2">
                <span>{{ $lateCount }}</span>
                <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded">{{ $totalEmployees > 0 ? round(($lateCount / $totalEmployees) * 100, 1) : 0 }}%</span>
            </div>
            <div class="mt-1 text-[11px] text-slate-400">Lewat jam masuk</div>
        </div>

        <!-- Tidak Hadir Hari Ini -->
        <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200/80 hover:border-slate-300 transition-colors">
            <div class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Izin &amp; Sakit</div>
            <div class="mt-2 text-lg sm:text-xl font-semibold text-slate-900 tracking-tight flex items-baseline gap-2">
                <span>{{ $leaveCount }}</span>
                <span class="text-xs font-semibold text-rose-500 bg-rose-50 px-1.5 py-0.5 rounded">{{ $totalEmployees > 0 ? round(($leaveCount / $totalEmployees) * 100, 1) : 0 }}%</span>
            </div>
            <div class="mt-1 text-[11px] text-slate-400">Surat terkonfirmasi</div>
        </div>
    </div>

    <!-- Ringkasan Per Institusi (3 Minimalist Cards) -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                Unit Pendidikan Yayasan
            </h2>
            <span class="text-xs text-slate-400 font-medium">{{ count($institutions) }} Unit Terdaftar</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($institutions as $inst)
            <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200/80 hover:border-slate-300 transition-colors flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">{{ $inst['name'] }}</h3>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $inst['category'] }}</div>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                            {{ $inst['employees_count'] }} Staf
                        </span>
                    </div>

                    <!-- Clean thin bar -->
                    <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden flex mt-3.5 mb-4">
                        <div class="bg-blue-600 h-full" style="width: {{ $inst['rate'] }}%"></div>
                        <div class="bg-slate-200 h-full" style="width: {{ 100 - $inst['rate'] }}%"></div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-[11px] pt-3 border-t border-slate-100">
                    <span class="text-slate-500">Hadir: <strong class="text-slate-800 font-semibold">{{ $inst['present_count'] }}</strong></span>
                    <span class="text-slate-500">Tingkat: <strong class="text-emerald-600 font-semibold">{{ $inst['rate'] }}%</strong></span>
                    <a href="{{ route('admin.institusi') }}" class="text-blue-600 hover:text-blue-800 font-medium">Detail &rarr;</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Grafik Presensi Mingguan (Modern Minimal Chart) -->
    <div class="bg-white p-5 sm:p-6 rounded-xl border border-slate-200/70">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Presensi Mingguan</h2>
                <div class="text-xs text-slate-400 mt-0.5">Tren kehadiran 7 hari terakhir seluruh unit</div>
            </div>
            
            <div class="flex items-center gap-4 text-xs text-slate-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span> Hadir
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-slate-200"></span> Total Staf
                </span>
            </div>
        </div>

        <!-- Minimal Bars -->
        <div class="space-y-3.5">
            @foreach ($weeklyStats as $stat)
            <div class="flex items-center gap-4 text-xs">
                <span class="w-12 text-slate-500 font-medium">{{ $stat['day'] }}</span>
                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden flex">
                    <div class="bg-blue-600 h-full" style="width: {{ $stat['percentage'] }}%"></div>
                </div>
                <span class="w-16 text-right text-slate-500 font-mono text-[11px]">{{ $stat['count'] }} / {{ $totalEmployees }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- ==========================================
         PENGATURAN LOKASI ABSEN & RADIUS GPS WILAYAH
         ========================================== -->
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 space-y-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Pengaturan Titik GPS &amp; Radius Absen</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Atur titik koordinat dan toleransi radius masing-masing unit institusi atau terapkan serentak
                </p>
            </div>
            <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full self-start sm:self-auto">
                {{ $rawInstitutions->count() }} Unit Institusi
            </span>
        </div>

        @php
            $sampleInst = $rawInstitutions->first();
        @endphp

        <form method="POST" action="{{ route('admin.dashboard.locations') }}" class="space-y-4" id="formGpsLocation">
            @csrf

            <!-- Pilihan Target Institusi -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Target Institusi</label>
                <select 
                    name="institution_id" 
                    id="selectInstitution"
                    class="w-full sm:w-1/2 px-3 py-2 bg-slate-50/70 border border-slate-200 rounded-lg text-slate-800 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600"
                    onchange="handleInstitutionChange(this)"
                >
                    <option value="">-- Terapkan Serentak ke Seluruh Unit --</option>
                    @foreach ($rawInstitutions as $inst)
                        <option 
                            value="{{ $inst->id }}" 
                            data-lat="{{ $inst->latitude }}" 
                            data-lng="{{ $inst->longitude }}" 
                            data-rad="{{ $inst->radius_meters }}">
                            {{ $inst->name }} ({{ $inst->code }})
                        </option>
                    @endforeach
                </select>
                <span class="text-[10px] text-slate-400 mt-1 block">Pilih institusi tertentu untuk mengatur lokasi spesifiknya, atau pilih serentak untuk menyamakan semua unit.</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <!-- Latitude -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Titik Latitude</label>
                    <input 
                        type="text" 
                        name="latitude" 
                        id="inputLatitude"
                        value="{{ old('latitude', $sampleInst->latitude ?? '3.488300') }}" 
                        required
                        placeholder="Contoh: 3.488300"
                        class="w-full px-3 py-2 bg-slate-50/70 border border-slate-200 rounded-lg text-slate-800 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-blue-600"
                    >
                    <span class="text-[10px] text-slate-400 mt-1 block">Garis lintang lokasi unit</span>
                </div>

                <!-- Longitude -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Titik Longitude</label>
                    <input 
                        type="text" 
                        name="longitude" 
                        id="inputLongitude"
                        value="{{ old('longitude', $sampleInst->longitude ?? '97.808500') }}" 
                        required
                        placeholder="Contoh: 97.808500"
                        class="w-full px-3 py-2 bg-slate-50/70 border border-slate-200 rounded-lg text-slate-800 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-blue-600"
                    >
                    <span class="text-[10px] text-slate-400 mt-1 block">Garis bujur lokasi unit</span>
                </div>

                <!-- Radius Meters -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Radius Presensi (Meter)
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            name="radius_meters" 
                            id="inputRadius"
                            value="{{ old('radius_meters', $sampleInst->radius_meters ?? 100) }}" 
                            min="10" 
                            max="5000"
                            required
                            placeholder="100"
                            class="w-full px-3 py-2 pr-14 bg-slate-50/70 border border-slate-200 rounded-lg text-slate-800 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-blue-600"
                        >
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-[11px] text-slate-400 font-medium pointer-events-none">
                            Meter
                        </span>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">Jarak toleransi maksimal dari titik tengah</span>
                </div>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-lg flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-600">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span id="targetLabel">Mode: <strong>Terapkan Serentak Seluruh Institusi</strong></span>
                </div>
                <button 
                    type="submit" 
                    class="py-2 px-5 rounded-lg bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white text-xs font-semibold shadow-sm transition flex items-center justify-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Titik &amp; Radius</span>
                </button>
            </div>
        </form>

        <script>
            function handleInstitutionChange(select) {
                const opt = select.options[select.selectedIndex];
                const targetLabel = document.getElementById('targetLabel');
                if (opt.value) {
                    if (opt.dataset.lat) document.getElementById('inputLatitude').value = opt.dataset.lat;
                    if (opt.dataset.lng) document.getElementById('inputLongitude').value = opt.dataset.lng;
                    if (opt.dataset.rad) document.getElementById('inputRadius').value = opt.dataset.rad;
                    targetLabel.innerHTML = 'Mode: Khusus <strong>' + opt.text + '</strong>';
                } else {
                    targetLabel.innerHTML = 'Mode: <strong>Terapkan Serentak Seluruh Institusi</strong>';
                }
            }
        </script>
    </div>

    <!-- Aktivitas Presensi Terbaru (Clean Minimal Table) -->
    <div class="bg-white rounded-xl border border-slate-200/70 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Presensi Terbaru</h2>
                <div class="text-xs text-slate-400 mt-0.5">Catatan presensi masuk pegawai terkini</div>
            </div>
            <a href="{{ route('admin.presensi') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                Lihat Semua &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="text-[11px] font-semibold text-slate-400 border-b border-slate-100 uppercase tracking-wider">
                        <th class="py-3 px-5">Nama Pegawai</th>
                        <th class="py-3 px-5">Institusi</th>
                        <th class="py-3 px-5">Jabatan</th>
                        <th class="py-3 px-5">Waktu</th>
                        <th class="py-3 px-5 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($recentActivities as $act)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-3.5 px-5 font-semibold text-slate-900">{{ $act->employee->name ?? '-' }}</td>
                        <td class="py-3.5 px-5 text-slate-500">{{ $act->institution->name ?? '-' }}</td>
                        <td class="py-3.5 px-5 text-slate-400">{{ $act->employee->position ?? '-' }}</td>
                        <td class="py-3.5 px-5 font-mono text-slate-600">
                            {{ $act->time_in ? date('H:i', strtotime($act->time_in)) . ' WIB' : '-' }}
                        </td>
                        <td class="py-3.5 px-5 text-right">
                            @if ($act->status === 'hadir')
                                <span class="text-emerald-600 font-medium">Hadir</span>
                            @elseif ($act->status === 'terlambat')
                                <span class="text-amber-600 font-medium">Terlambat</span>
                            @elseif ($act->status === 'izin')
                                <span class="text-blue-600 font-medium">Izin</span>
                            @elseif ($act->status === 'sakit')
                                <span class="text-purple-600 font-medium">Sakit</span>
                            @else
                                <span class="text-rose-500 font-medium">Alpa</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">Belum ada riwayat aktivitas presensi tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
