<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponDownload extends Model
{
    use HasFactory;

    protected $table = 'fm_download';
    protected $primaryKey = 'download_seq';
    public $timestamps = false;

    protected $guarded = [];

    // Relationships
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_seq', 'coupon_seq');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_seq', 'member_seq');
    }
}
