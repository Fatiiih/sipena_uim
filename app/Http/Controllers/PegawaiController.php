<?php

namespace App\Http\Controllers;

use App\Models\Pegawai; 
use App\Services\PegawaiService;
use App\Services\JabatanService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Exports\PegawaiExport;
use App\Imports\PegawaiImport;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class PegawaiController extends Controller
{
    public function __construct(
        protected PegawaiService $pegawaiService,
        protected JabatanService $jabatanService,
    ) {}

    public function index()
    {
        $pegawais = $this->pegawaiService->getAll();
        return view('pegawai.index', compact('pegawais'));
    }

    public function create()
    {
        $jabatans = $this->jabatanService->getAll();
        $roles    = Role::all();
        return view('pegawai.create', compact('jabatans', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap'  => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:6',
            'jenis_kelamin' => 'required|in:L,P',
            'jenis_pegawai' => 'required|in:dosen,tendik',
            'status'        => 'required|in:aktif,nonaktif,pensiun',
            'role'          => 'required|exists:roles,name',
            'nip'           => 'nullable|unique:pegawais,nip',
            'nidn'          => 'nullable|unique:pegawais,nidn',
            'jabatan_id'    => 'nullable|exists:jabatans,id',
            'foto'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();

        // Handle foto upload -> Cloudinary
        if ($request->hasFile('foto')) {
            $uploaded = Cloudinary::upload(
                $request->file('foto')->getRealPath(),
                ['folder' => 'foto-pegawai']
            );
            $data['foto'] = $uploaded->getSecurePath();
        }

        $this->pegawaiService->create($data);
        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function show(int $id)
    {
        $pegawai  = $this->pegawaiService->findById($id);
        $jabatans = $this->jabatanService->getAll();
        return view('pegawai.show', compact('pegawai', 'jabatans'));
    }

    public function edit(int $id)
    {
        $pegawai  = $this->pegawaiService->findById($id);
        $jabatans = $this->jabatanService->getAll();
        $roles    = Role::all();
        return view('pegawai.edit', compact('pegawai', 'jabatans', 'roles'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'nama_lengkap'  => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $this->pegawaiService->findById($id)->user_id,
            'jenis_kelamin' => 'required|in:L,P',
            'jenis_pegawai' => 'required|in:dosen,tendik',
            'status'        => 'required|in:aktif,nonaktif,pensiun',
            'role'          => 'required|exists:roles,name',
            'foto'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();

        // Handle foto upload -> Cloudinary
        if ($request->hasFile('foto')) {
            $uploaded = Cloudinary::upload(
                $request->file('foto')->getRealPath(),
                ['folder' => 'foto-pegawai']
            );
            $data['foto'] = $uploaded->getSecurePath();
        }

        $this->pegawaiService->update($id, $data);
        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $this->pegawaiService->delete($id);
        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }

    // Assign jabatan tambahan (multi-role)
    public function assignJabatan(Request $request, int $id)
    {
        $request->validate([
            'jabatan_id' => 'required|exists:jabatans,id',
            'jenis'      => 'required|in:utama,rangkap',
        ]);

        $this->pegawaiService->assignJabatan($id, $request->jabatan_id, $request->jenis);
        return back()->with('success', 'Jabatan berhasil ditambahkan.');
    }

    // Revoke jabatan
    public function revokeJabatan(int $pegawaiId, int $jabatanId)
    {
        $this->pegawaiService->revokeJabatan($pegawaiId, $jabatanId);
        return back()->with('success', 'Jabatan berhasil dinonaktifkan.');
    }

    // Export Excel
    // CATATAN: pakai storage_path('app/') biasa (bukan '/public/'), dan hapus
    // '/tmp/' prefix ini WAJIB di Vercel karena hanya folder /tmp yang writable.
    public function exportExcel()
    {
        $export      = new PegawaiExport();
        $spreadsheet = $export->download();
        $writer      = new Xlsx($spreadsheet);
        $filename    = 'data-pegawai-' . now()->format('Ymd') . '.xlsx';
        $path        = sys_get_temp_dir() . '/' . $filename;
        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    // Download template import
    public function templateImport()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import');

        $headers = ['Nama Lengkap*', 'NIP', 'NIDN', 'Email*', 'Jenis Pegawai* (dosen/tendik)',
                    'Jenis Kelamin* (L/P)', 'No HP'];

        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . '1', $h);
            $sheet->getColumnDimension($col)->setWidth(25);
        }

        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                       'startColor' => ['rgb' => '1E3A5F']],
        ]);

        // Contoh data
        $sheet->setCellValue('A2', 'Dr. Ahmad Contoh');
        $sheet->setCellValue('C2', '0123456789');
        $sheet->setCellValue('D2', 'ahmad@uim.ac.id');
        $sheet->setCellValue('E2', 'dosen');
        $sheet->setCellValue('F2', 'L');
        $sheet->setCellValue('G2', '081234567890');

        $writer   = new Xlsx($spreadsheet);
        $filename = 'template-import-pegawai.xlsx';
        $path     = sys_get_temp_dir() . '/' . $filename;
        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    // Import Excel
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_import' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        // Simpan sementara ke /tmp (bukan storage/app) karena filesystem
        // Vercel read-only kecuali folder /tmp
        $tmpPath = sys_get_temp_dir() . '/' . Str::ulid() . '.' . $request->file('file_import')->getClientOriginalExtension();
        $request->file('file_import')->move(dirname($tmpPath), basename($tmpPath));

        $import = new PegawaiImport();
        $import->import($tmpPath);

        $message = "Berhasil import {$import->success} pegawai.";
        if (!empty($import->errors)) {
            $message .= ' Terdapat ' . count($import->errors) . ' baris error: ' . implode(' | ', $import->errors);
        }

        @unlink($tmpPath);

        return redirect()->route('pegawai.index')->with('success', $message);
    }

    public function uploadFoto(Request $request, int $id)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $uploaded = Cloudinary::upload(
            $request->file('foto')->getRealPath(),
            ['folder' => 'foto-pegawai']
        );

        $pegawai       = Pegawai::findOrFail($id);
        $pegawai->foto = $uploaded->getSecurePath();
        $pegawai->save();

        return response()->json(['foto' => $uploaded->getSecurePath()]);
    }
}