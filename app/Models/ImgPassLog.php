<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImgPassLog extends Model
{
    use HasFactory;

    protected $table = 'fm_img_pass_log';
    protected $primaryKey = 'seq';
    public $timestamps = false; // reg_date manual handling

    protected $fillable = [
        'manager_id',
        'reg_date',
        'miss_msg',
        'log_msg'
    ];
}
