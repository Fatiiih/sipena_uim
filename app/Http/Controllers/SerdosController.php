<?php

namespace App\Http\Controllers;

use App\Services\SerdosService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class SerdosController extends Controller
{
    public function __construct(protected SerdosService $serdosService) {}

    public function index()
    {
        $pegawai = auth()->user()->pegawai;
        $surat   = $this->serdosService->getByPegawai($pegawai->id);
        return view('serdos.index', compact('surat', 'pegawai'));
    }

    public function create()
    {
        return view('serdos.create');
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
        $this->serdosService->create($request->all(), $pegawai->id);

        return redirect()->route('serdos.index')->with('success', 'Surat Serdos berhasil diajukan!');
    }

    public function show(int $id)
    {
        $surat = $this->serdosService->findById($id);
        return view('serdos.show', compact('surat'));
    }

    public function cetakPdf(int $id)
    {
        $surat = $this->serdosService->findById($id);
        $pdf   = Pdf::loadView('serdos.pdf', compact('surat'))
                    ->setPaper('a4', 'portrait');
        
        // Sanitasi filename: hapus karakter yang tidak boleh di filename
        // Ganti / \ : * ? " < > | dengan -
        $sanitizedFilename = preg_replace('/[\/\\:*?"<>|]/', '-', $surat->nomor_surat);
        
        return $pdf->download("surat-serdos-{$sanitizedFilename}.pdf");
    }

    public function uploadScan(Request $request, int $id)
    {
        $request->validate([
            'file_scan' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $uploaded = Cloudinary::upload(
            $request->file('file_scan')->getRealPath(),
            ['folder' => 'scan-serdos', 'resource_type' => 'auto']
        );
        $this->serdosService->uploadScan($id, $uploaded->getSecurePath());

        return back()->with('success', 'File scan berhasil diupload.');
    }
}