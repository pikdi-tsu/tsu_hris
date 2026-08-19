<?php

namespace App\Notifications;

class CutiDiajukanNotification extends TsuRealtimeNotification
{
    public $cuti;
    public $role;

    /**
     * Create a new notification instance.
     */
    public function __construct($cuti, $message, $role = 'atasan')
    {
        $this->cuti = $cuti;
        $this->role = $role;

        // Condition for silent notification and action text
        $is_silent = false;
        $action_text = 'Proses Cuti';
        if ($role === 'hrd' && $cuti->statusatasan === 'waiting') {
            $is_silent = true;
            $action_text = 'Lihat Cuti';
        }

        parent::__construct(
            $message,
            'indexapprovalcuti', // module
            route('users.indexapprovalcuti'), // action_url
            $action_text, // action_text
            'Pengajuan Cuti', // title
            'fas fa-umbrella-beach text-warning', // icon
            $is_silent, // is_silent
            ['id_cuti' => $cuti->id, 'role' => $role, 'statusatasan' => $cuti->statusatasan, 'jenis' => 'cuti'] // options
        );
    }
}
