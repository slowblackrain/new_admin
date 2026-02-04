<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventChoice extends Model
{
    protected $table = 'fm_event_choice';
    protected $primaryKey = 'event_choice_seq';
    public $timestamps = false;
    protected $guarded = [];
}
