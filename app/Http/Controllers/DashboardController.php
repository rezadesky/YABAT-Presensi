<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Institution;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        $totalEmployees = Employee::where('is_active', true)->count();
        $totalInstitutions = Institution::where('is_active', true)->count();

        $todayAttendances = Attendance::with(['employee', 'institution'])
            ->whereDate('date', $today)
            ->get();

        $presentCount = $todayAttendances->whereIn('status', ['hadir', 'terlambat'])->count();
        $lateCount = $todayAttendances->where('status', 'terlambat')->count();
        $leaveCount = $todayAttendances->whereIn('status', ['izin', 'sakit'])->count();
        
        $attendanceRate = $totalEmployees > 0 
            ? round(($presentCount / $totalEmployees) * 100, 1) 
            : 0;

        // Institution Breakdown
        $institutions = Institution::withCount('employees')
            ->where('is_active', true)
            ->get()
            ->map(function ($inst) use ($today) {
                $todayInstAttendances = Attendance::where('institution_id', $inst->id)
                    ->whereDate('date', $today)
                    ->get();

                $instPresent = $todayInstAttendances->whereIn('status', ['hadir', 'terlambat'])->count();
                $rate = $inst->employees_count > 0 
                    ? round(($instPresent / $inst->employees_count) * 100, 1) 
                    : 0;

                return [
                    'id' => $inst->id,
                    'name' => $inst->name,
                    'category' => $inst->category,
                    'employees_count' => $inst->employees_count,
                    'present_count' => $instPresent,
                    'rate' => $rate,
                ];
            });

        // 7-day trend (Optimized Single Grouped Query)
        $sevenDaysAgo = Carbon::today()->subDays(6)->toDateString();
        $weeklyCounts = Attendance::where('date', '>=', $sevenDaysAgo)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->selectRaw('date, count(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $weeklyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $dayName = Carbon::today()->subDays($i)->locale('id')->isoFormat('ddd');
            $count = $weeklyCounts[$date] ?? 0;
            $percentage = $totalEmployees > 0 ? min(100, round(($count / $totalEmployees) * 100)) : 0;
            $weeklyStats[] = [
                'day' => $dayName,
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        // Recent Activity
        $recentActivities = Attendance::with(['employee', 'institution'])
            ->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->take(6)
            ->get();

        $rawInstitutions = Institution::where('is_active', true)->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'totalInstitutions',
            'presentCount',
            'lateCount',
            'leaveCount',
            'attendanceRate',
            'institutions',
            'rawInstitutions',
            'weeklyStats',
            'recentActivities'
        ));
    }

    // Update Lokasi Absen (Latitude, Longitude, Radius) dari Dashboard
    public function updateLocations(Request $request)
    {
        $validated = $request->validate([
            'institution_id' => 'nullable|exists:institutions,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer|min:10|max:5000',
        ]);

        if (!empty($validated['institution_id'])) {
            $inst = Institution::findOrFail($validated['institution_id']);
            $inst->update([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'radius_meters' => $validated['radius_meters'],
            ]);
            $msg = "Titik lokasi GPS & radius untuk {$inst->name} berhasil diperbarui.";
        } else {
            Institution::query()->update([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'radius_meters' => $validated['radius_meters'],
            ]);
            $msg = 'Titik lokasi GPS wilayah dan batas radius berhasil diperbarui untuk seluruh unit institusi.';
        }

        return redirect()->route('admin.dashboard')->with('success', $msg);
    }
}
