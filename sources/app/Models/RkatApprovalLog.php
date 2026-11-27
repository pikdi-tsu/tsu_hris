<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RkatApprovalLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rkat_approval_logs';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'nominal_disetujui' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(RkatPengajuan::class, 'pengajuan_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
