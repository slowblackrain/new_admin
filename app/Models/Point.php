<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;

    protected $table = 'fm_point';
    protected $primaryKey = 'point_seq';
    public $timestamps = false;

    protected $guarded = [];

    // Relationships
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_seq', 'member_seq');
    }
}
