<?php

namespace App\Http\Controllers;

use App\Services\JafaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class JafaController extends Controller
{
    public function __construct(protected JafaService $jafaService) {}

    public function index()
    {
        $surat = $this->jafaService->getAll();
        return view('jafa.index', compact('surat'));
    }

    public function create()
    {
        return view('jafa.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'jabatan_fungsional_diajukan' => 'required|string',
            'jabatan_fungsional_sekarang' => 'required|string',
            'pangkat_golongan'            => 'required|string',
            'tmt_pangkat'                 => 'required|date',
            'daftar_karya'                => 'nullable|string',
        ]);

        $pegawai = auth()->user()->pegawai;
        $this->jafaService->create($request->all(), $pegawai->id);

        return redirect()->route('jafa.index')->with('success', 'Surat Jafa berhasil diajukan!');
    }

    public function show(int $id)
    {
        $surat = $this->jafaService->findById($id);
        return view('jafa.show', compact('surat'));
    }

    public function cetakPdf(int $id)
    {
        $surat = $this->jafaService->findById($id);
        $pdf   = Pdf::loadView('jafa.pdf', compact('surat'))
                    ->setPaper('a4', 'portrait');
        
        // Sanitasi filename: hapus karakter yang tidak boleh di filename
        // Ganti / \ : * ? " < > | dengan -
        $sanitizedFilename = preg_replace('/[\/\\:*?"<>|]/', '-', $surat->nomor_surat);
        
        return $pdf->download("surat-jafa-{$sanitizedFilename}.pdf");
    }

    public function uploadScan(Request $request, int $id)
    {
        $request->validate([
            'file_scan' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $uploaded = Cloudinary::upload(
            $request->file('file_scan')->getRealPath(),
            ['folder' => 'scan-jafa', 'resource_type' => 'auto']
        );
        $this->jafaService->uploadScan($id, $uploaded->getSecurePath());

        return back()->with('success', 'File scan berhasil diupload.');
    }
}