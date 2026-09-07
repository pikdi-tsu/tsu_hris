<?php

namespace App\Services;

use App\Models\PayrollPeriod;
use App\Models\HonorariumDosen;
use App\Models\MasterTarifHonorarium;
use App\Models\DataDosenTendik;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HonorariumCalculationService
{
    /**
     * Ambil seluruh matriks tarif honorarium aktif terindeks kode_jafung
     */
    public static function getTarifMatrix(): array
    {
        $tarifs = MasterTarifHonorarium::where('is_active', 1)->get();
        $matrix = [];
        foreach ($tarifs as $t) {
            $matrix[strtoupper(trim($t->kode_jafung))] = $t;
        }
        return $matrix;
    }

    /**
     * Deteksi kode jafung dosen (TP, AA, L, LK, GB) dari data kepegawaian
     */
    public static function detectKodeJafung(DataDosenTendik $pegawai): array
    {
        // Cari jabatan fungsional aktif
        $fungsional = $pegawai->jabatanFungsionals
            ? $pegawai->jabatanFungsionals->where('is_active', 'Y')->first()
            : null;

        // Cek jika terhubung langsung via jabatan_fungsional_id di master_tarif_honorariums
        if ($fungsional && $fungsional->jabatan_fungsional_id) {
            $tarifDirect = MasterTarifHonorarium::where('jabatan_fungsional_id', $fungsional->jabatan_fungsional_id)->first();
            if ($tarifDirect) {
                return ['kode' => $tarifDirect->kode_jafung, 'nama' => $tarifDirect->nama_jafung];
            }
        }

        $namaJafung = '';
        if ($fungsional && $fungsional->masterFungsional) {
            $namaJafung = $fungsional->masterFungsional->nama_jabatan ?? ($fungsional->masterFungsional->nama ?? '');
        }

        $namaUpper = strtoupper($namaJafung);
        if (str_contains($namaUpper, 'GURU BESAR') || str_contains($namaUpper, 'PROFESOR')) {
            return ['kode' => 'LK', 'nama' => $namaJafung ?: 'Guru Besar'];
        } elseif (str_contains($namaUpper, 'LEKTOR KEPALA')) {
            return ['kode' => 'LK', 'nama' => $namaJafung ?: 'Lektor Kepala'];
        } elseif (str_contains($namaUpper, 'LEKTOR')) {
            return ['kode' => 'L', 'nama' => $namaJafung ?: 'Lektor'];
        } elseif (str_contains($namaUpper, 'ASISTEN AHLI')) {
            return ['kode' => 'AA', 'nama' => $namaJafung ?: 'Asisten Ahli'];
        }

        return ['kode' => 'TP', 'nama' => $namaJafung ?: 'Tenaga Pengajar'];
    }

    /**
     * Hitung Ulang Ringkasan Periode Honorarium
     */
    public static function syncPeriodSummary(PayrollPeriod $period): void
    {
        $sumKotor    = HonorariumDosen::where('payroll_period_id', $period->id)->sum('total_honor_kotor');
        $sumPotongan = HonorariumDosen::where('payroll_period_id', $period->id)->sum('total_potongan');
        $sumBersih   = HonorariumDosen::where('payroll_period_id', $period->id)->sum('total_transfer');
        $totalDosen  = HonorariumDosen::where('payroll_period_id', $period->id)->distinct('nama_dosen')->count('nama_dosen');

        $period->update([
            'total_pegawai'     => $totalDosen,
            'total_gaji_kotor'  => $sumKotor,
            'total_potongan'    => $sumPotongan,
            'total_gaji_bersih' => $sumBersih,
        ]);
    }

    /**
     * Inisialisasi Draft Dosen untuk Periode 8A (Kelebihan SKS)
     */
    public static function initPeriodKelebihanSks(PayrollPeriod $period): void
    {
        $dosenList = DataDosenTendik::where('tipe_karyawan', 'Dosen')
            ->where('is_active', 1)
            ->with([
                'unit',
                'jabatanFungsionals' => function ($q) {
                    $q->where('is_active', 'Y')->with('masterFungsional');
                },
                'jabatanStrukturals' => function ($q) {
                    $q->where('is_active', 'Y')->with('masterStruktural');
                },
            ])
            ->get();

        $matrixTarif = self::getTarifMatrix();
        $jmlPertemuan = $period->jumlah_pertemuan ?: 3;

        foreach ($dosenList as $dosen) {
            $jafungInfo = self::detectKodeJafung($dosen);
            $kodeJafung = $jafungInfo['kode'];
            $tarifItem  = $matrixTarif[$kodeJafung] ?? ($matrixTarif['TP'] ?? null);
            $tarifSks   = $tarifItem ? $tarifItem->tarif_sks_hadir : 25000;

            // Ambil jabatan struktural jika ada
            $struktural = $dosen->jabatanStrukturals ? $dosen->jabatanStrukturals->where('is_active', 'Y')->first() : null;
            $namaStruktural = $struktural ? ($struktural->masterStruktural->nama ?? 'Dosen') : 'Dosen';
            $sksStruktur = $struktural ? 3 : 0; // Default 3 SKS jika menjabat struktural

            HonorariumDosen::create([
                'id'                   => (string) Str::uuid(),
                'payroll_period_id'    => $period->id,
                'data_dosen_tendik_id' => $dosen->id,
                'kategori_honor'       => 'kelebihan_sks',
                'nama_dosen'           => $dosen->nama,
                'nik_nip'              => $dosen->nip ?? $dosen->nik,
                'nama_unit'            => $dosen->unit->nama_unit ?? '-',
                'struktural'           => $namaStruktural,
                'kode_jafung'          => $kodeJafung,
                'nama_jafung'          => $jafungInfo['nama'],
                'sks_struktural'       => $sksStruktur,
                'sks_mengajar'         => 0,
                'total_sks'            => $sksStruktur,
                'sks_wajib'            => 12,
                'sks_lebih'            => 0,
                'tarif_sks'            => $tarifSks,
                'jumlah_pertemuan'     => $jmlPertemuan,
                'total_honor_sks'      => 0,
                'total_honor_kotor'    => 0,
                'potongan_pajak'       => 0,
                'potongan_lainnya'     => 0,
                'total_potongan'       => 0,
                'total_transfer'       => 0,
                'rekening_bank'        => $dosen->nama_bank ?? 'BSI',
                'nomor_rekening'       => $dosen->no_rekening,
                'nama_rekening'        => $dosen->atas_nama_rekening ?? $dosen->nama,
            ]);
        }

        self::syncPeriodSummary($period);
    }

    /**
     * Inisialisasi Draft Dosen untuk Periode 8B (Pembimbing & Penguji TA / KP)
     */
    public static function initPeriodPembimbingPenguji(PayrollPeriod $period): void
    {
        $dosenList = DataDosenTendik::where('tipe_karyawan', 'Dosen')
            ->where('is_active', 1)
            ->with([
                'unit',
                'jabatanFungsionals' => function ($q) {
                    $q->where('is_active', 'Y')->with('masterFungsional');
                },
                'jabatanStrukturals' => function ($q) {
                    $q->where('is_active', 'Y')->with('masterStruktural');
                },
            ])
            ->get();

        $matrixTarif = self::getTarifMatrix();

        foreach ($dosenList as $dosen) {
            $jafungInfo = self::detectKodeJafung($dosen);
            $kodeJafung = $jafungInfo['kode'];
            $tarifItem  = $matrixTarif[$kodeJafung] ?? ($matrixTarif['TP'] ?? null);

            $tarifBimbingan = $tarifItem ? $tarifItem->tarif_bimbingan_ta : 200000;
            $tarifPenguji   = $tarifItem ? $tarifItem->tarif_penguji_ta : 75000;
            $tarifKP        = $tarifItem ? $tarifItem->tarif_kerja_praktek : 150000;

            $struktural = $dosen->jabatanStrukturals ? $dosen->jabatanStrukturals->where('is_active', 'Y')->first() : null;
            $namaStruktural = $struktural ? ($struktural->masterStruktural->nama ?? 'Dosen') : 'Dosen';

            HonorariumDosen::create([
                'id'                   => (string) Str::uuid(),
                'payroll_period_id'    => $period->id,
                'data_dosen_tendik_id' => $dosen->id,
                'kategori_honor'       => 'pembimbing_penguji',
                'nama_dosen'           => $dosen->nama,
                'nik_nip'              => $dosen->nip ?? $dosen->nik,
                'nama_unit'            => $dosen->unit->nama_unit ?? '-',
                'struktural'           => $namaStruktural,
                'kode_jafung'          => $kodeJafung,
                'nama_jafung'          => $jafungInfo['nama'],
                'jml_bimbingan_ta'     => 0,
                'tarif_bimbingan_ta'   => $tarifBimbingan,
                'total_bimbingan_ta'   => 0,
                'jml_penguji_ta'       => 0,
                'tarif_penguji_ta'     => $tarifPenguji,
                'total_penguji_ta'     => 0,
                'jml_kerja_praktek'    => 0,
                'tarif_kerja_praktek'  => $tarifKP,
                'total_kerja_praktek'  => 0,
                'total_honor_kotor'    => 0,
                'potongan_pajak'       => 0,
                'potongan_lainnya'     => 0,
                'total_potongan'       => 0,
                'total_transfer'       => 0,
                'rekening_bank'        => $dosen->nama_bank ?? 'BSI',
                'nomor_rekening'       => $dosen->no_rekening,
                'nama_rekening'        => $dosen->atas_nama_rekening ?? $dosen->nama,
            ]);
        }

        self::syncPeriodSummary($period);
    }

    /**
     * Inisialisasi Draft Dosen untuk Periode 8C (Honorarium Ujian UTS & UAS)
     */
    public static function initPeriodUjian(PayrollPeriod $period): void
    {
        $dosenList = DataDosenTendik::where('tipe_karyawan', 'Dosen')
            ->where('is_active', 1)
            ->with([
                'unit',
                'jabatanFungsionals' => function ($q) {
                    $q->where('is_active', 'Y')->with('masterFungsional');
                },
                'jabatanStrukturals' => function ($q) {
                    $q->where('is_active', 'Y')->with('masterStruktural');
                },
            ])
            ->get();

        $matrixTarif = self::getTarifMatrix();

        foreach ($dosenList as $dosen) {
            $jafungInfo = self::detectKodeJafung($dosen);
            $kodeJafung = $jafungInfo['kode'];
            $tarifItem  = $matrixTarif[$kodeJafung] ?? ($matrixTarif['TP'] ?? null);

            $tarifSoal    = $tarifItem ? $tarifItem->tarif_soal_teori : 25000;
            $tarifKoreksi = $tarifItem ? $tarifItem->tarif_koreksi_teori : 2000;

            $struktural = $dosen->jabatanStrukturals ? $dosen->jabatanStrukturals->where('is_active', 'Y')->first() : null;
            $namaStruktural = $struktural ? ($struktural->masterStruktural->nama ?? 'Dosen') : 'Dosen';

            HonorariumDosen::create([
                'id'                   => (string) Str::uuid(),
                'payroll_period_id'    => $period->id,
                'data_dosen_tendik_id' => $dosen->id,
                'kategori_honor'       => 'ujian',
                'nama_dosen'           => $dosen->nama,
                'nik_nip'              => $dosen->nip ?? $dosen->nik,
                'nama_unit'            => $dosen->unit->nama_unit ?? '-',
                'struktural'           => $namaStruktural,
                'kode_jafung'          => $kodeJafung,
                'nama_jafung'          => $jafungInfo['nama'],
                'mata_kuliah'          => '-',
                'tipe_kelas'           => 'T',
                'jml_kelas_uts'        => 0,
                'jml_kelas_uas'        => 0,
                'total_kelas_soal'     => 0,
                'tarif_soal'           => $tarifSoal,
                'total_honor_soal'     => 0,
                'jml_peserta_uts'      => 0,
                'jml_peserta_uas'      => 0,
                'total_peserta_koreksi'=> 0,
                'tarif_koreksi'        => $tarifKoreksi,
                'total_honor_koreksi'  => 0,
                'total_honor_kotor'    => 0,
                'potongan_pajak'       => 0,
                'potongan_lainnya'     => 0,
                'total_potongan'       => 0,
                'total_transfer'       => 0,
                'rekening_bank'        => $dosen->nama_bank ?? 'BSI',
                'nomor_rekening'       => $dosen->no_rekening,
                'nama_rekening'        => $dosen->atas_nama_rekening ?? $dosen->nama,
            ]);
        }

        self::syncPeriodSummary($period);
    }

    /**
     * Kalkulasi lengkap seluruh komponen honorarium dosen (8A, 8B, 8C, potongan, dan net transfer)
     */
    public static function calculateRowData(array $input, ?MasterTarifHonorarium $tarif = null): array
    {
        // 8A. SKS
        $sksStruktur = floatval($input['sks_struktural'] ?? 0);
        $sksMengajar = floatval($input['sks_mengajar'] ?? 0);
        $totalSks    = $sksStruktur + $sksMengajar;
        $sksLebih    = max(0, $totalSks - 12);
        $tarifSks    = isset($input['tarif_sks']) ? floatval($input['tarif_sks']) : ($tarif ? floatval($tarif->tarif_sks_hadir) : 25000);
        $pertemuan   = intval($input['jumlah_pertemuan'] ?? 3);
        if ($pertemuan <= 0) $pertemuan = 3;
        $totalHonorSks = $sksLebih * $tarifSks * $pertemuan;

        // 8B. Bimbingan & Penguji
        $jmlBimbingan   = intval($input['jml_bimbingan_ta'] ?? 0);
        $tarifBimbingan = isset($input['tarif_bimbingan_ta']) ? floatval($input['tarif_bimbingan_ta']) : ($tarif ? floatval($tarif->tarif_bimbingan_ta) : 200000);
        $totalBimbingan = $jmlBimbingan * $tarifBimbingan;

        $jmlPenguji   = intval($input['jml_penguji_ta'] ?? 0);
        $tarifPenguji = isset($input['tarif_penguji_ta']) ? floatval($input['tarif_penguji_ta']) : ($tarif ? floatval($tarif->tarif_penguji_ta) : 75000);
        $totalPenguji = $jmlPenguji * $tarifPenguji;

        $jmlKp   = intval($input['jml_kerja_praktek'] ?? 0);
        $tarifKp = isset($input['tarif_kerja_praktek']) ? floatval($input['tarif_kerja_praktek']) : ($tarif ? floatval($tarif->tarif_kerja_praktek) : 150000);
        $totalKp = $jmlKp * $tarifKp;

        // 8C. Ujian (UTS & UAS)
        $tipeKelas    = strtoupper(trim($input['tipe_kelas'] ?? 'T'));
        $isTeoriPraktik = in_array($tipeKelas, ['T/P', 'P', 'TP']);

        $klsUts       = intval($input['jml_kelas_uts'] ?? 0);
        $klsUas       = intval($input['jml_kelas_uas'] ?? 0);
        $totKls       = $klsUts + $klsUas;

        $defaultTarifSoal = $isTeoriPraktik 
            ? ($tarif ? floatval($tarif->tarif_soal_teori_praktik) : 30000) 
            : ($tarif ? floatval($tarif->tarif_soal_teori) : 25000);

        $defaultTarifKoreksi = $isTeoriPraktik 
            ? ($tarif ? floatval($tarif->tarif_koreksi_teori_praktik) : 2500) 
            : ($tarif ? floatval($tarif->tarif_koreksi_teori) : 2000);

        $tarifSoal    = (isset($input['tarif_soal']) && floatval($input['tarif_soal']) > 0) ? floatval($input['tarif_soal']) : $defaultTarifSoal;
        $totalSoal    = $totKls * $tarifSoal;

        $mhsUts       = intval($input['jml_peserta_uts'] ?? 0);
        $mhsUas       = intval($input['jml_peserta_uas'] ?? 0);
        $totMhs       = $mhsUts + $mhsUas;
        $tarifKoreksi = (isset($input['tarif_koreksi']) && floatval($input['tarif_koreksi']) > 0) ? floatval($input['tarif_koreksi']) : $defaultTarifKoreksi;
        $totalKoreksi = $totMhs * $tarifKoreksi;

        // Total Kotor
        $totalKotor = $totalHonorSks + $totalBimbingan + $totalPenguji + $totalKp + $totalSoal + $totalKoreksi;

        // Potongan
        $potPajak   = floatval($input['potongan_pajak'] ?? 0);
        $potLain    = floatval($input['potongan_lainnya'] ?? 0);
        $totPotongan= $potPajak + $potLain;
        $totTransfer= max(0, $totalKotor - $totPotongan);

        return [
            // 8A
            'sks_struktural'       => $sksStruktur,
            'sks_mengajar'         => $sksMengajar,
            'total_sks'            => $totalSks,
            'sks_wajib'            => 12,
            'sks_lebih'            => $sksLebih,
            'tarif_sks'            => $tarifSks,
            'jumlah_pertemuan'     => $pertemuan,
            'total_honor_sks'      => $totalHonorSks,
            // 8B
            'jml_bimbingan_ta'     => $jmlBimbingan,
            'tarif_bimbingan_ta'   => $tarifBimbingan,
            'total_bimbingan_ta'   => $totalBimbingan,
            'jml_penguji_ta'       => $jmlPenguji,
            'tarif_penguji_ta'     => $tarifPenguji,
            'total_penguji_ta'     => $totalPenguji,
            'jml_kerja_praktek'    => $jmlKp,
            'tarif_kerja_praktek'  => $tarifKp,
            'total_kerja_praktek'  => $totalKp,
            // 8C
            'mata_kuliah'          => $input['mata_kuliah'] ?? null,
            'tipe_kelas'           => $input['tipe_kelas'] ?? 'T',
            'jml_kelas_uts'        => $klsUts,
            'jml_kelas_uas'        => $klsUas,
            'total_kelas_soal'     => $totKls,
            'tarif_soal'           => $tarifSoal,
            'total_honor_soal'     => $totalSoal,
            'jml_peserta_uts'      => $mhsUts,
            'jml_peserta_uas'      => $mhsUas,
            'total_peserta_koreksi'=> $totMhs,
            'tarif_koreksi'        => $tarifKoreksi,
            'total_honor_koreksi'  => $totalKoreksi,
            // Ringkasan
            'total_honor_kotor'    => $totalKotor,
            'potongan_pajak'       => $potPajak,
            'potongan_lainnya'     => $potLain,
            'keterangan_potongan'  => $input['keterangan_potongan'] ?? null,
            'total_potongan'       => $totPotongan,
            'total_transfer'       => $totTransfer,
            'catatan_koreksi'      => $input['catatan_koreksi'] ?? null,
        ];
    }
}
