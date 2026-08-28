<?php

namespace App\Notifications;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ExportGagalNotification extends TsuRealtimeNotification implements ShouldBroadcastNow
{
    /**
     * Create a new notification instance.
     */
    public function __construct($message, $errorDetail = null)
    {
        parent::__construct(
            $message,
            'export', // module
            '#', // action_url
            'Gagal', // action_text
            'Export Gagal', // title
            'fas fa-times-circle text-danger', // icon
            false, // is_silent
            ['error_detail' => $errorDetail, 'statusatasan' => 'export-failed'] // options
        );
    }
}
