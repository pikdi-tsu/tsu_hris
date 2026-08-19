<?php

namespace App\Notifications;

class LemburDiajukanNotification extends TsuRealtimeNotification
{
    public $lembur;
    public $role;

    /**
     * Create a new notification instance.
     */
    public function __construct($lembur, $message, $role = 'atasan')
    {
        $this->lembur = $lembur;
        $this->role = $role;

        // Condition for silent notification (dari legacy footer) and action text
        $is_silent = false;
        $action_text = 'Proses Lembur';
        if ($role === 'hrd' && $lembur->statusatasan === 'waiting') {
            $is_silent = true;
            $action_text = 'Lihat Lembur';
        }

        parent::__construct(
            $message,
            'lembur', // module
            route('users.lembur.index') . '#content-persetujuan-bawahan', // action_url
            $action_text, // action_text
            'Pengajuan Lembur', // title
            'fas fa-clock text-info', // icon
            $is_silent, // is_silent
            ['id_lembur' => $lembur->id, 'role' => $role, 'statusatasan' => $lembur->statusatasan, 'jenis' => 'lembur'] // options (jenis kept for backward compatibility if needed)
        );
    }
}
