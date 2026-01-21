<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'fm_manager';
    protected $primaryKey = 'manager_seq';
    public $timestamps = false; // Legacy table may not have standard timestamps

    protected $fillable = [
        'manager_id', 'mpasswd', 'mname', 'lastlogin_date'
    ];

    protected $hidden = [
        'mpasswd',
    ];

    public function getAuthPassword()
    {
        return $this->mpasswd;
    }
}
