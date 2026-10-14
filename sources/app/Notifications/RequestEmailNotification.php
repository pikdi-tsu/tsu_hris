<?php

namespace App\Notifications;

use App\Models\RequestEmail;

class RequestEmailNotification extends TsuRealtimeNotification
{
    public $requestEmail;
    public $step;
    public $targetRole;

    /**
     * Create a new notification instance.
     *
     * @param RequestEmail $requestEmail
     * @param string $step 'diajukan' | 'divalidasi_sdm' | 'selesai' | 'ditolak_sdm' | 'ditolak_pikdi'
     * @param string $targetRole 'sdm' | 'pikdi' | 'pemohon'
     * @param string $message
     */
    public function __construct(RequestEmail $requestEmail, string $step, string $targetRole, string $message)
    {
        $this->requestEmail = $requestEmail;
        $this->step = $step;
        $this->targetRole = $targetRole;

        $title = match ($step) {
            'diajukan'        => 'Permintaan Email Baru Masuk',
            'divalidasi_sdm'  => 'Email Siap Diterbitkan (Validasi SDM)',
            'selesai'         => 'Akun Email Resmi Telah Aktif (@tsu.ac.id)',
            'ditolak_sdm'     => 'Permintaan Email Ditolak oleh SDM',
            'ditolak_pikdi'   => 'Kendala Penerbitan Email (PIKDI)',
            default           => 'Update Permintaan Akun Email',
        };

        $icon = match ($step) {
            'diajukan'        => 'fas fa-envelope-open-text text-warning',
            'divalidasi_sdm'  => 'fas fa-user-check text-info',
            'selesai'         => 'fas fa-check-double text-success',
            'ditolak_sdm',
            'ditolak_pikdi'   => 'fas fa-times-circle text-danger',
            default           => 'fas fa-bell text-secondary',
        };

        $actionUrl = route('admin.request-email.index', ['search' => $requestEmail->nomor_tiket]);
        $actionText = match ($targetRole) {
            'sdm'     => 'Validasi Alamat Email',
            'pikdi'   => 'Terbitkan Akun di PIKDI',
            'pemohon' => 'Lihat Detail Akun',
            default   => 'Lihat Permohonan',
        };

        parent::__construct(
            $message,
            'request-email', // module name for dynamic badge injection
            $actionUrl,
            $actionText,
            $title,
            $icon,
            false, // not silent
            [
                'request_email_id' => $requestEmail->id,
                'nomor_tiket'      => $requestEmail->nomor_tiket,
                'nama_pemohon'     => $requestEmail->nama_pemohon,
                'unit_nama'        => $requestEmail->unit_nama,
                'status'           => $requestEmail->status,
                'email_usulan'     => $requestEmail->email_usulan,
                'email_rekomendasi'=> $requestEmail->email_rekomendasi_sdm,
                'email_final'      => $requestEmail->email_final_dibuat,
                'step'             => $step,
                'target_role'      => $targetRole,
            ]
        );
    }
}
