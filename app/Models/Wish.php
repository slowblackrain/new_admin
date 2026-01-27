<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wish extends Model
{
    use HasFactory;

    protected $table = 'fm_goods_wish';
    protected $primaryKey = 'wish_seq';
    public $timestamps = false; // regist_date only

    protected $guarded = [];

    // Relationship to Goods
    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_seq', 'goods_seq');
    }

    // Scope for current user
    public function scopeCurrentUser($query)
    {
        return $query->where('member_seq', \Illuminate\Support\Facades\Auth::id());
    }
}
