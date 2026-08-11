@extends('layouts.app')
@section('title', 'Data Pegawai')
@section('header', 'Manajemen Data Pegawai')

@section('content')
<div class="flex sm:flex-row flex-col justify-between items-start sm:items-center gap-3 mb-6">
    <p class="text-gray-500 text-sm">Daftar seluruh pegawai (Dosen & Tendik)</p>
    <div class="flex flex-wrap gap-2">
        <button onclick="document.getElementById('modal-import').classList.remove('hidden')"
            class="bg-green-600 hover:bg-green-700 px-3 py-2 rounded-lg font-medium text-white text-xs">
            Import Excel
        </button>
        <a href="{{ route('pegawai.export') }}"
            class="bg-teal-600 hover:bg-teal-700 px-3 py-2 rounded-lg font-medium text-white text-xs">
            Export Excel
        </a>
        <a href="{{ route('pegawai.create') }}"
            class="bg-[#1E3A5F] hover:bg-[#16304f] px-3 py-2 rounded-lg font-medium text-white text-xs">
            + Tambah Pegawai
        </a>
    </div>
</div>

{{-- Modal Import --}}
<div id="modal-import" class="hidden z-50 fixed inset-0 flex justify-center items-center bg-black bg-opacity-50 p-4">
    <div class="bg-white shadow-xl p-6 rounded-xl w-full max-w-md">
        <h3 class="mb-4 font-semibold text-gray-700">Import Data Pegawai</h3>
        <div class="bg-blue-50 mb-4 p-3 rounded-lg text-blue-700 text-sm">
            ℹ️ Password default akun yang diimport adalah <strong>password123</strong>.
        </div>
        <a href="{{ route('pegawai.template') }}" class="inline-block mb-4 text-green-600 text-sm hover:underline">
            Download Template Excel
        </a>
        <form action="{{ route('pegawai.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block mb-1 font-medium text-gray-700 text-sm">Pilih File Excel</label>
                <input type="file" name="file_import" accept=".xlsx,.xls"
                    class="px-3 py-2 border border-gray-300 rounded-lg w-full text-sm">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-[#1E3A5F] py-2 rounded-lg text-white text-sm">Import</button>
                <button type="button"
                    onclick="document.getElementById('modal-import').classList.add('hidden')"
                    class="flex-1 py-2 border border-gray-300 rounded-lg text-sm">Batal</button>
            </div>
        </form>
    </div>
</div>

<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[700px] text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 font-semibold text-left">No</th>
                    <th class="px-4 py-3 font-semibold text-left">Nama Lengkap</th>
                    <th class="px-4 py-3 font-semibold text-left">NIP/NIDN</th>
                    <th class="px-4 py-3 font-semibold text-left">Jenis</th>
                    <th class="px-4 py-3 font-semibold text-left">Jabatan Aktif</th>
                    <th class="px-4 py-3 font-semibold text-center">Status</th>
                    <th class="px-4 py-3 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($pegawais as $i => $pegawai)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-4 py-3 text-gray-500">{{ $i + 1 }}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $pegawai->nama_lengkap }}</div>
                        <div class="text-gray-400 text-xs">{{ $pegawai->email }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $pegawai->nip ?? $pegawai->nidn ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $pegawai->jenis_pegawai === 'dosen' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ ucfirst($pegawai->jenis_pegawai) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @forelse($pegawai->jabatanAktif as $jabatan)
                            <span class="inline-block bg-gray-100 mr-1 mb-1 px-2 py-0.5 rounded text-gray-700 text-xs">
                                {{ $jabatan->nama }}
                                @if($jabatan->pivot->jenis === 'rangkap')
                                    <span class="text-orange-500">(rangkap)</span>
                                @endif
                            </span>
                        @empty
                            <span class="text-gray-400 text-xs">Belum ada jabatan</span>
                        @endforelse
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $pegawai->status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($pegawai->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center items-center gap-1">
                            <a href="{{ route('pegawai.show', $pegawai->id) }}"
                                class="bg-blue-50 hover:bg-blue-100 px-2 py-1.5 rounded-lg font-medium text-blue-600 text-xs">Detail</a>
                            <a href="{{ route('pegawai.edit', $pegawai->id) }}"
                                class="bg-amber-50 hover:bg-amber-100 px-2 py-1.5 rounded-lg font-medium text-amber-600 text-xs">Edit</a>
                           <form action="{{ route('pegawai.destroy', $pegawai->id) }}" method="POST" 
    class="inline"
    onsubmit="return confirm('Yakin hapus pegawai ini?')">
    @csrf
    @method('DELETE')
    <button type="submit" 
        class="bg-red-50 hover:bg-red-100 px-2 py-1.5 rounded-lg font-medium text-red-600 text-xs">
        Hapus
    </button>
</form>
<form action="{{ route('pegawai.reset-cuti') }}" method="POST" class="inline"
    onsubmit="return confirm('Yakin reset sisa cuti semua pegawai aktif ke 12 hari? Pastikan ini dilakukan di awal tahun!')">
    @csrf
    <button class="bg-red-600 text-white px-3 py-2 rounded-lg text-xs hover:bg-red-700">
        🔄 Reset Cuti Tahunan
    </button>
</form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-gray-400 text-center">Belum ada data pegawai.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection