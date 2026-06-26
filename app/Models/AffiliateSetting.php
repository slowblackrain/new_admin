<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AffiliateSetting extends Model
{
    use HasFactory;
    
    protected $fillable = ['affiliate_site_id', 'margin_rate', 'shipping_fee'];
}
