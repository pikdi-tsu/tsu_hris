<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CutiKaryawan extends Authenticatable
{
    use Notifiable;

    protected $table = 'cuti_karyawan';
    protected $primaryKey = 'id';
    protected $guarded = [];
}
