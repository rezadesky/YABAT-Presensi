@extends('layouts.admin')

@section('title', 'Data Pegawai | YABAT PRESENSI')

@section('content')
<div class="max-w-7xl mx-auto space-y-7 pb-12" x-data="{ openCreateModal: false, openEditModal: false, editData: {} }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                Data Pegawai
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Direktori pendidik dan tenaga kependidikan seluruh unit YABAT
            </p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <button 
                type="button" 
                @click="openCreateModal = true"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Pegawai</span>
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium">
            <div class="font-bold mb-1">Gagal Menyimpan Data:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Filter & Search Bar -->
    <form method="GET" action="{{ route('admin.pegawai') }}" class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input 
                type="text" 
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari nama, NIP/NIDN, atau jabatan..." 
                class="w-full pl-9 pr-3 py-1.5 bg-slate-50/60 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition"
            >
        </div>
        <div class="flex items-center gap-2 flex-wrap text-xs">
            <select name="institution_id" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-slate-50/60 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="">Semua Institusi</option>
                @foreach ($institutions as $inst)
                    <option value="{{ $inst->id }}" {{ request('institution_id') == $inst->id ? 'selected' : '' }}>
                        {{ $inst->name }}
                    </option>
                @endforeach
            </select>
            <select name="employment_status" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-slate-50/60 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="">Status Kepegawaian</option>
                <option value="tetap" {{ request('employment_status') == 'tetap' ? 'selected' : '' }}>Pegawai Tetap</option>
                <option value="kontrak" {{ request('employment_status') == 'kontrak' ? 'selected' : '' }}>Kontrak</option>
                <option value="honorer" {{ request('employment_status') == 'honorer' ? 'selected' : '' }}>Honorer</option>
            </select>
            @if(request()->hasAny(['search', 'institution_id', 'employment_status']))
                <a href="{{ route('admin.pegawai') }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600">Reset</a>
            @endif
        </div>
    </form>

    <!-- Minimal Clean Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="text-[11px] font-semibold text-slate-400 border-b border-slate-100 uppercase tracking-wider">
                        <th class="py-3 px-5">Nama Pegawai</th>
                        <th class="py-3 px-5">Institusi</th>
                        <th class="py-3 px-5">Jabatan</th>
                        <th class="py-3 px-5">Status</th>
                        <th class="py-3 px-5">Kontak</th>
                        <th class="py-3 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($employees as $emp)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-3.5 px-5 font-semibold text-slate-900">
                            <div>{{ $emp->name }}</div>
                            <div class="text-[11px] text-slate-400 font-normal">NIP/ID: {{ $emp->nip_nidn }}</div>
                        </td>
                        <td class="py-3.5 px-5 text-slate-600">{{ $emp->institution->name ?? '-' }}</td>
                        <td class="py-3.5 px-5 text-slate-400">{{ $emp->position }}</td>
                        <td class="py-3.5 px-5">
                            <span class="capitalize text-emerald-600 font-medium">{{ $emp->employment_status }}</span>
                        </td>
                        <td class="py-3.5 px-5 font-mono text-slate-500 text-[11px]">{{ $emp->phone ?? '-' }}</td>
                        <td class="py-3.5 px-5 text-right">
                            <div class="inline-flex items-center gap-2">
                                <button 
                                    type="button" 
                                    @click="editData = {{ json_encode($emp) }}; openEditModal = true"
                                    class="text-blue-600 hover:text-blue-800 font-medium"
                                >
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.pegawai.destroy', $emp->id) }}" onsubmit="return confirm('Hapus data pegawai {{ $emp->name }}?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 font-medium">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">
                            Tidak ada data pegawai yang sesuai dengan pencarian atau filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employees->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Pegawai -->
    <div x-show="openCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto" @click.away="openCreateModal = false">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-base font-bold text-slate-900">Tambah Pegawai Baru</h3>
                <button type="button" @click="openCreateModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.pegawai.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Institusi Unit *</label>
                    <select name="institution_id" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600">
                        @foreach ($institutions as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">NIP / NIDN *</label>
                        <input type="text" name="nip_nidn" required placeholder="Contoh: 0112088501" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" required placeholder="Gelar & Nama" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jabatan *</label>
                        <input type="text" name="position" required placeholder="Dosen / Guru / Staf" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status Kepegawaian *</label>
                        <select name="employment_status" required class="w-full px-3 py-2 border rounded-lg">
                            <option value="tetap">Tetap</option>
                            <option value="kontrak">Kontrak</option>
                            <option value="honorer">Honorer</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jenis Kelamin *</label>
                        <select name="gender" required class="w-full px-3 py-2 border rounded-lg">
                            <option value="L">Laki-laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nomor Telepon / WA</label>
                        <input type="text" name="phone" placeholder="0812-..." class="w-full px-3 py-2 border rounded-lg">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Email Login</label>
                        <input type="email" name="email" placeholder="email@yabat.sch.id (opsional)" class="w-full px-3 py-2 border rounded-lg">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Jika kosong, dibuat otomatis dari NIP.</span>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kata Sandi Akun Mobile</label>
                        <input type="password" name="password" placeholder="Minimal 6 karakter (opsional)" class="w-full px-3 py-2 border rounded-lg">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Jika kosong, default menggunakan NIP/NIDN.</span>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Alamat Tinggal</label>
                    <textarea name="address" rows="2" placeholder="Alamat domisili..." class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>
                <div class="p-2.5 bg-blue-50/70 border border-blue-100 rounded-lg text-[11px] text-blue-700 flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Akun login aplikasi mobile langsung aktif otomatis setelah disimpan. Pegawai bisa login menggunakan NIP atau Email.</span>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2 border rounded-lg text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">Simpan Pegawai & Akun</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Pegawai -->
    <div x-show="openEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto" @click.away="openEditModal = false">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-base font-bold text-slate-900">Ubah Data Pegawai</h3>
                <button type="button" @click="openEditModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form :action="'{{ url('/admin/pegawai') }}/' + editData.id" method="POST" class="space-y-3.5 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Institusi Unit *</label>
                    <select name="institution_id" x-model="editData.institution_id" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600">
                        @foreach ($institutions as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">NIP / NIDN *</label>
                        <input type="text" name="nip_nidn" x-model="editData.nip_nidn" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" x-model="editData.name" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jabatan *</label>
                        <input type="text" name="position" x-model="editData.position" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status Kepegawaian *</label>
                        <select name="employment_status" x-model="editData.employment_status" required class="w-full px-3 py-2 border rounded-lg">
                            <option value="tetap">Tetap</option>
                            <option value="kontrak">Kontrak</option>
                            <option value="honorer">Honorer</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jenis Kelamin *</label>
                        <select name="gender" x-model="editData.gender" required class="w-full px-3 py-2 border rounded-lg">
                            <option value="L">Laki-laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nomor Telepon / WA</label>
                        <input type="text" name="phone" x-model="editData.phone" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Email Login</label>
                        <input type="email" name="email" x-model="editData.email" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Ganti Kata Sandi (Opsional)</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Alamat Tinggal</label>
                    <textarea name="address" x-model="editData.address" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2 border rounded-lg text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
