<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PengajuanDiprosesNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;
    public $jenis;
    public $action_url;
    public $action_text;
    public $icon_class;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $jenis, $action_url, $action_text = 'Cek Riwayat', $icon_class = 'fa-info-circle text-info')
    {
        $this->message = $message;
        $this->jenis = $jenis;
        $this->action_url = $action_url;
        $this->action_text = $action_text;
        $this->icon_class = $icon_class;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'jenis' => $this->jenis,
            'action_url' => $this->action_url,
            'action_text' => $this->action_text,
            'icon_class' => $this->icon_class
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => $this->message,
            'jenis' => $this->jenis,
            'action_url' => $this->action_url,
            'action_text' => $this->action_text,
            'icon_class' => $this->icon_class
        ]);
    }
}
