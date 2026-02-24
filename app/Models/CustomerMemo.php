<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerMemo extends Model
{
    use HasFactory;

    protected $table = 'fm_customer_memo';
    protected $primaryKey = 'memo_idx';
    public $timestamps = false; // Legacy table has update_time

    protected $fillable = [
        'memo',
        'sort',
        'point',
        'update_time'
    ];

    protected $dates = [
        'update_time'
    ];

    /**
     * 포인트(중요) 여부를 boolean 처럼 쓰기 위함. y / n
     */
    public function getIsPointAttribute()
    {
        return $this->point === 'y';
    }
}
