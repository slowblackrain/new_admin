<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardBulkOrder extends Model
{
    protected $table = 'fm_boardbulkorder';
    protected $primaryKey = 'seq';
    public $timestamps = false; // r_date, m_date handled manually or via events

    protected $guarded = [];

    // Constants for status, etc. if needed
}
