<?php

namespace Modules\FreeFire\Models;

use Illuminate\Database\Eloquent\Model;

class FreefireWheelSlot extends Model
{
    protected $table = 'freefire_wheel_slots';

    protected $fillable = [
        'session_id',
        'type',
        'token_value',
        'item_name',
        'token_exchange',
        'rarity',
        'slot_count',
    ];

    public function session()
    {
        return $this->belongsTo(FreefireSpinSession::class, 'session_id');
    }
}
