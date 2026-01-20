<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartOption extends Model
{
    use HasFactory;

    protected $table = 'fm_cart_option';
    protected $primaryKey = 'cart_option_seq';
    public $timestamps = false;

    protected $guarded = [];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_seq', 'cart_seq');
    }
}
