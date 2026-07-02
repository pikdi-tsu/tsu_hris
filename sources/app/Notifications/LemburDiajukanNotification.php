<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;

class LemburDiajukanNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public $lembur;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($lembur, $message)
    {
        $this->lembur = $lembur;
        $this->message = $message;
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
            'id_lembur' => $this->lembur->id,
            'message' => $this->message,
            'jenis' => 'lembur'
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id_lembur' => $this->lembur->id,
            'message' => $this->message,
            'jenis' => 'lembur'
        ]);
    }
}
