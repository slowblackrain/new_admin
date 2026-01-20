<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Member extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'fm_member';
    protected $primaryKey = 'member_seq';
    public $timestamps = false;

    // Legacy password field override
    public function getAuthPassword()
    {
        return $this->password;
    }

    protected $guarded = [];

    protected $hidden = [
        'password',
    ];
}
