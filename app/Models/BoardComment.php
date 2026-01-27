<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardComment extends Model
{
    protected $table = 'fm_board_comment';
    protected $primaryKey = 'seq';
    public $timestamps = false;

    protected $guarded = [];

    // Relationship to Parent Post
    public function board()
    {
        return $this->belongsTo(Board::class, 'parent', 'seq');
    }

    // Accessor: masking user ID for privacy
    // ...
}
