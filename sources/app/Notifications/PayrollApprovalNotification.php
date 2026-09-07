<?php

namespace App\Notifications;

class PayrollApprovalNotification extends TsuRealtimeNotification
{
    public $period;
    public $step;

    /**
     * Create a new notification instance for Payroll approval events.
     *
     * @param mixed $period
     * @param string $message
     * @param string $step (validator_1, validator_2, approval, locked, revision, unlocked)
     * @param string $actionText
     */
    public function __construct($period, $message, $step = 'validator_1', $actionText = 'Kroscek Payroll')
    {
        $this->period = $period;
        $this->step = $step;

        $isHonor = ($period->tipe === 'honorarium');
        $typeName = $isHonor ? 'Honorarium Dosen' : 'Payroll';
        $targetRoute = $isHonor ? route('admin.honorarium.show', $period->id) : route('admin.payroll.show', $period->id);

        $icon = $isHonor ? 'fas fa-graduation-cap text-primary' : 'fas fa-file-invoice-dollar text-primary';
        $title = "Pemberitahuan {$typeName}";

        if ($step === 'validator_1' || $step === 'submitted') {
            $icon = 'fas fa-user-check text-info';
            $title = "Pengajuan Validasi {$typeName} (Tahap 1)";
        } elseif ($step === 'validator_2' || $step === 'pending_val_2') {
            $icon = 'fas fa-user-check text-primary';
            $title = "Pengajuan Verifikasi {$typeName} (Tahap 2)";
        } elseif ($step === 'approval' || $step === 'pending_approval') {
            $icon = 'fas fa-crown text-warning';
            $title = "Persetujuan Final {$typeName}";
        } elseif ($step === 'locked') {
            $icon = 'fas fa-lock text-success';
            $title = "{$typeName} Telah Disetujui & Terkunci";
        } elseif ($step === 'revision' || $step === 'revision_requested') {
            $icon = 'fas fa-undo-alt text-danger';
            $title = "Catatan Revisi {$typeName}";
        } elseif ($step === 'unlocked') {
            $icon = 'fas fa-unlock text-warning';
            $title = "Kunci {$typeName} Dibuka Kembali";
        }

        parent::__construct(
            $message,
            $isHonor ? 'honorarium' : 'payroll',
            $targetRoute,
            $actionText,
            $title,
            $icon,
            false,
            [
                'payroll_period_id' => $period->id,
                'nama_periode'      => $period->nama_periode,
                'tipe'              => $period->tipe,
                'step'              => $step,
            ]
        );
    }
}
