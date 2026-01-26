<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $table = 'fm_brand';
    protected $primaryKey = 'category_code'; // Brand uses category_code as PK in legacy
    public $incrementing = false; // non-integer PK
    protected $keyType = 'string';
    public $timestamps = false; // Legacy table

    protected $guarded = [];

    // Relationships if needed in future
}
