<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Services\SuratKeputusanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class SuratKeputusanController extends Controller
{
    public function __construct(protected SuratKeputusanService $skService) {}

    public function index()
    {
        $skList = $this->skService->getAll();
        return view('surat-keputusan.index', compact('skList'));
    }

    public function create()
    {
        $pegawais = Pegawai::orderBy('nama_lengkap')->get();
        return view('surat-keputusan.create', compact('pegawais'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id'              => 'required|exists:pegawais,id',
            'jenis_sk'                => 'required|in:pengangkatan,jabatan_fungsional,jabatan_struktural',
            'jabatan_yang_ditetapkan' => 'required|string',
            'tanggal_sk'              => 'required|date',
            'tmt'                     => 'nullable|date',
            'pertimbangan'            => 'nullable|string',
            'keterangan'              => 'nullable|string',
        ]);

        $this->skService->create($request->all());
        return redirect()->route('surat-keputusan.index')
            ->with('success', 'SK berhasil dibuat.');
    }

    public function show(int $id)
    {
        $sk = $this->skService->findById($id);
        return view('surat-keputusan.show', compact('sk'));
    }

    public function edit(int $id)
    {
        $sk       = $this->skService->findById($id);
        $pegawais = Pegawai::orderBy('nama_lengkap')->get();
        return view('surat-keputusan.edit', compact('sk', 'pegawais'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'jabatan_yang_ditetapkan' => 'required|string',
            'tanggal_sk'              => 'required|date',
            'tmt'                     => 'nullable|date',
            'pertimbangan'            => 'nullable|string',
            'keterangan'              => 'nullable|string',
            'status'                  => 'required|in:draft,diterbitkan,tidak_berlaku',
        ]);

        $this->skService->update($id, $request->all());
        return redirect()->route('surat-keputusan.index')
            ->with('success', 'SK berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $this->skService->delete($id);
        return redirect()->route('surat-keputusan.index')
            ->with('success', 'SK berhasil dihapus.');
    }

    public function terbitkan(int $id)
    {
        $this->skService->terbitkan($id);
        return back()->with('success', 'SK berhasil diterbitkan.');
    }

   public function cetakPdf(int $id)
{
    $sk  = $this->skService->findById($id);
    $pdf = Pdf::loadView('surat-keputusan.pdf', compact('sk'))
              ->setPaper('a4', 'portrait');
    
    $nomorFile = str_replace(['/', '\\', ' '], '-', $sk->nomor_sk);
    
    return $pdf->download("sk-{$nomorFile}.pdf");
}

    public function uploadFile(Request $request, int $id)
    {
        $request->validate([
            'file_sk' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
        $uploaded = Cloudinary::upload(
            $request->file('file_sk')->getRealPath(),
            ['folder' => 'file-sk', 'resource_type' => 'auto']
        );
        $this->skService->uploadFileSK($id, $uploaded->getSecurePath());
        return back()->with('success', 'File SK berhasil diupload.');
    }

    // Halaman SK untuk pegawai sendiri
    public function milik()
    {
        $pegawai = auth()->user()->pegawai;
        $skList  = $this->skService->getByPegawai($pegawai->id);
        return view('surat-keputusan.milik', compact('skList', 'pegawai'));
    }
}