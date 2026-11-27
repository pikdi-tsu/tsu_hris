<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RkatRealisasi extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rkat_realisasis';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'jumlah_realisasi' => 'decimal:2',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(RkatPengajuan::class, 'pengajuan_id', 'id');
    }

    public function anggaranItem()
    {
        return $this->belongsTo(RkatAnggaranItem::class, 'anggaran_item_id', 'id');
    }
}
