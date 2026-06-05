<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class SaldoCutiKaryawan extends Authenticatable
{
    use Notifiable;

    protected $table = 'saldo_cuti_karyawan';
    protected $primaryKey = 'id';
    protected $guarded = [];
}
