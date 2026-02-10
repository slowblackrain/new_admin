<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesList extends Model
{
    protected $table = 'fm_sales_list';
    protected $primaryKey = 'sales_id';
    public $timestamps = false;

    protected $guarded = [];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_seq', 'member_seq');
    }

    public function details()
    {
        return $this->hasMany(SalesDetail::class, 'sales_id', 'sales_id');
    }
}
