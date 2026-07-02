<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class DataAbsensi extends Authenticatable
{
    use Notifiable;

    protected $table = 'data_absensi';
    protected $primaryKey = 'id';
    protected $guarded = [];
}
