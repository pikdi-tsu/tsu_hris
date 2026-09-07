<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\MasterShift;
use App\Models\MasterShiftDetail;

class MasterShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            // 1. Dosen & Struktural (Berdasarkan Durasi Kerja)
            [
                'nama_shift' => 'Dosen - Tenaga Pengajar / Asisten Ahli',
                'kode_shift' => 'DSN-AA',
                'tipe_shift' => 'durasi',
                'target_durasi_menit' => 420, // 7 jam kerja efektif
                'keterangan' => 'Tenaga Pengajar/Asisten Ahli: 7 Jam kerja + 1 Jam istirahat',
                'details' => []
            ],
            [
                'nama_shift' => 'Dosen - Lektor 200',
                'kode_shift' => 'DSN-L200',
                'tipe_shift' => 'durasi',
                'target_durasi_menit' => 360, // 6 jam kerja efektif
                'keterangan' => 'Lektor 200: 6 Jam kerja + 1 Jam istirahat',
                'details' => []
            ],
            [
                'nama_shift' => 'Dosen - Lektor 300',
                'kode_shift' => 'DSN-L300',
                'tipe_shift' => 'durasi',
                'target_durasi_menit' => 300, // 5 jam kerja efektif
                'keterangan' => 'Lektor 300: 5 Jam kerja + 1 Jam istirahat',
                'details' => []
            ],
            [
                'nama_shift' => 'Dosen - Lektor Kepala',
                'kode_shift' => 'DSN-LK',
                'tipe_shift' => 'durasi',
                'target_durasi_menit' => 240, // 4 jam kerja efektif
                'keterangan' => 'Lektor Kepala: 4 Jam kerja + 1 Jam istirahat',
                'details' => []
            ],
            [
                'nama_shift' => 'Jabatan Struktural',
                'kode_shift' => 'STRUKTURAL',
                'tipe_shift' => 'durasi',
                'target_durasi_menit' => 420, // 7 jam kerja efektif
                'keterangan' => 'Jabatan Struktural: Tetap 7 Jam kerja + 1 Jam istirahat',
                'details' => []
            ],

            // 2. Tendik (Umum)
            [
                'nama_shift' => 'Tendik - Masuk Kerja Pagi',
                'kode_shift' => 'TNDK-PAGI',
                'tipe_shift' => 'jadwal',
                'target_durasi_menit' => 450,
                'keterangan' => 'Tendik Pagi (Senin-Kamis 08.00-16.30, Jumat 08.00-16.30, Sabtu 08.00-12.00)',
                'details' => [
                    // Senin - Kamis
                    ['hari' => 1, 'jam_masuk' => '08:00:00', 'jam_pulang' => '16:30:00', 'jam_istirahat_mulai' => '12:00:00', 'jam_istirahat_selesai' => '13:00:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 2, 'jam_masuk' => '08:00:00', 'jam_pulang' => '16:30:00', 'jam_istirahat_mulai' => '12:00:00', 'jam_istirahat_selesai' => '13:00:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 3, 'jam_masuk' => '08:00:00', 'jam_pulang' => '16:30:00', 'jam_istirahat_mulai' => '12:00:00', 'jam_istirahat_selesai' => '13:00:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 4, 'jam_masuk' => '08:00:00', 'jam_pulang' => '16:30:00', 'jam_istirahat_mulai' => '12:00:00', 'jam_istirahat_selesai' => '13:00:00', 'is_cross_day' => false, 'is_libur' => false],
                    // Jumat
                    ['hari' => 5, 'jam_masuk' => '08:00:00', 'jam_pulang' => '16:30:00', 'jam_istirahat_mulai' => '11:30:00', 'jam_istirahat_selesai' => '13:00:00', 'is_cross_day' => false, 'is_libur' => false],
                    // Sabtu
                    ['hari' => 6, 'jam_masuk' => '08:00:00', 'jam_pulang' => '12:00:00', 'jam_istirahat_mulai' => null, 'jam_istirahat_selesai' => null, 'is_cross_day' => false, 'is_libur' => false],
                    // Minggu
                    ['hari' => 7, 'jam_masuk' => null, 'jam_pulang' => null, 'jam_istirahat_mulai' => null, 'jam_istirahat_selesai' => null, 'is_cross_day' => false, 'is_libur' => true],
                ]
            ],
            [
                'nama_shift' => 'Tendik - Masuk Kerja Siang',
                'kode_shift' => 'TNDK-SIANG',
                'tipe_shift' => 'jadwal',
                'target_durasi_menit' => 420,
                'keterangan' => 'Tendik Siang (Senin-Jumat 12.30-20.30, Sabtu 08.00-12.00)',
                'details' => [
                    ['hari' => 1, 'jam_masuk' => '12:30:00', 'jam_pulang' => '20:30:00', 'jam_istirahat_mulai' => '17:30:00', 'jam_istirahat_selesai' => '18:30:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 2, 'jam_masuk' => '12:30:00', 'jam_pulang' => '20:30:00', 'jam_istirahat_mulai' => '17:30:00', 'jam_istirahat_selesai' => '18:30:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 3, 'jam_masuk' => '12:30:00', 'jam_pulang' => '20:30:00', 'jam_istirahat_mulai' => '17:30:00', 'jam_istirahat_selesai' => '18:30:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 4, 'jam_masuk' => '12:30:00', 'jam_pulang' => '20:30:00', 'jam_istirahat_mulai' => '17:30:00', 'jam_istirahat_selesai' => '18:30:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 5, 'jam_masuk' => '12:30:00', 'jam_pulang' => '20:30:00', 'jam_istirahat_mulai' => '17:30:00', 'jam_istirahat_selesai' => '18:30:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 6, 'jam_masuk' => '08:00:00', 'jam_pulang' => '12:00:00', 'jam_istirahat_mulai' => null, 'jam_istirahat_selesai' => null, 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 7, 'jam_masuk' => null, 'jam_pulang' => null, 'jam_istirahat_mulai' => null, 'jam_istirahat_selesai' => null, 'is_cross_day' => false, 'is_libur' => true],
                ]
            ],

            // 3. Sarana Prasarana / Kerumahtanggaan
            [
                'nama_shift' => 'Sarpras - Masuk Kerja Pagi',
                'kode_shift' => 'SARPRAS-PAGI',
                'tipe_shift' => 'jadwal',
                'target_durasi_menit' => 450,
                'keterangan' => 'Sarpras Pagi (Senin-Kamis 06.00-14.30, Jumat 06.00-14.30, Sabtu 08.00-12.00)',
                'details' => [
                    ['hari' => 1, 'jam_masuk' => '06:00:00', 'jam_pulang' => '14:30:00', 'jam_istirahat_mulai' => '12:00:00', 'jam_istirahat_selesai' => '13:00:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 2, 'jam_masuk' => '06:00:00', 'jam_pulang' => '14:30:00', 'jam_istirahat_mulai' => '12:00:00', 'jam_istirahat_selesai' => '13:00:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 3, 'jam_masuk' => '06:00:00', 'jam_pulang' => '14:30:00', 'jam_istirahat_mulai' => '12:00:00', 'jam_istirahat_selesai' => '13:00:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 4, 'jam_masuk' => '06:00:00', 'jam_pulang' => '14:30:00', 'jam_istirahat_mulai' => '12:00:00', 'jam_istirahat_selesai' => '13:00:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 5, 'jam_masuk' => '06:00:00', 'jam_pulang' => '14:30:00', 'jam_istirahat_mulai' => '11:30:00', 'jam_istirahat_selesai' => '13:00:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 6, 'jam_masuk' => '08:00:00', 'jam_pulang' => '12:00:00', 'jam_istirahat_mulai' => null, 'jam_istirahat_selesai' => null, 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 7, 'jam_masuk' => null, 'jam_pulang' => null, 'jam_istirahat_mulai' => null, 'jam_istirahat_selesai' => null, 'is_cross_day' => false, 'is_libur' => true],
                ]
            ],
            [
                'nama_shift' => 'Sarpras - Masuk Kerja Siang',
                'kode_shift' => 'SARPRAS-SIANG',
                'tipe_shift' => 'jadwal',
                'target_durasi_menit' => 450,
                'keterangan' => 'Sarpras Siang (Senin-Jumat 12.30-21.00, Sabtu 08.00-12.00)',
                'details' => [
                    ['hari' => 1, 'jam_masuk' => '12:30:00', 'jam_pulang' => '21:00:00', 'jam_istirahat_mulai' => '17:30:00', 'jam_istirahat_selesai' => '18:30:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 2, 'jam_masuk' => '12:30:00', 'jam_pulang' => '21:00:00', 'jam_istirahat_mulai' => '17:30:00', 'jam_istirahat_selesai' => '18:30:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 3, 'jam_masuk' => '12:30:00', 'jam_pulang' => '21:00:00', 'jam_istirahat_mulai' => '17:30:00', 'jam_istirahat_selesai' => '18:30:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 4, 'jam_masuk' => '12:30:00', 'jam_pulang' => '21:00:00', 'jam_istirahat_mulai' => '17:30:00', 'jam_istirahat_selesai' => '18:30:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 5, 'jam_masuk' => '12:30:00', 'jam_pulang' => '21:00:00', 'jam_istirahat_mulai' => '17:30:00', 'jam_istirahat_selesai' => '18:30:00', 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 6, 'jam_masuk' => '08:00:00', 'jam_pulang' => '12:00:00', 'jam_istirahat_mulai' => null, 'jam_istirahat_selesai' => null, 'is_cross_day' => false, 'is_libur' => false],
                    ['hari' => 7, 'jam_masuk' => null, 'jam_pulang' => null, 'jam_istirahat_mulai' => null, 'jam_istirahat_selesai' => null, 'is_cross_day' => false, 'is_libur' => true],
                ]
            ],

            // 4. Keamanan / Satuan Pengamanan (Satpam)
            [
                'nama_shift' => 'Keamanan - Shift 1',
                'kode_shift' => 'SEC-S1',
                'tipe_shift' => 'jadwal',
                'target_durasi_menit' => 480, // 8 jam
                'keterangan' => 'Keamanan Shift 1 (07.00 - 15.00 WIB)',
                'details' => array_map(function ($hari) {
                    return ['hari' => $hari, 'jam_masuk' => '07:00:00', 'jam_pulang' => '15:00:00', 'jam_istirahat_mulai' => null, 'jam_istirahat_selesai' => null, 'is_cross_day' => false, 'is_libur' => false];
                }, range(1, 7))
            ],
            [
                'nama_shift' => 'Keamanan - Shift 2',
                'kode_shift' => 'SEC-S2',
                'tipe_shift' => 'jadwal',
                'target_durasi_menit' => 420, // 7 jam
                'keterangan' => 'Keamanan Shift 2 (15.00 - 22.00 WIB)',
                'details' => array_map(function ($hari) {
                    return ['hari' => $hari, 'jam_masuk' => '15:00:00', 'jam_pulang' => '22:00:00', 'jam_istirahat_mulai' => null, 'jam_istirahat_selesai' => null, 'is_cross_day' => false, 'is_libur' => false];
                }, range(1, 7))
            ],
            [
                'nama_shift' => 'Keamanan - Shift 3',
                'kode_shift' => 'SEC-S3',
                'tipe_shift' => 'jadwal',
                'target_durasi_menit' => 540, // 9 jam
                'keterangan' => 'Keamanan Shift 3 (22.00 - 07.00 WIB Lintas Hari)',
                'details' => array_map(function ($hari) {
                    return ['hari' => $hari, 'jam_masuk' => '22:00:00', 'jam_pulang' => '07:00:00', 'jam_istirahat_mulai' => null, 'jam_istirahat_selesai' => null, 'is_cross_day' => true, 'is_libur' => false];
                }, range(1, 7))
            ],
        ];

        foreach ($shifts as $shiftData) {
            $shift = MasterShift::updateOrCreate(
                ['kode_shift' => $shiftData['kode_shift']],
                [
                    'id' => Str::uuid()->toString(),
                    'nama_shift' => $shiftData['nama_shift'],
                    'tipe_shift' => $shiftData['tipe_shift'],
                    'target_durasi_menit' => $shiftData['target_durasi_menit'],
                    'keterangan' => $shiftData['keterangan'],
                    'is_active' => 'Y'
                ]
            );

            // Details
            if (!empty($shiftData['details'])) {
                MasterShiftDetail::where('master_shift_id', $shift->id)->delete();
                foreach ($shiftData['details'] as $detail) {
                    MasterShiftDetail::create(array_merge($detail, [
                        'id' => Str::uuid()->toString(),
                        'master_shift_id' => $shift->id
                    ]));
                }
            }
        }
    }
}
