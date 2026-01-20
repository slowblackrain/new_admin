<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'fm_cart';
    protected $primaryKey = 'cart_seq';
    public $timestamps = false; // Legacy tables usually manage dates manually or allow nulls, but let's check.
    // fm_cart has regist_date and update_date. Eloquent expects created_at/updated_at.
    // We should override strict timestamp handling or map them.
    const CREATED_AT = 'regist_date';
    const UPDATED_AT = 'update_date';

    protected $guarded = [];

    // Relationships
    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_seq', 'goods_seq');
    }

    public function options()
    {
        return $this->hasMany(CartOption::class, 'cart_seq', 'cart_seq');
    }

    public function inputs()
    {
        return $this->hasMany(CartInput::class, 'cart_seq', 'cart_seq');
    }

    // Scopes
    public function scopeCurrentUser($query)
    {
        if (Auth::check()) {
            return $query->where('member_seq', Auth::id());
        } else {
            return $query->where('session_id', Session::getId());
        }
    }
}
