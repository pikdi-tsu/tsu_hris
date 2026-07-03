<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CutiDiajukanNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public $cuti;
    public $message;
    public $role;

    /**
     * Create a new notification instance.
     */
    public function __construct($cuti, $message, $role = 'atasan')
    {
        $this->cuti = $cuti;
        $this->message = $message;
        $this->role = $role;
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
            'id_cuti' => $this->cuti->id,
            'message' => $this->message,
            'jenis' => 'cuti',
            'role' => $this->role,
            'statusatasan' => $this->cuti->statusatasan,
            'action_text' => 'Proses Cuti',
            'action_url' => route('users.indexapprovalcuti')
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id_cuti' => $this->cuti->id,
            'message' => $this->message,
            'jenis' => 'cuti',
            'role' => $this->role,
            'statusatasan' => $this->cuti->statusatasan
        ]);
    }
}
