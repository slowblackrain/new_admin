<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;

class ScmTrader extends Model
{
    protected $table = 'fm_scm_trader';
    protected $primaryKey = 'trader_seq';
    public $timestamps = false;

    protected $fillable = [
        'trader_id', 'trader_name', 'trader_use', 'trader_group', 
        'trader_type', 'trader_location', 'regist_number', 
        'company_owner', 'business_type', 'business_category',
        'bank_name', 'bank_owner', 'bank_number',
        'phone_number', 'fax_number', 'email', 'zipcode', 'address', 'address_detail',
        'admin_memo', 'regist_date', 'modify_date'
    ];
}
