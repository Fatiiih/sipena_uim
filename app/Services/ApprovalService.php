<?php

namespace App\Services;

use App\Models\CutiApproval;
use App\Models\LemburApproval;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function __construct(
        protected NotifikasiService $notifikasiService
    ) {}

    // Ambil queue cuti untuk approver yang sedang login
    public function getQueueCuti(int $userId)
    {
        return CutiApproval::with(['pengajuanCuti.pegawai', 'jabatan'])
            ->where('approver_id', $userId)
            ->where('status', 'menunggu')
            ->latest()
            ->get();
    }

    // Ambil queue lembur untuk approver yang sedang login
    public function getQueueLembur(int $userId)
    {
        return LemburApproval::with(['pengajuanLembur.pegawai'])
            ->where('approver_id', $userId)
            ->where('status', 'menunggu')
            ->latest()
            ->get();
    }

    // Proses approval cuti
    public function prosesCuti(int $approvalId, string $status, string $catatan = null): void
    {
        DB::transaction(function () use ($approvalId, $status, $catatan) {
            $approval = CutiApproval::with('pengajuanCuti.pegawai')->findOrFail($approvalId);

            // Update approval record
            $approval->update([
                'status'        => $status,
                'catatan'       => $catatan,
                'diproses_pada' => now(),
            ]);

            $pengajuan = $approval->pengajuanCuti;

            if ($status === 'ditolak') {
                // Langsung tolak seluruh pengajuan
                $pengajuan->update(['status' => 'ditolak']);

                // Notifikasi ke pemohon
                $this->notifikasiService->kirim(
                    $pengajuan->pegawai->user_id,
                    'Pengajuan Cuti Ditolak',
                    "Pengajuan cuti Anda pada {$pengajuan->tanggal_mulai->format('d/m/Y')} ditolak. Catatan: {$catatan}",
                    'error',
                );

            } elseif ($status === 'disetujui') {
                // Cek apakah semua approval sudah disetujui
                $totalApproval    = $pengajuan->approvals()->count();
                $approvedCount    = $pengajuan->approvals()->where('status', 'disetujui')->count();

                if ($approvedCount >= $totalApproval) {
                    // Semua sudah approve → update status pengajuan
                    $pengajuan->update(['status' => 'disetujui']);

                    // Kurangi sisa cuti pegawai
                    $pengajuan->pegawai->decrement('sisa_cuti', $pengajuan->jumlah_hari);

                    // Notifikasi ke pemohon
                    $this->notifikasiService->kirim(
                        $pengajuan->pegawai->user_id,
                        'Pengajuan Cuti Disetujui',
                        "Pengajuan cuti Anda pada {$pengajuan->tanggal_mulai->format('d/m/Y')} telah disetujui.",
                        'success',
                    );

                    // Cross-notifikasi ke semua pimpinan unit
                    $this->notifikasiService->kirimCrossNotifikasi($pengajuan);
                }
            }
        });
    }

    // Proses approval lembur
    public function prosesLembur(int $approvalId, string $status, string $catatan = null): void
    {
        DB::transaction(function () use ($approvalId, $status, $catatan) {
            $approval = LemburApproval::with('pengajuanLembur.pegawai')->findOrFail($approvalId);

            $approval->update([
                'status'        => $status,
                'catatan'       => $catatan,
                'diproses_pada' => now(),
            ]);

            $pengajuan = $approval->pengajuanLembur;
            $pengajuan->update(['status' => $status]);

            // Notifikasi ke pemohon
            $pesan = $status === 'disetujui'
                ? "Pengajuan lembur Anda pada {$pengajuan->tanggal_lembur} telah disetujui."
                : "Pengajuan lembur Anda pada {$pengajuan->tanggal_lembur} ditolak. Catatan: {$catatan}";

            $this->notifikasiService->kirim(
                $pengajuan->pegawai->user_id,
                $status === 'disetujui' ? 'Lembur Disetujui' : 'Lembur Ditolak',
                $pesan,
                $status === 'disetujui' ? 'success' : 'error',
            );
        });
    }

    public function getRiwayatCuti(int $userId)
    {
        return CutiApproval::with(['pengajuanCuti.pegawai', 'jabatan'])
            ->where('approver_id', $userId)
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->latest()
            ->get();
    }
}