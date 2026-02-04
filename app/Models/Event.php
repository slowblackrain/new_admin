<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'fm_event';
    protected $primaryKey = 'event_seq';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'regist_date' => 'datetime',
        'update_date' => 'datetime',
    ];

    public function choices()
    {
        return $this->hasMany(EventChoice::class, 'event_seq', 'event_seq');
    }
}
