<?php

namespace App\Models\Admin\Goods;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'fm_brand';
    public $timestamps = false;
    protected $primaryKey = 'id';
    
    protected $guarded = [];

    // Helper to get formatted code
    public static function generateNextCode($parentCode = '')
    {
        $len = strlen($parentCode) + 4;
        $prefix = $parentCode;
        
        $maxCode = self::where('category_code', 'like', $prefix . '%')
            ->whereRaw('LENGTH(category_code) = ?', [$len])
            ->max('category_code');

        if (!$maxCode) {
            return $prefix . '0001';
        }
        
        // Increment last 4 digits
        $lastNum = intval(substr($maxCode, -4));
        return $prefix . sprintf('%04d', $lastNum + 1);
    }
}
