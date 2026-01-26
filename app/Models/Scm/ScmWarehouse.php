<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScmWarehouse extends Model
{
    use HasFactory;

    protected $table = 'fm_scm_warehouse';
    protected $primaryKey = 'wh_seq';
    public $timestamps = false;

    protected $guarded = [];

    // Legacy date fields
    const CREATED_AT = 'wh_regist_date';
    const UPDATED_AT = 'wh_modify_date';

    // Relationships
}
