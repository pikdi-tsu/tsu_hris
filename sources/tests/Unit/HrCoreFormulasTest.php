<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\HonorariumCalculationService;

class HrCoreFormulasTest extends TestCase
{
    /**
     * Test formula THR: Masa kerja >= 12 bulan berhak mendapat THR Penuh (1x Upah Tetap).
     */
    public function test_thr_full_for_12_months_or_more()
    {
        $upahTetap = 5000000;
        $masaKerjaBulan = 14;

        if ($masaKerjaBulan >= 12) {
            $statusThr = 'Penuh';
            $nominalThr = $upahTetap;
        } elseif ($masaKerjaBulan >= 1) {
            $statusThr = 'Pro Rata';
            $nominalThr = round(($masaKerjaBulan / 12) * $upahTetap);
        } else {
            $statusThr = 'Pro Rata';
            $nominalThr = 0;
        }

        $this->assertEquals('Penuh', $statusThr);
        $this->assertEquals(5000000, $nominalThr);
    }

    /**
     * Test formula THR: Masa kerja 6 bulan mendapat THR Pro Rata (6/12 * Upah Tetap = 50%).
     */
    public function test_thr_prorata_for_6_months()
    {
        $upahTetap = 6000000;
        $masaKerjaBulan = 6;

        if ($masaKerjaBulan >= 12) {
            $statusThr = 'Penuh';
            $nominalThr = $upahTetap;
        } elseif ($masaKerjaBulan >= 1) {
            $statusThr = 'Pro Rata';
            $nominalThr = round(($masaKerjaBulan / 12) * $upahTetap);
        } else {
            $statusThr = 'Pro Rata';
            $nominalThr = 0;
        }

        $this->assertEquals('Pro Rata', $statusThr);
        $this->assertEquals(3000000, $nominalThr);
    }

    /**
     * Test formula THR: Masa kerja < 1 bulan belum berhak mendapat THR (0).
     */
    public function test_thr_zero_for_less_than_1_month()
    {
        $upahTetap = 4500000;
        $masaKerjaBulan = 0;

        if ($masaKerjaBulan >= 12) {
            $statusThr = 'Penuh';
            $nominalThr = $upahTetap;
        } elseif ($masaKerjaBulan >= 1) {
            $statusThr = 'Pro Rata';
            $nominalThr = round(($masaKerjaBulan / 12) * $upahTetap);
        } else {
            $statusThr = 'Pro Rata';
            $nominalThr = 0;
        }

        $this->assertEquals(0, $nominalThr);
    }

    /**
     * Test formula Honorarium SKS: Kelebihan SKS diatas SKS wajib (12 SKS).
     */
    public function test_honorarium_sks_excess_calculation()
    {
        $input = [
            'sks_struktural'   => 3,
            'sks_mengajar'     => 13, // Total 16 SKS => Kelebihan 4 SKS
            'tarif_sks'        => 30000,
            'jumlah_pertemuan' => 4,
            'potongan_pajak'   => 0,
            'potongan_lainnya' => 0,
        ];

        $result = HonorariumCalculationService::calculateRowData($input);

        $this->assertEquals(16, $result['total_sks']);
        $this->assertEquals(4, $result['sks_lebih']);
        // 4 SKS * 30.000 * 4 pertemuan = 480.000
        $this->assertEquals(480000, $result['total_honor_sks']);
        $this->assertEquals(480000, $result['total_transfer']);
    }

    /**
     * Test formula Honorarium Bimbingan, Penguji, dan Ujian dengan potongan pajak.
     */
    public function test_honorarium_full_components_with_tax()
    {
        $input = [
            'sks_struktural'     => 0,
            'sks_mengajar'       => 12, // 0 lebih
            'jml_bimbingan_ta'   => 2,
            'tarif_bimbingan_ta' => 200000, // 400.000
            'jml_penguji_ta'     => 4,
            'tarif_penguji_ta'   => 75000,  // 300.000
            'jml_kerja_praktek'  => 1,
            'tarif_kerja_praktek'=> 150000, // 150.000
            'jml_kelas_uts'      => 2,
            'jml_kelas_uas'      => 2,
            'tarif_soal'         => 25000,  // 4 * 25.000 = 100.000
            'jml_peserta_uts'    => 20,
            'jml_peserta_uas'    => 20,
            'tarif_koreksi'      => 2000,   // 40 * 2.000 = 80.000
            'potongan_pajak'     => 50000,
            'potongan_lainnya'   => 10000,
        ];

        // Total Kotor = 0 + 400.000 + 300.000 + 150.000 + 100.000 + 80.000 = 1.030.000
        // Total Potongan = 50.000 + 10.000 = 60.000
        // Net Transfer = 970.000
        $result = HonorariumCalculationService::calculateRowData($input);

        $this->assertEquals(1030000, $result['total_honor_kotor']);
        $this->assertEquals(60000, $result['total_potongan']);
        $this->assertEquals(970000, $result['total_transfer']);
    }
}
