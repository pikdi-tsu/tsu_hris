<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class MasterCuti extends Authenticatable
{
    use Notifiable;

    protected $table = 'master_cuti';
    protected $primaryKey = 'id';
    protected $guarded = [];
}
