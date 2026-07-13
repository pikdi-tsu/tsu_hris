<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ExportSelesaiNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $message;
    public $downloadUrl;

    public function __construct($message, $downloadUrl)
    {
        $this->message = $message;
        $this->downloadUrl = $downloadUrl;
    }

    public function via($notifiable)
    {
        return ['broadcast', 'database']; 
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'download_url' => $this->downloadUrl,
            'statusatasan' => 'export-ready'
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => $this->message,
            'download_url' => $this->downloadUrl,
            'statusatasan' => 'export-ready' // using this key as indicator for the front-end to know it's a toast with action
        ]);
    }
}
