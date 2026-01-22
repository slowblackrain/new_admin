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

    /**
     * Decrypt legacy AES encrypted fields
     */
    private function decryptLegacy($value)
    {
        if (empty($value)) return $value;
        
        // If it looks like an email or phone (contains @, -, etc), return as is
        // Encrypted data is usually a Hex string (0-9, A-F)
        if (!ctype_xdigit((string)$value)) {
            return $value;
        }

        try {
            // Key verified from user input
            $key = 'OTgwNTc='; 
            $result = \Illuminate\Support\Facades\DB::select("SELECT AES_DECRYPT(UNHEX(?), ?) as d", [$value, $key]);
            
            if (!empty($result[0]->d)) {
                return $result[0]->d;
            }
        } catch (\Exception $e) {
            // Log error or ignore
        }
        
        return $value;
    }

    public function getEmailAttribute($value)
    {
        return $this->decryptLegacy($value);
    }

    public function getPhoneAttribute($value)
    {
        return $this->decryptLegacy($value);
    }

    public function getCellphoneAttribute($value)
    {
        return $this->decryptLegacy($value);
    }
}
