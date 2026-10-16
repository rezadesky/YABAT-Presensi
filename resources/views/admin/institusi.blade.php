@extends('layouts.admin')

@section('title', 'Institusi | YABAT PRESENSI')

@section('content')
<div class="max-w-7xl mx-auto space-y-7 pb-12">
    <!-- Header with Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                Unit Institusi
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Lembaga pendidikan naungan Yayasan Anak Bangsa Aceh Tenggara
            </p>
        </div>
        <button 
            type="button" 
            onclick="openCreateModal()"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Institusi Baru</span>
        </button>
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

    <!-- Grid Institution Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse ($institutions as $inst)
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 hover:border-slate-300 transition-colors flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-semibold text-blue-600 uppercase tracking-wider">{{ $inst->category }}</span>
                    <span class="text-emerald-600 text-xs font-medium">Aktif</span>
                </div>
                <h2 class="text-base font-bold text-slate-900">{{ $inst->name }}</h2>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                    {{ $inst->description ?? 'Lembaga pendidikan formal di bawah naungan Yayasan Anak Bangsa.' }}
                </p>

                <div class="mt-5 space-y-2 text-xs border-t border-slate-100 pt-3 text-slate-600">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Kode Unit:</span>
                        <span class="font-mono text-slate-900 font-semibold">{{ $inst->code }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Staf Terdaftar:</span>
                        <span class="font-semibold text-slate-900">{{ $inst->employees_count }} Pegawai</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Titik Koordinat:</span>
                        <span class="font-mono text-slate-700 font-medium text-[11px]">
                            {{ $inst->latitude ? number_format($inst->latitude, 4) . ', ' . number_format($inst->longitude, 4) : 'Belum diset' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Radius Presensi:</span>
                        <span class="font-mono text-emerald-600 font-bold">{{ $inst->radius_meters }} Meter</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Pimpinan:</span>
                        <span class="text-slate-700 font-medium">{{ $inst->head_name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Per Institusi -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                <button 
                    type="button" 
                    onclick="openEditModal({{ json_encode($inst) }})"
                    class="flex-1 py-1.5 px-3 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold flex items-center justify-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Edit Lokasi GPS</span>
                </button>
                @if ($inst->employees_count == 0)
                <form action="{{ route('admin.institusi.destroy', $inst->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus unit institusi ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50" title="Hapus Unit">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-4 bg-white p-8 rounded-xl border text-center text-slate-400">
            Tidak ada unit institusi terdaftar.
        </div>
        @endforelse
    </div>

    <!-- Modal Tambah Institusi Baru -->
    <div id="modalCreate" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Tambah Unit Institusi Baru</h3>
                    <p class="text-xs text-slate-400">Daftarkan lembaga pendidikan baru di bawah naungan YABAT</p>
                </div>
                <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.institusi.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Nama Institusi *</label>
                        <input type="text" name="name" required placeholder="Contoh: SMA Islam YABAT" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Kode Unit *</label>
                        <input type="text" name="code" required placeholder="Contoh: SMA-YBT" class="w-full px-3 py-2 border rounded-lg uppercase font-mono focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Kategori Lembaga *</label>
                        <input type="text" name="category" required placeholder="Contoh: Sekolah Menengah Atas" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Nama Pimpinan / Kepala</label>
                        <input type="text" name="head_name" placeholder="Gelar & Nama Kepala" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Latitude</label>
                        <input type="text" name="latitude" placeholder="3.4883" class="w-full px-3 py-2 border rounded-lg font-mono focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Longitude</label>
                        <input type="text" name="longitude" placeholder="97.8085" class="w-full px-3 py-2 border rounded-lg font-mono focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Radius (Meter) *</label>
                        <input type="number" name="radius_meters" value="100" min="10" max="5000" required class="w-full px-3 py-2 border rounded-lg font-mono focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Alamat Kampus / Sekolah</label>
                    <textarea name="address" rows="2" placeholder="Pulonas Baru, Kec. Lawe Bulan..." class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeCreateModal()" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-semibold shadow-sm">Simpan Institusi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Lokasi Institusi -->
    <div id="modalEdit" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900" id="modalTitle">Atur Lokasi GPS Unit</h3>
                    <p class="text-xs text-slate-400" id="modalSubtitle">Ubah koordinat dan batas radius presensi pegawai</p>
                </div>
                <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="formEditInst" method="POST" action="" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" id="modalInputName">
                <input type="hidden" name="code" id="modalInputCode">
                <input type="hidden" name="category" id="modalInputCategory">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Latitude</label>
                        <input type="text" name="latitude" id="modalInputLat" required class="w-full px-3 py-2 border rounded-lg font-mono focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Longitude</label>
                        <input type="text" name="longitude" id="modalInputLng" required class="w-full px-3 py-2 border rounded-lg font-mono focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Radius Presensi (Meter)</label>
                    <input type="number" name="radius_meters" id="modalInputRad" min="10" max="5000" required class="w-full px-3 py-2 border rounded-lg font-mono focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    <span class="text-[10px] text-slate-400 mt-1 block">Toleransi maksimal jarak HP pegawai dari titik pusat gedung unit ini.</span>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Alamat Kampus / Sekolah</label>
                    <textarea name="address" id="modalInputAddress" rows="2" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-600 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-semibold shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('modalCreate').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('modalCreate').classList.add('hidden');
        }

        function openEditModal(inst) {
            document.getElementById('modalTitle').innerText = 'Atur Lokasi GPS ' + inst.name;
            document.getElementById('modalSubtitle').innerText = 'Kode Unit: ' + inst.code;
            document.getElementById('formEditInst').action = '/admin/institusi/' + inst.id;

            document.getElementById('modalInputName').value = inst.name;
            document.getElementById('modalInputCode').value = inst.code;
            document.getElementById('modalInputCategory').value = inst.category;

            document.getElementById('modalInputLat').value = inst.latitude ?? '';
            document.getElementById('modalInputLng').value = inst.longitude ?? '';
            document.getElementById('modalInputRad').value = inst.radius_meters ?? 100;
            document.getElementById('modalInputAddress').value = inst.address ?? '';

            document.getElementById('modalEdit').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('modalEdit').classList.add('hidden');
        }
    </script>
</div>
@endsection
