<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScmStore extends Model
{
    use HasFactory;

    protected $table = 'fm_scm_store';
    protected $primaryKey = 'store_seq';
    public $timestamps = false;

    protected $guarded = [];

    // Legacy date fields
    const CREATED_AT = 'regist_date';
    const UPDATED_AT = 'modify_date';
}
