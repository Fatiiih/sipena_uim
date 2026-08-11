@extends('layouts.app')
@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')

{{-- ── Hero Profil ── --}}
<div class="bg-[#1E3A5F] mb-6 p-5 lg:p-6 rounded-2xl text-white">
    <div class="flex items-center gap-4">
        <img src="{{ $pegawai?->foto ? (str_starts_with($pegawai->foto, 'http') ? $pegawai->foto : asset('storage/'.$pegawai->foto)) : 'https://ui-avatars.com/api/?name='.urlencode($pegawai?->nama_lengkap ?? auth()->user()->name).'&background=ffffff&color=1E3A5F' }}"
            class="w-16 h-16 rounded-2xl object-cover border-2 border-white/30 flex-shrink-0">
        <div class="flex-1 min-w-0">
            <p class="text-blue-200 text-xs">Selamat datang kembali,</p>
            <h2 class="font-bold text-lg lg:text-xl truncate">{{ $pegawai?->nama_lengkap ?? auth()->user()->name }}</h2>
            <div class="flex flex-wrap gap-2 mt-1.5">
                <span class="bg-white/15 px-2.5 py-0.5 border border-white/20 rounded-full text-blue-100 text-xs">
                    {{ ucwords(str_replace('_', ' ', auth()->user()->getRoleNames()->first())) }}
                </span>
                @if($pegawai)
                <span class="bg-green-500/20 px-2.5 py-0.5 border border-green-400/30 rounded-full text-green-300 text-xs">
                    {{ ucfirst($pegawai->status) }}
                </span>
                @endif
            </div>
        </div>
    </div>

    @if($pegawai)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 pt-4 border-t border-white/10">
        <div>
            <p class="text-blue-300 text-xs">NIP</p>
            <p class="text-white text-sm font-medium">{{ $pegawai->nip ?? '-' }}</p>
        </div>
        <div>
            <p class="text-blue-300 text-xs">NIDN</p>
            <p class="text-white text-sm font-medium">{{ $pegawai->nidn ?? '-' }}</p>
        </div>
        <div>
            <p class="text-blue-300 text-xs">No HP</p>
            <p class="text-white text-sm font-medium">{{ $pegawai->no_hp ?? '-' }}</p>
        </div>
        <div>
            <p class="text-blue-300 text-xs">Jabatan Aktif</p>
            <p class="text-white text-sm font-medium truncate">
                {{ $pegawai->jabatanAktif->first()?->nama ?? '-' }}
            </p>
        </div>
    </div>
    @endif
</div>

