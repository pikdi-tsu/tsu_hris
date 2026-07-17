<?php

namespace App\Notifications;

class IzinDiajukanNotification extends TsuRealtimeNotification
{
    public $izin;
    public $role;

    /**
     * Create a new notification instance.
     */
    public function __construct($izin, $message, $role = 'atasan')
    {
        $this->izin = $izin;
        $this->role = $role;

        // Condition for silent notification
        $is_silent = false;
        if ($role === 'hrd' && $izin->statusatasan === 'waiting') {
            $is_silent = true;
        }

        parent::__construct(
            $message,
            'indexapprovalizin', // module
            route('users.indexapprovalizin'), // action_url
            'Proses Izin', // action_text
            'Pengajuan Izin', // title
            'fas fa-envelope-open-text text-primary', // icon
            $is_silent, // is_silent
            ['id_izin' => $izin->id, 'role' => $role, 'statusatasan' => $izin->statusatasan, 'jenis' => 'izin'] // options
        );
    }
}
