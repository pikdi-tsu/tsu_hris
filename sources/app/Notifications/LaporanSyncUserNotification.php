<?php

namespace App\Notifications;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class LaporanSyncUserNotification extends TsuRealtimeNotification implements ShouldBroadcastNow
{
    /**
     * Create a new notification instance.
     */
    public function __construct($message, $errorDetail = null)
    {
        $options = [];
        if ($errorDetail) {
            $options['error_detail'] = $errorDetail;
        }

        parent::__construct(
            $message,
            'users', // module
            route('users.notifications.index'), // action_url
            'Buka Kotak Masuk', // action_text
            'Laporan Sync User', // title
            'fas fa-sync text-info', // icon
            true, // is_silent
            $options // options
        );
    }
}
