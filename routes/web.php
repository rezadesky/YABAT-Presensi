<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\WorkScheduleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', function () {
    return redirect()->route('login');
});
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rute Informasi khusus bagi pegawai yang membuka tautan di browser web
Route::get('/app-notice', function () {
    return view('mobile_notice');
})->name('mobile.info');

// ========================================================
// RUTE TERPROTEKSI (WAJIB LOGIN UNTUK MENGAKSES)
// ========================================================
Route::middleware('auth')->group(function () {
    // Seluruh rute /mobile di browser dialihkan ke halaman info aplikasi mobile
    Route::prefix('mobile')->name('mobile.')->group(function () {
        Route::get('/beranda', function () {
            if (auth()->user()->role === 'admin') return redirect()->route('admin.dashboard');
            return redirect()->route('mobile.info');
        })->name('beranda');

        Route::get('/presensi', function () {
            if (auth()->user()->role === 'admin') return redirect()->route('admin.dashboard');
            return redirect()->route('mobile.info');
        })->name('presensi');

        Route::get('/riwayat', function () {
            if (auth()->user()->role === 'admin') return redirect()->route('admin.dashboard');
            return redirect()->route('mobile.info');
        })->name('riwayat');

        Route::get('/profil', function () {
            if (auth()->user()->role === 'admin') return redirect()->route('admin.dashboard');
            return redirect()->route('mobile.info');
        })->name('profil');
    });

    Route::get('/presensi-mobile', function () {
        if (auth()->user()->role === 'admin') return redirect()->route('admin.dashboard');
        return redirect()->route('mobile.info');
    })->name('presensi.mobile');

    // Admin Panel Routes (Hanya untuk Role Admin)
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        // 1. Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/dashboard/locations', [DashboardController::class, 'updateLocations'])->name('dashboard.locations');

    // 2. Data Pegawai CRUD
    Route::get('/pegawai', [EmployeeController::class, 'index'])->name('pegawai');
    Route::post('/pegawai', [EmployeeController::class, 'store'])->name('pegawai.store');
    Route::put('/pegawai/{id}', [EmployeeController::class, 'update'])->name('pegawai.update');
    Route::delete('/pegawai/{id}', [EmployeeController::class, 'destroy'])->name('pegawai.destroy');

    // 3. Institusi Unit (Daftar unit yayasan & pengaturan lokasi GPS per institusi)
    Route::get('/institusi', [InstitutionController::class, 'index'])->name('institusi');
    Route::post('/institusi', [InstitutionController::class, 'store'])->name('institusi.store');
    Route::put('/institusi/{id}', [InstitutionController::class, 'update'])->name('institusi.update');
    Route::delete('/institusi/{id}', [InstitutionController::class, 'destroy'])->name('institusi.destroy');

    // 4. Jadwal Kerja CRUD
    Route::get('/jadwal', [WorkScheduleController::class, 'index'])->name('jadwal');
    Route::post('/jadwal', [WorkScheduleController::class, 'store'])->name('jadwal.store');
    Route::put('/jadwal/{id}', [WorkScheduleController::class, 'update'])->name('jadwal.update');
    Route::delete('/jadwal/{id}', [WorkScheduleController::class, 'destroy'])->name('jadwal.destroy');

    // 5. Presensi Monitoring & API
    Route::get('/presensi', [AttendanceController::class, 'monitor'])->name('presensi');
    Route::post('/presensi/checkin', [AttendanceController::class, 'checkIn'])->name('presensi.checkin');
    Route::post('/presensi/checkout', [AttendanceController::class, 'checkOut'])->name('presensi.checkout');

    // 6. Riwayat Presensi
    Route::get('/riwayat', [AttendanceController::class, 'history'])->name('riwayat');

    // 7. Laporan Rekapitulasi (Bulanan, Tahunan, Kustom & Ekspor Excel)
    Route::get('/laporan', [ReportController::class, 'index'])->name('laporan');
    Route::get('/laporan/export-excel', [ReportController::class, 'exportExcel'])->name('laporan.export');

        // 8. Pengaturan Sistem
        Route::get('/pengaturan', [SettingController::class, 'index'])->name('pengaturan');
        Route::post('/pengaturan', [SettingController::class, 'update'])->name('pengaturan.update');
    });
});

