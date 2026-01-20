<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $table = 'fm_boarddata';
    protected $primaryKey = 'seq';
    public $timestamps = false;

    // Use 'r_date' for creation time
    const CREATED_AT = 'r_date';
    const UPDATED_AT = 'm_date';

    protected $guarded = [];

    // Relationship to Board Manager
    public function manager()
    {
        return $this->belongsTo(BoardManager::class, 'boardid', 'id');
    }

    // Scope for specific board type
    public function scopeBoard($query, $boardId)
    {
        return $query->where('boardid', $boardId);
    }

    // Scope for Notices
    public function scopeNotice($query)
    {
        return $query->where('notice', 1);
    }
}
