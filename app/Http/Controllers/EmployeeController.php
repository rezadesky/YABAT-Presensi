<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Institution;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('institution');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip_nidn', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($request->filled('institution_id')) {
            $query->where('institution_id', $request->institution_id);
        }

        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->employment_status);
        }

        $employees = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();
        $institutions = Institution::where('is_active', true)->get();

        return view('admin.pegawai', compact('employees', 'institutions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'nip_nidn' => 'required|unique:employees,nip_nidn',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'gender' => 'required|in:L,P',
            'position' => 'required|string|max:255',
            'employment_status' => 'required|in:tetap,kontrak,honorer',
            'join_date' => 'nullable|date',
            'address' => 'nullable|string',
            'password' => 'nullable|string|min:6',
        ]);

        \DB::transaction(function () use ($validated, $request) {
            // Generate email unik jika kosong
            $cleanNip = preg_replace('/[^A-Za-z0-9]/', '', strtolower($validated['nip_nidn']));
            $email = !empty($validated['email']) ? $validated['email'] : ($cleanNip . '@yabat.sch.id');

            // Default password: password yang diisi, atau default 'stkipus2026'
            $rawPassword = !empty($validated['password']) ? $validated['password'] : 'stkipus2026';

            // 1. Buat User Account
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $email,
                'phone' => $validated['phone'] ?? null,
                'password' => \Hash::make($rawPassword),
                'role' => 'pegawai',
            ]);

            // 2. Buat Data Pegawai terhubung dengan user_id
            unset($validated['password']);
            $validated['user_id'] = $user->id;
            $validated['email'] = $email;
            $validated['is_active'] = true;

            Employee::create($validated);
        });

        return redirect()->route('admin.pegawai')->with('success', 'Pegawai & akun login aplikasi mobile berhasil dibuat (Password default: NIP/NIDN).');
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'nip_nidn' => 'required|unique:employees,nip_nidn,' . $employee->id,
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'gender' => 'required|in:L,P',
            'position' => 'required|string|max:255',
            'employment_status' => 'required|in:tetap,kontrak,honorer',
            'join_date' => 'nullable|date',
            'address' => 'nullable|string',
            'password' => 'nullable|string|min:6',
        ]);

        \DB::transaction(function () use ($employee, $validated, $request) {
            // Sinkronkan akun user
            if ($employee->user_id) {
                $user = \App\Models\User::find($employee->user_id);
                if ($user) {
                    $userData = [
                        'name' => $validated['name'],
                        'phone' => $validated['phone'] ?? $user->phone,
                    ];
                    if (!empty($validated['email'])) {
                        $userData['email'] = $validated['email'];
                    }
                    if (!empty($validated['password'])) {
                        $userData['password'] = \Hash::make($validated['password']);
                    }
                    $user->update($userData);
                }
            } else {
                // Jika belum punya user_id, buatkan akunnya
                $cleanNip = preg_replace('/[^A-Za-z0-9]/', '', strtolower($validated['nip_nidn']));
                $email = !empty($validated['email']) ? $validated['email'] : ($cleanNip . '@yabat.sch.id');
                $rawPassword = !empty($validated['password']) ? $validated['password'] : $validated['nip_nidn'];

                $user = \App\Models\User::create([
                    'name' => $validated['name'],
                    'email' => $email,
                    'phone' => $validated['phone'] ?? null,
                    'password' => \Hash::make($rawPassword),
                    'role' => 'pegawai',
                ]);
                $validated['user_id'] = $user->id;
            }

            unset($validated['password']);
            $employee->update($validated);
        });

        return redirect()->route('admin.pegawai')->with('success', 'Data pegawai & akun login berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $userId = $employee->user_id;

        \DB::transaction(function () use ($employee, $userId) {
            $employee->delete();
            if ($userId) {
                \App\Models\User::where('id', $userId)->where('role', 'pegawai')->delete();
            }
        });

        return redirect()->route('admin.pegawai')->with('success', 'Pegawai & akun login berhasil dihapus.');
    }
}
