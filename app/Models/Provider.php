<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $table = 'fm_provider';
    protected $primaryKey = 'provider_seq';
    public $timestamps = false; // Legacy tables often don't have standard timestamps

    protected $guarded = [];
}
