<?php

namespace App\Http\Controllers;

use App\Services\SerdosService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SerdosController extends Controller
{
    public function __construct(protected SerdosService $serdosService) {}

    public function index()
{
    $pegawai = auth()->user()->pegawai;

    if (!$pegawai) {
        $surat = collect();
        return view('serdos.index', compact('surat', 'pegawai'));
    }

    $surat = $this->serdosService->getByPegawai($pegawai->id);
    return view('serdos.index', compact('surat', 'pegawai'));
}

public function store(Request $request)
{
    $request->validate([
        'program_studi'        => 'required|string',
        'bidang_ilmu'          => 'required|string',
        'jumlah_sks'           => 'required|integer|min:1',
        'tahun_mulai_mengajar' => 'required|integer|min:1990',
        'mata_kuliah'          => 'nullable|string',
    ]);

    $pegawai = auth()->user()->pegawai;

    if (!$pegawai) {
        return redirect()->route('serdos.index')
            ->with('error', 'Data pegawai tidak ditemukan. Hubungi Admin SDM.');
    }

    $this->serdosService->create($request->all(), $pegawai->id);
    return redirect()->route('serdos.index')->with('success', 'Surat Serdos berhasil diajukan!');
}
}
