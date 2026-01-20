<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DeliveryAddress extends Model
{
    use HasFactory;

    protected $table = 'fm_delivery_address';
    protected $primaryKey = 'address_seq';
    public $timestamps = false;

    const CREATED_AT = 'regist_date';
    const UPDATED_AT = 'update_date';

    protected $guarded = [];

    // Scope for current user
    public function scopeCurrentUser($query)
    {
        return $query->where('member_seq', Auth::id());
    }
}
