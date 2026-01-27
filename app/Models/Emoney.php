<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emoney extends Model
{
    use HasFactory;

    protected $table = 'fm_emoney';
    protected $primaryKey = 'emoney_seq';
    public $timestamps = false;

    protected $guarded = [];

    // Relationships
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_seq', 'member_seq');
    }
}
