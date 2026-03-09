<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScmOrderDefaultinfo extends Model
{
    use HasFactory;

    protected $table = 'fm_scm_order_defaultinfo';
    public $timestamps = false; // Legacy table may not have created_at/updated_at
    
    // primary key is likely goods_seq depending on schema, but avoiding strict pk definition if not needed
    protected $primaryKey = 'goods_seq';
    protected $guarded = [];
}
