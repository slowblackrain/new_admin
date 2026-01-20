<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardManager extends Model
{
    protected $table = 'fm_boardmanager';
    protected $primaryKey = 'seq';
    public $timestamps = false; // r_date, m_date

    protected $guarded = [];

    // Find by Board ID string (e.g., 'notice')
    public static function findById($boardId)
    {
        return self::where('id', $boardId)->first();
    }
}
