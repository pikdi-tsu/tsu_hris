<?php

namespace App\Notifications;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ExportSelesaiNotification extends TsuRealtimeNotification implements ShouldBroadcastNow
{
    /**
     * Create a new notification instance.
     */
    public function __construct($message, $downloadUrl)
    {
        parent::__construct(
            $message,
            'export', // module
            $downloadUrl, // action_url
            'Download', // action_text
            'Export Selesai', // title
            'fas fa-file-excel text-success', // icon
            false, // is_silent
            ['download_url' => $downloadUrl, 'statusatasan' => 'export-ready'] // options
        );
    }
}
