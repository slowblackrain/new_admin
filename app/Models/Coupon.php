<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $table = 'fm_coupon';
    protected $primaryKey = 'coupon_seq';
    public $timestamps = false; // Legacy table, might not use created_at/updated_at

    protected $guarded = [];

    // Relationships
    public function downloads()
    {
        return $this->hasMany(CouponDownload::class, 'coupon_seq', 'coupon_seq');
    }

    public function issueCategories()
    {
        return $this->hasMany(CouponIssueCategory::class, 'coupon_seq', 'coupon_seq');
    }

    public function issueGoods()
    {
        return $this->hasMany(CouponIssueGoods::class, 'coupon_seq', 'coupon_seq');
    }
}
