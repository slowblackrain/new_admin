<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationLink extends Model
{
    use HasFactory;

    protected $table = 'fm_location_link';
    protected $primaryKey = 'location_link_seq';
    public $timestamps = false;

    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_code', 'location_code');
    }

    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_seq', 'goods_seq');
    }
}
