<?php

namespace App\Notifications;

class PerubahanDataDiajukanNotification extends TsuRealtimeNotification
{
    public $perubahan;
    public $karyawan;

    /**
     * Create a new notification instance.
     */
    public function __construct($perubahan, $karyawan, $message)
    {
        $this->perubahan = $perubahan;
        $this->karyawan = $karyawan;

        $is_silent = false;
        $action_text = 'Verifikasi Perubahan Data';

        parent::__construct(
            $message,
            'perubahan_data', // module
            route('admin.approval-perubahan-data.index'), // action_url
            $action_text,
            'Pengajuan Perubahan Data Pegawai', // title
            'fas fa-user-edit text-warning', // icon
            $is_silent,
            [
                'id_perubahan' => $perubahan->id,
                'data_dosen_tendik_id' => $karyawan->id,
                'nama' => $karyawan->nama,
                'status' => $perubahan->status
            ]
        );
    }
}
