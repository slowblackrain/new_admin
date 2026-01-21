<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Seller extends Authenticatable
{
    use Notifiable;

    protected $table = 'fm_provider';
    protected $primaryKey = 'provider_seq';
    public $timestamps = false;

    protected $fillable = [
        'provider_id',
        'provider_passwd',
        'provider_name',
        'provider_gb',
        'provider_status',
        'provider_email',
        // Add other necessary fields
    ];

    protected $hidden = [
        'provider_passwd',
        'remember_token',
    ];

    // Authenticatable override
    public function getAuthPassword()
    {
        return $this->provider_passwd;
    }

    public function getAuthPasswordName()
    {
        return 'provider_passwd';
    }

    // Disable automatic rehashing for legacy MD5
    public function getAttribute($key)
    {
        if ($key === 'password') {
            return $this->provider_passwd;
        }
        return parent::getAttribute($key);
    }
}
