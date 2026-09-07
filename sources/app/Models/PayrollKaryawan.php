<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollKaryawan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payroll_karyawans';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'persen_gapok'          => 'integer',
        'gaji_pokok'            => 'decimal:2',
        'tunjangan_fungsional'  => 'decimal:2',
        'tunjangan_struktural'  => 'decimal:2',
        'tunjangan_khusus'      => 'decimal:2',
        'tunjangan_keluarga'    => 'decimal:2',
        'tunjangan_anak'        => 'decimal:2',
        'tunjangan_kesehatan'   => 'decimal:2',
        'gaji_tetap'            => 'decimal:2',
        'hari_hadir_valid'      => 'decimal:2',
        'tarif_transport'       => 'decimal:2',
        'total_transport'       => 'decimal:2',
        'total_jam_lembur'      => 'decimal:2',
        'total_lembur'          => 'decimal:2',
        'hari_unpaid_leave'     => 'decimal:2',
        'rate_unpaid_leave'     => 'decimal:2',
        'potongan_unpaid_leave' => 'decimal:2',
        'potongan_bpjs_kes'     => 'decimal:2',
        'potongan_lainnya'      => 'decimal:2',
        'gaji_kotor'            => 'decimal:2',
        'total_potongan'        => 'decimal:2',
        'gaji_bersih'           => 'decimal:2',
    ];

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function pegawai()
    {
        return $this->belongsTo(DataDosenTendik::class, 'data_dosen_tendik_id');
    }

    /**
     * Hitung ulang total gaji untuk baris ini
     */
    public function recalculateTotals(): void
    {
        $this->gaji_tetap = floatval($this->gaji_pokok)
            + floatval($this->tunjangan_fungsional)
            + floatval($this->tunjangan_struktural)
            + floatval($this->tunjangan_khusus)
            + floatval($this->tunjangan_keluarga)
            + floatval($this->tunjangan_anak)
            + floatval($this->tunjangan_kesehatan);

        $this->total_transport = floatval($this->hari_hadir_valid) * floatval($this->tarif_transport);

        if (floatval($this->rate_unpaid_leave) <= 0 && floatval($this->gaji_pokok) > 0) {
            $this->rate_unpaid_leave = round(floatval($this->gaji_pokok) / 25, 2);
        }
        $this->potongan_unpaid_leave = floatval($this->hari_unpaid_leave) * floatval($this->rate_unpaid_leave);

        $this->gaji_kotor = floatval($this->gaji_tetap)
            + floatval($this->total_transport)
            + floatval($this->total_lembur);

        $this->total_potongan = floatval($this->potongan_unpaid_leave)
            + floatval($this->potongan_bpjs_kes)
            + floatval($this->potongan_lainnya);

        $this->gaji_bersih = max(0, floatval($this->gaji_kotor) - floatval($this->total_potongan));
    }
}
