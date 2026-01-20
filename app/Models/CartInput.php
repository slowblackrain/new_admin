<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartInput extends Model
{
    use HasFactory;

    protected $table = 'fm_cart_input';
    protected $primaryKey = 'cart_input_seq';
    public $timestamps = false; // No timestamps in describe output

    protected $fillable = [
        'cart_seq',
        'cart_option_seq',
        'type',
        'input_title',
        'input_value',
    ];
}
