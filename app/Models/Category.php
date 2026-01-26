<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'fm_category';
    protected $primaryKey = 'id';
    public $timestamps = false; // fm_category has regist_date/update_date but not standard created_at/updated_at?
    // inspect results showed regist_date, update_date.
    
    protected $guarded = [];

    const CREATED_AT = 'regist_date';
    const UPDATED_AT = 'update_date';

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id')->orderBy('position', 'asc');
    }
}
