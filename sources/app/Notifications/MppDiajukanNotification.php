<?php

namespace App\Notifications;

class MppDiajukanNotification extends TsuRealtimeNotification
{
    public $mpp;
    public $role;

    /**
     * Create a new notification instance.
     */
    public function __construct($mpp, $message, $role = 'hrd')
    {
        $this->mpp = $mpp;
        $this->role = $role;

        $is_silent = false;
        $action_text = 'Proses MPP';

        parent::__construct(
            $message,
            'mpp', // module
            route('admin.mpp.index'), // action_url
            $action_text, // action_text
            'Pengajuan Manpower Planning', // title
            'fas fa-users-cog text-info', // icon
            $is_silent, // is_silent
            ['id_mpp' => $mpp->id, 'role' => $role, 'status' => $mpp->status, 'jenis' => 'mpp'] // options
        );
    }
}
