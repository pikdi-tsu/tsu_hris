<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RkatAnggaranItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rkat_anggaran_items';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'kuantitas' => 'integer',
        'harga_satuan' => 'decimal:2',
        'total_biaya' => 'decimal:2',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(RkatPengajuan::class, 'pengajuan_id', 'id');
    }

    public function akun()
    {
        return $this->belongsTo(RkatAkun::class, 'akun_id', 'id');
    }

    public function sumberDana()
    {
        return $this->belongsTo(RkatSumberDana::class, 'sumber_dana_id', 'id');
    }
}
