<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodsInput extends Model
{
    use HasFactory;

    protected $table = 'fm_goods_input';
    protected $primaryKey = 'input_seq';
    public $timestamps = false; // Based on describe output, no created_at/updated_at

    protected $fillable = [
        'goods_seq',
        'input_name',
        'input_form',
        'input_limit',
        'input_require',
    ];
}
