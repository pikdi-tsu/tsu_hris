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

        // Condition for silent notification
        $is_silent = false;
        if ($role === 'hrd' && $cuti->statusatasan === 'waiting') {
            $is_silent = true;
        }

        parent::__construct(
            $message,
            'indexapprovalcuti', // module
            route('users.indexapprovalcuti'), // action_url
            'Proses Cuti', // action_text
            'Pengajuan Cuti', // title
            'fas fa-umbrella-beach text-warning', // icon
            $is_silent, // is_silent
            ['id_cuti' => $cuti->id, 'role' => $role, 'statusatasan' => $cuti->statusatasan, 'jenis' => 'cuti'] // options
        );
    }
}
