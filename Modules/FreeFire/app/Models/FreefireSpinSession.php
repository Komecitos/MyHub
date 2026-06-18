<?php

namespace Modules\FreeFire\Models;

use Illuminate\Database\Eloquent\Model;

class FreefireSpinSession extends Model
{
    protected $table = 'freefire_spin_sessions';

    protected $fillable = [
        'item_name',
        'spin_type',
        'token_needed',
        'starting_token',
        'luck_percentage',
        'discount_percentage',
        'modal_diamond',
        'spent_diamond',
        'current_spin',
        'current_token',
        'status',
        'event_start',
        'event_end',
    ];

    public function logs()
    {
        return $this->hasMany(FreefireSpinLog::class, 'session_id');
    }

    public function slots()
    {
        return $this->hasMany(FreefireWheelSlot::class, 'session_id');
    }
}   