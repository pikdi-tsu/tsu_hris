<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ExportGagalNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $message;
    public $errorDetail;

    public function __construct($message, $errorDetail = null)
    {
        $this->message = $message;
        $this->errorDetail = $errorDetail;
    }

    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'error_detail' => $this->errorDetail,
            'statusatasan' => 'export-failed'
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => $this->message,
            'error_detail' => $this->errorDetail,
            'statusatasan' => 'export-failed' // Indicator for frontend to show error toast
        ]);
    }
}
