@extends('layouts.admin')

@section('title', 'Pengaturan Sistem | YABAT PRESENSI')

@section('content')
<div class="max-w-5xl mx-auto space-y-7 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                Pengaturan Sistem
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Konfigurasi aplikasi, batas geofencing GPS, dan validasi presensi
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="space-y-6">
        @csrf

        <!-- General App Settings -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 space-y-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Identitas Portal Yayasan</h2>
                <p class="text-xs text-slate-400 mt-0.5">Nama instansi dan alamat resmi yayasan</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2 border-t border-slate-100">
                <div>
                    <label class="block font-medium text-slate-700 mb-1.5">Nama Aplikasi</label>
                    <input type="text" name="app_name" value="{{ $settings['app_name'] ?? 'YABAT PRESENSI' }}" class="w-full px-3 py-1.5 bg-slate-50/60 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1.5">Nama Yayasan</label>
                    <input type="text" name="institution_name" value="{{ $settings['institution_name'] ?? 'Yayasan Anak Bangsa Aceh Tenggara' }}" class="w-full px-3 py-1.5 bg-slate-50/60 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-medium text-slate-700 mb-1.5">Alamat Yayasan</label>
                    <input type="text" name="foundation_address" value="{{ $settings['foundation_address'] ?? 'Jl. Kutacane - Blangkejeren, Aceh Tenggara' }}" class="w-full px-3 py-1.5 bg-slate-50/60 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>
        </div>

        <!-- Geofencing Settings & Unified GPS Area -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 space-y-5">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Kebijakan Lokasi &amp; Jam Presensi</h2>
                <p class="text-xs text-slate-400 mt-0.5">Penetapan batas jam kerja dan opsi penyeragaman koordinat institusi</p>
            </div>

            <div class="p-3 bg-blue-50/70 border border-blue-100 rounded-lg flex items-start gap-2.5">
                <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-[11px] text-blue-800 leading-relaxed">
                    <span class="font-semibold">Lokasi Absen Mandiri per Institusi:</span> Saat ini sistem mendukung lokasi GPS &amp; radius yang berbeda untuk masing-masing unit (STKIP, Pesantren Thawalib, SMK Anak Bangsa, dan SMP Thawalib). Anda dapat mengatur titik tiap unit di menu <a href="{{ route('admin.institusi') }}" class="font-semibold underline hover:text-blue-900">Unit Institusi</a> atau Dashboard.
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2 border-t border-slate-100">
                <div>
                    <label class="block font-medium text-slate-700 mb-1.5">Batas Jam Alpa Otomatis</label>
                    <input type="time" name="auto_alpha_cutoff" value="{{ $settings['auto_alpha_cutoff'] ?? '12:00' }}" class="w-full px-3 py-1.5 bg-slate-50/60 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 font-mono">
                    <span class="text-[11px] text-slate-400 mt-1 block">Batas maksimal pegawai dianggap alpa jika tanpa presensi</span>
                </div>
            </div>
        </div>

        <!-- Location Security Policy -->
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 space-y-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Keamanan &amp; Integritas Lokasi GPS</h2>
                <p class="text-xs text-slate-400 mt-0.5">Validasi keaslian titik koordinat perangkat pegawai</p>
            </div>
            <div class="space-y-3 pt-2 border-t border-slate-100">
                <label class="flex items-center justify-between p-3 rounded-lg bg-slate-50/70 hover:bg-slate-50 transition cursor-pointer">
                    <div>
                        <div class="text-xs font-semibold text-slate-800">Izinkan Fake GPS / Mock Location</div>
                        <div class="text-[11px] text-slate-400">Matikan untuk memblokir aplikasi lokasi palsu pada perangkat Android/iOS</div>
                    </div>
                    <input type="checkbox" name="allow_mock_location" value="true" {{ ($settings['allow_mock_location'] ?? 'false') === 'true' ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                </label>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition">
                Simpan Seluruh Pengaturan
            </button>
        </div>
    </form>
</div>

@endsection
