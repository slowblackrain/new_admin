<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AffiliateCategoryMapping extends Model
{
    use HasFactory;
    
    protected $fillable = ['affiliate_site_id', 'dometopia_category_code', 'affiliate_category_code', 'affiliate_category_name'];
}
