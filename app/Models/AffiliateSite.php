<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateSite extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'domain', 'sync_type', 'api_key', 'login_id', 'login_password', 'is_active'];
}
