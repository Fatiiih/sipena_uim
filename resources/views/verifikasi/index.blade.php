@extends('layouts.app')
@section('title', 'Verifikasi Berkas')
@section('header', 'Verifikasi Berkas Jafa & Serdos')

@section('content')
{{-- Verifikasi Jafa --}}
<div class="mb-8">
    <h3 class="font-semibold text-gray-700 mb-4">📋 Berkas Jafa Masuk ({{ $jafas->count() }})</h3>
    @forelse($jafas as $jafa)
    <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
        <div class="flex justify-between items-start">
            <div>
                <h4 class="font-semibold text-gray-800">{{ $jafa->pegawai->nama_lengkap }}</h4>
                <p class="text-xs font-mono text-gray-500">{{ $jafa->nomor_surat }}</p>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $jafa->jabatan_fungsional_sekarang }} → <strong>{{ $jafa->jabatan_fungsional_diajukan }}</strong>
                </p>
                @if($jafa->file_scan)
                    <a href="{{ $jafa->file_scan }}" target="_blank"
                        class="text-xs text-green-600 hover:underline mt-1 inline-block">
                        📎 Lihat Scan TTD Rektor
                    </a>
                @else
                    <p class="text-xs text-red-500 mt-1">⚠️ Belum ada scan tanda tangan</p>
                @endif
            </div>
            <form action="{{ route('verifikasi.jafa', $jafa->id) }}" method="POST" class="flex flex-col gap-2 min-w-[200px]">
                @csrf
                <textarea name="catatan" rows="2" placeholder="Catatan (opsional)"
                    class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:outline-none"></textarea>
                <div class="flex gap-2">
                    <button type="submit" name="status" value="diverifikasi"
                        class="flex-1 bg-green-500 text-white py-2 rounded-lg text-sm hover:bg-green-600">
                        ✅ Verifikasi
                    </button>
                    <button type="submit" name="status" value="ditolak"
                        class="flex-1 bg-red-500 text-white py-2 rounded-lg text-sm hover:bg-red-600">
                        ❌ Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-400">
        Tidak ada berkas Jafa yang perlu diverifikasi.
    </div>
    @endforelse
</div>

{{-- Verifikasi Serdos --}}
<div>
    <h3 class="font-semibold text-gray-700 mb-4">🎓 Berkas Serdos Masuk ({{ $serdos->count() }})</h3>
    @forelse($serdos as $s)
    <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
        <div class="flex justify-between items-start">
            <div>
                <h4 class="font-semibold text-gray-800">{{ $s->pegawai->nama_lengkap }}</h4>
                <p class="text-xs font-mono text-gray-500">{{ $s->nomor_surat }}</p>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $s->program_studi }} | {{ $s->bidang_ilmu }}
                </p>
                @if($s->file_scan)
                    <a href="{{ $s->file_scan }}" target="_blank"
                        class="text-xs text-green-600 hover:underline mt-1 inline-block">
                        📎 Lihat Scan TTD Rektor
                    </a>
                @else
                    <p class="text-xs text-red-500 mt-1">⚠️ Belum ada scan tanda tangan</p>
                @endif
            </div>
            <form action="{{ route('verifikasi.serdos', $s->id) }}" method="POST" class="flex flex-col gap-2 min-w-[200px]">
                @csrf
                <textarea name="catatan" rows="2" placeholder="Catatan (opsional)"
                    class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:outline-none"></textarea>
                <div class="flex gap-2">
                    <button type="submit" name="status" value="diverifikasi"
                        class="flex-1 bg-green-500 text-white py-2 rounded-lg text-sm hover:bg-green-600">
                        ✅ Verifikasi
                    </button>
                    <button type="submit" name="status" value="ditolak"
                        class="flex-1 bg-red-500 text-white py-2 rounded-lg text-sm hover:bg-red-600">
                        ❌ Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-400">
        Tidak ada berkas Serdos yang perlu diverifikasi.
    </div>
    @endforelse
</div>
@endsection