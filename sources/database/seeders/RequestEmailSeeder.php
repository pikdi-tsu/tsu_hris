<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequestEmail;
use App\Models\DataDosenTendik;
use App\Models\MasterUnit;
use App\Models\User;
use Carbon\Carbon;

class RequestEmailSeeder extends Seeder
{
    public function run(): void
    {
        $pegawais = DataDosenTendik::with('unit')->take(3)->get();
        $sdmUser = User::where('email', 'sdm@tsu.ac.id')->first() ?? User::first();
        $pikdiUser = User::where('name', 'like', '%pikdi%')->orWhere('email', 'like', '%pikdi%')->first() ?? $sdmUser;

        // 1. Request Baru (Menunggu Validasi SDM)
        if ($pegawais->isNotEmpty()) {
            $p1 = $pegawais[0];
            RequestEmail::updateOrCreate(
                ['nomor_tiket' => 'REQ-MAIL-2026-001'],
                [
                    'pegawai_id'            => $p1->id,
                    'nama_pemohon'          => $p1->nama,
                    'nik_nip'               => $p1->nip ?? $p1->nik,
                    'unit_id'               => $p1->master_unit_id,
                    'unit_nama'             => $p1->unit->nama_unit ?? 'Fakultas Teknik',
                    'jabatan'               => $p1->tipe_karyawan,
                    'tipe_pemohon'          => $p1->tipe_karyawan === 'Dosen' ? 'Dosen' : 'Tendik',
                    'keperluan'             => 'Kebutuhan korespondensi akademik resmi dan akses jurnal ilmiah universitas.',
                    'email_usulan'          => strtolower(explode(' ', trim($p1->nama))[0]) . '@tsu.ac.id',
                    'email_rekomendasi_sdm' => null,
                    'email_final_dibuat'    => null,
                    'kontak_hp'             => $p1->no_hp ?? '081234567890',
                    'email_alternatif'      => $p1->email_pribadi ?? 'pegawai1@gmail.com',
                    'status'                => 'Diajukan',
                    'catatan_pemohon'       => 'Mohon diproses untuk keperluan akreditasi prodi.',
                    'riwayat_status'        => [
                        ['tanggal' => Carbon::now()->subDays(2)->format('d M Y H:i'), 'status' => 'Permintaan Email Masuk (Diajukan oleh Pemohon)']
                    ],
                    'created_at'            => Carbon::now()->subDays(2),
                ]
            );
        }

        // 2. Request Divalidasi SDM (SDM Mengedit Rekomendasi Email -> Menunggu Tindak Lanjut PIKDI)
        if ($pegawais->count() > 1) {
            $p2 = $pegawais[1];
            $cleanName = strtolower(preg_replace('/[^a-zA-Z]/', '', explode(' ', trim($p2->nama))[0]));
            RequestEmail::updateOrCreate(
                ['nomor_tiket' => 'REQ-MAIL-2026-002'],
                [
                    'pegawai_id'            => $p2->id,
                    'nama_pemohon'          => $p2->nama,
                    'nik_nip'               => $p2->nip ?? $p2->nik,
                    'unit_id'               => $p2->master_unit_id,
                    'unit_nama'             => $p2->unit->nama_unit ?? 'Biro Administrasi Umum',
                    'jabatan'               => $p2->tipe_karyawan,
                    'tipe_pemohon'          => $p2->tipe_karyawan === 'Dosen' ? 'Dosen' : 'Tendik',
                    'keperluan'             => 'Komunikasi dinas administrasi kampus dan integrasi surat menyurat elektronik.',
                    'email_usulan'          => $cleanName . '2026@tsu.ac.id',
                    'email_rekomendasi_sdm' => $cleanName . '.sanjaya@tsu.ac.id', // SDM mengedit sesuai standar penamaan resmi
                    'email_final_dibuat'    => null,
                    'kontak_hp'             => $p2->no_hp ?? '081298765432',
                    'email_alternatif'      => $p2->email_pribadi ?? 'pegawai2@gmail.com',
                    'status'                => 'Divalidasi SDM',
                    'catatan_pemohon'       => 'Diusulkan alamat email dinas.',
                    'catatan_sdm'           => 'Data kepegawaian valid. Alamat email disesuaikan dengan format standar universitas (nama.belakang@tsu.ac.id). Diteruskan ke PIKDI untuk aktivasi Google Workspace.',
                    'validated_by'          => $sdmUser ? $sdmUser->id : null,
                    'validated_at'          => Carbon::now()->subDay(),
                    'riwayat_status'        => [
                        ['tanggal' => Carbon::now()->subDays(3)->format('d M Y H:i'), 'status' => 'Permintaan Email Masuk'],
                        ['tanggal' => Carbon::now()->subDay()->format('d M Y H:i'), 'status' => 'Divalidasi oleh SDM (Alamat disesuaikan: ' . $cleanName . '.sanjaya@tsu.ac.id). Menunggu pembuatan oleh PIKDI']
                    ],
                    'created_at'            => Carbon::now()->subDays(3),
                ]
            );
        }

        // 3. Request Selesai (PIKDI Telah Menerbitkan Akun)
        if ($pegawais->count() > 2) {
            $p3 = $pegawais[2];
            $cleanName3 = strtolower(preg_replace('/[^a-zA-Z]/', '', explode(' ', trim($p3->nama))[0]));
            RequestEmail::updateOrCreate(
                ['nomor_tiket' => 'REQ-MAIL-2026-003'],
                [
                    'pegawai_id'            => $p3->id,
                    'nama_pemohon'          => $p3->nama,
                    'nik_nip'               => $p3->nip ?? $p3->nik,
                    'unit_id'               => $p3->master_unit_id,
                    'unit_nama'             => $p3->unit->nama_unit ?? 'Lembaga Penelitian dan Pengabdian',
                    'jabatan'               => $p3->tipe_karyawan,
                    'tipe_pemohon'          => 'Dosen',
                    'keperluan'             => 'Korespondensi submit hibah penelitian DIKTI dan reviewer jurnal internasional.',
                    'email_usulan'          => $cleanName3 . '@tsu.ac.id',
                    'email_rekomendasi_sdm' => $cleanName3 . '@tsu.ac.id',
                    'email_final_dibuat'    => $cleanName3 . '@tsu.ac.id',
                    'kontak_hp'             => $p3->no_hp ?? '081377889900',
                    'email_alternatif'      => $p3->email_pribadi ?? 'pegawai3@gmail.com',
                    'status'                => 'Selesai',
                    'catatan_pemohon'       => 'Urgent untuk pendaftaran hibah kemendikbud.',
                    'catatan_sdm'           => 'Data terverifikasi aktif sebagai dosen tetap NIDN.',
                    'catatan_pikdi'         => 'Akun Google Workspace universitas telah dibuat dan aktif dengan kapasitas penyimpanan enterprise. Password sementara telah dikirimkan ke WhatsApp pemohon.',
                    'password_sementara'    => 'TigaSerangkai#2026',
                    'validated_by'          => $sdmUser ? $sdmUser->id : null,
                    'validated_at'          => Carbon::now()->subDays(4),
                    'processed_by'          => $pikdiUser ? $pikdiUser->id : null,
                    'processed_at'          => Carbon::now()->subDays(3),
                    'riwayat_status'        => [
                        ['tanggal' => Carbon::now()->subDays(5)->format('d M Y H:i'), 'status' => 'Permintaan Email Masuk'],
                        ['tanggal' => Carbon::now()->subDays(4)->format('d M Y H:i'), 'status' => 'Divalidasi oleh SDM'],
                        ['tanggal' => Carbon::now()->subDays(3)->format('d M Y H:i'), 'status' => 'Akun Resmi Berhasil Dibuat oleh PIKDI (' . $cleanName3 . '@tsu.ac.id). Permintaan Selesai.']
                    ],
                    'created_at'            => Carbon::now()->subDays(5),
                ]
            );
        }

        // 4. Request Akun Unit / Kepanitiaan (Unit Request)
        $lppmUnit = MasterUnit::where('nama_unit', 'like', '%LPPM%')->first();
        RequestEmail::updateOrCreate(
            ['nomor_tiket' => 'REQ-MAIL-2026-004'],
            [
                'pegawai_id'            => null,
                'nama_pemohon'          => 'Sekretariat Panitia Dies Natalis 2026',
                'nik_nip'               => 'UNIT-DIES-2026',
                'unit_id'               => $lppmUnit ? $lppmUnit->id : null,
                'unit_nama'             => 'Kepanitiaan Dies Natalis TSU',
                'jabatan'               => 'Kepanitiaan Khusus',
                'tipe_pemohon'          => 'Organisasi / Kepanitiaan',
                'keperluan'             => 'Penyelenggaraan rangkaian seminar nasional dan lomba dies natalis universitas.',
                'email_usulan'          => 'diesnatalis@tsu.ac.id',
                'email_rekomendasi_sdm' => 'diesnatalis2026@tsu.ac.id',
                'email_final_dibuat'    => null,
                'kontak_hp'             => '081211223344',
                'email_alternatif'      => 'panitia.tsu@gmail.com',
                'status'                => 'Diajukan',
                'catatan_pemohon'       => 'Dibutuhkan segera untuk korespondensi pembicara seminar.',
                'riwayat_status'        => [
                    ['tanggal' => Carbon::now()->format('d M Y H:i'), 'status' => 'Permintaan Email Masuk']
                ],
                'created_at'            => Carbon::now(),
            ]
        );
    }
}
