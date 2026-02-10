<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesDetail extends Model
{
    protected $table = 'fm_sales_detail';
    protected $primaryKey = 'idx'; // Assuming idx is primary key based on legacys usage
    public $timestamps = false;

    protected $guarded = [];

    public function salesList()
    {
        return $this->belongsTo(SalesList::class, 'sales_id', 'sales_id');
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class, 'sales_seq', 'seq');
    }
}
