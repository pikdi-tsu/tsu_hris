<?php

namespace App\Notifications;

class ThrApprovalNotification extends TsuRealtimeNotification
{
    public $period;
    public $step;

    /**
     * Create a new notification instance for THR approval events.
     *
     * @param mixed $period
     * @param string $message
     * @param string $step (validator_1, validator_2, approval, locked, revision, unlocked)
     * @param string $actionText
     */
    public function __construct($period, $message, $step = 'validator_1', $actionText = 'Kroscek THR')
    {
        $this->period = $period;
        $this->step = $step;

        $targetRoute = route('admin.payroll.thr.show', $period->id);
        $icon = 'fas fa-gifts text-primary';
        $title = "Pemberitahuan Tunjangan Hari Raya (THR)";

        if ($step === 'validator_1' || $step === 'submitted') {
            $icon = 'fas fa-user-check text-info';
            $title = "Pengajuan Validasi THR (Tahap 1)";
        } elseif ($step === 'validator_2' || $step === 'pending_val_2') {
            $icon = 'fas fa-user-check text-primary';
            $title = "Pengajuan Verifikasi THR (Tahap 2)";
        } elseif ($step === 'approval' || $step === 'pending_approval') {
            $icon = 'fas fa-crown text-warning';
            $title = "Persetujuan Final THR";
        } elseif ($step === 'locked') {
            $icon = 'fas fa-lock text-success';
            $title = "THR Telah Disetujui & Terkunci";
        } elseif ($step === 'revision' || $step === 'revision_requested') {
            $icon = 'fas fa-undo-alt text-danger';
            $title = "Catatan Revisi THR";
        } elseif ($step === 'unlocked') {
            $icon = 'fas fa-unlock text-warning';
            $title = "Kunci Periode THR Dibuka Kembali";
        }

        parent::__construct(
            $message,
            'thr',
            $targetRoute,
            $actionText,
            $title,
            $icon,
            false,
            [
                'thr_period_id' => $period->id,
                'nama_periode'  => $period->nama_periode,
                'step'          => $step,
            ]
        );
    }
}
