<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;

class IzinDiajukanNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public $izin;
    public $message;
    public $role;

    /**
     * Create a new notification instance.
     */
    public function __construct($izin, $message, $role = 'atasan')
    {
        $this->izin = $izin;
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
            'id_izin' => $this->izin->id,
            'message' => $this->message,
            'jenis' => 'izin',
            'role' => $this->role,
            'statusatasan' => $this->izin->statusatasan,
            'action_text' => 'Proses Izin',
            'action_url' => route('users.indexapprovalizin')
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id_izin' => $this->izin->id,
            'message' => $this->message,
            'jenis' => 'izin',
            'role' => $this->role,
            'statusatasan' => $this->izin->statusatasan
        ]);
    }
}
