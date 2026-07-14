@extends('layouts.app')
@section('title', 'Detail SK')
@section('header', 'Detail Surat Keputusan')

@section('content')
<div class="max-w-2xl space-y-4">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex justify-between items-start mb-5">
            <div>
                <h3 class="font-semibold text-gray-800">{{ $sk->pegawai->nama_lengkap }}</h3>
                <p class="text-xs font-mono text-gray-500 mt-0.5">{{ $sk->nomor_sk }}</p>
            </div>
            <div class="flex gap-2 flex-wrap justify-end">
                @if($sk->status === 'draft')
                <form action="{{ route('surat-keputusan.terbitkan', $sk->id) }}" method="POST">
                    @csrf
                    <button class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">Terbitkan</button>
                </form>
                @endif
                <a href="{{ route('surat-keputusan.pdf', $sk->id) }}"
                    class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700">Download PDF</a>
                <span class="px-3 py-2 rounded-full text-sm font-medium
                    {{ $sk->status === 'diterbitkan' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $sk->status === 'draft' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $sk->status === 'tidak_berlaku' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $sk->status)) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-400">Jenis SK</p>
                <p class="font-medium">{{ $sk->jenis_sk_label }}</p>
            </div>
            <div>
                <p class="text-gray-400">Jabatan Ditetapkan</p>
                <p class="font-medium">{{ $sk->jabatan_yang_ditetapkan }}</p>
            </div>
            <div>
                <p class="text-gray-400">Tanggal SK</p>
                <p class="font-medium">{{ $sk->tanggal_sk->format('d F Y') }}</p>
            </div>
            <div>
                <p class="text-gray-400">TMT</p>
                <p class="font-medium">{{ $sk->tmt ? $sk->tmt->format('d F Y') : '-' }}</p>
            </div>
            @if($sk->pertimbangan)
            <div class="col-span-2">
                <p class="text-gray-400">Pertimbangan</p>
                <p class="font-medium">{{ $sk->pertimbangan }}</p>
            </div>
            @endif
            @if($sk->keterangan)
            <div class="col-span-2">
                <p class="text-gray-400">Keterangan</p>
                <p class="font-medium">{{ $sk->keterangan }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Upload File SK --}}
    @if(!$sk->file_sk)
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h4 class="font-semibold text-gray-700 text-sm mb-3">Upload File SK (PDF/Scan)</h4>
        <form action="{{ route('surat-keputusan.upload', $sk->id) }}" method="POST" enctype="multipart/form-data" class="flex gap-3">
            @csrf
            <input type="file" name="file_sk" accept=".pdf,.jpg,.jpeg,.png"
                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <button type="submit" class="bg-[#1E3A5F] text-white px-4 py-2 rounded-lg text-sm hover:bg-[#16304f]">Upload</button>
        </form>
    </div>
    @else
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700">
        ✅ File SK sudah diupload.
        <a href="{{ $sk->file_sk }}" target="_blank" class="underline ml-1">Lihat File</a>
    </div>
    @endif

    <a href="{{ route('surat-keputusan.index') }}" class="text-gray-500 hover:underline text-sm">← Kembali</a>
</div>
@endsection