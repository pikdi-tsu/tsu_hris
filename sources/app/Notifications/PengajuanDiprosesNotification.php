<?php

namespace App\Notifications;

class PengajuanDiprosesNotification extends TsuRealtimeNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct($message, $jenis, $action_url, $action_text = 'Cek Riwayat', $icon_class = 'fa-info-circle text-info')
    {
        // Pastikan icon formatnya ada fas/far nya sesuai TsuRealtimeNotification
        $icon = strpos($icon_class, 'fas ') !== false || strpos($icon_class, 'far ') !== false ? $icon_class : 'fas ' . $icon_class;

        parent::__construct(
            $message,
            $jenis, // module (digunakan fallback jika ada sidebar)
            $action_url,
            $action_text,
            'Status Pengajuan', // title
            $icon, // icon
            false, // is_silent
            ['jenis' => $jenis, 'icon_class' => $icon_class] // options untuk backward compatibility
        );
    }
}
