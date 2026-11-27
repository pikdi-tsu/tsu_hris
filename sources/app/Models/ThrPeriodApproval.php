<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThrPeriodApproval extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'thr_period_approvals';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function period()
    {
        return $this->belongsTo(ThrPeriod::class, 'thr_period_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(DataDosenTendik::class, 'karyawan_id');
    }
}
