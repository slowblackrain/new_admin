<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FmConfig extends Model
{
    use HasFactory;

    protected $table = 'fm_config';
    protected $primaryKey = 'codecd';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'groupcd',
        'codecd',
        'value',
    ];
}