{{-- ── Stats ── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 border border-gray-200 rounded-xl">
        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Sisa Cuti</p>
        <h3 class="font-bold text-2xl mt-1 {{ $sisaCuti < 0 ? 'text-red-500' : 'text-[#1E3A5F]' }}">{{ $sisaCuti }}</h3>
        <p class="text-gray-400 text-xs mt-0.5">hari tersisa</p>
    </div>
    <div class="bg-white p-4 border border-gray-200 rounded-xl">
        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Pengajuan</p>
        <h3 class="font-bold text-green-600 text-2xl mt-1">{{ $totalPengajuan }}</h3>
        <p class="text-gray-400 text-xs mt-0.5">total cuti</p>
    </div>
    @if($queueCuti > 0 || $queueLembur > 0)
    <div class="bg-white p-4 border border-amber-200 rounded-xl">
        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Antrian Cuti</p>
        <h3 class="font-bold text-amber-500 text-2xl mt-1">{{ $queueCuti }}</h3>
        <p class="text-gray-400 text-xs mt-0.5">menunggu approval</p>
    </div>
    <div class="bg-white p-4 border border-orange-200 rounded-xl">
        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Antrian Lembur</p>
        <h3 class="font-bold text-orange-500 text-2xl mt-1">{{ $queueLembur }}</h3>
        <p class="text-gray-400 text-xs mt-0.5">menunggu approval</p>
    </div>
    @else
    <div class="bg-white p-4 border border-gray-200 rounded-xl">
        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Status Jafa</p>
        <h3 class="font-bold text-purple-600 text-lg mt-1">{{ $jafaProgress ? ucfirst($jafaProgress->status) : '-' }}</h3>
        <p class="text-gray-400 text-xs mt-0.5">pengajuan terakhir</p>
    </div>
    <div class="bg-white p-4 border border-gray-200 rounded-xl">
        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">Status Serdos</p>
        <h3 class="font-bold text-indigo-600 text-lg mt-1">{{ $serdosProgress ? ucfirst($serdosProgress->status) : '-' }}</h3>
        <p class="text-gray-400 text-xs mt-0.5">pengajuan terakhir</p>
    </div>
    @endif
</div>

{{-- ── Kontrak Aktif (kalau ada) ── --}}
@if($kontrakAktif)
<div class="bg-white p-4 border border-gray-200 rounded-xl mb-6">
    <div class="flex justify-between items-center">
        <div>
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Kontrak Aktif</p>
            <p class="font-medium text-gray-800 text-sm">{{ $kontrakAktif->nomor_kontrak }}</p>
            <p class="text-gray-400 text-xs mt-0.5">
                {{ $kontrakAktif->tanggal_mulai?->format('d/m/Y') }} — {{ $kontrakAktif->tanggal_selesai?->format('d/m/Y') ?? 'Tidak terbatas' }}
            </p>
        </div>
        @php $sisa = $kontrakAktif->sisa_hari; @endphp
        <span class="px-3 py-1.5 rounded-full text-xs font-medium
            {{ $sisa <= 30 ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
            {{ $sisa > 0 ? $sisa . ' hari lagi' : 'Kadaluarsa' }}
        </span>
    </div>
</div>
@endif

{{-- ── Aksi Cepat ── --}}
<div class="bg-white p-5 border border-gray-200 rounded-xl">
    <h3 class="font-semibold text-gray-700 text-sm mb-4">Aksi Cepat</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">

        @if($queueCuti > 0 || $queueLembur > 0)
        <a href="{{ route('approval.index') }}"
            class="flex items-center gap-3 bg-amber-50 hover:bg-amber-100 p-3 border border-amber-200 rounded-xl transition-all">
            <div class="flex-shrink-0 w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="font-medium text-gray-700 text-sm">Proses Approval</span>
        </a>
        @endif

        <a href="{{ route('cuti.create') }}"
            class="flex items-center gap-3 bg-gray-50 hover:bg-blue-50 p-3 border border-transparent hover:border-blue-200 rounded-xl transition-all">
            <div class="flex-shrink-0 w-8 h-8 bg-[#1E3A5F]/10 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-[#1E3A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <span class="font-medium text-gray-700 text-sm">Ajukan Cuti</span>
        </a>

        <a href="{{ route('lembur.create') }}"
            class="flex items-center gap-3 bg-gray-50 hover:bg-orange-50 p-3 border border-transparent hover:border-orange-200 rounded-xl transition-all">
            <div class="flex-shrink-0 w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="font-medium text-gray-700 text-sm">Ajukan Lembur</span>
        </a>

        @if(auth()->user()->hasAnyRole(['dosen', 'rektor', 'wakil_rektor', 'dekan', 'kaprodi', 'ka_biro']))
        <a href="{{ route('jafa.create') }}"
            class="flex items-center gap-3 bg-gray-50 hover:bg-green-50 p-3 border border-transparent hover:border-green-200 rounded-xl transition-all">
            <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <span class="font-medium text-gray-700 text-sm">Ajukan Jafa</span>
        </a>

        <a href="{{ route('serdos.create') }}"
            class="flex items-center gap-3 bg-gray-50 hover:bg-purple-50 p-3 border border-transparent hover:border-purple-200 rounded-xl transition-all">
            <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 7v-7"/>
                </svg>
            </div>
            <span class="font-medium text-gray-700 text-sm">Ajukan Serdos</span>
        </a>
        @endif

        <a href="{{ route('kontrak-kerja.milik') }}"
            class="flex items-center gap-3 bg-gray-50 hover:bg-teal-50 p-3 border border-transparent hover:border-teal-200 rounded-xl transition-all">
            <div class="flex-shrink-0 w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <span class="font-medium text-gray-700 text-sm">Kontrak Saya</span>
        </a>

        <a href="{{ route('surat-keputusan.milik') }}"
            class="flex items-center gap-3 bg-gray-50 hover:bg-indigo-50 p-3 border border-transparent hover:border-indigo-200 rounded-xl transition-all">
            <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <span class="font-medium text-gray-700 text-sm">SK Saya</span>
        </a>
    </div>
</div>
@endsection