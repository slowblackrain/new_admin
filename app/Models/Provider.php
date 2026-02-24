<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Provider extends Authenticatable
{
    use HasFactory;

    protected $table = 'fm_provider';
    protected $primaryKey = 'provider_seq';
    public $timestamps = false; // Legacy tables often don't have standard timestamps

    protected $guarded = [];

    // Optional: override if password column is different
    // public function getAuthPassword()
    // {
    //     return $this->passwd; // legacy pwd col inside fm_member?
    //     // Or if provider uses member_seq to join.
    // }
}
