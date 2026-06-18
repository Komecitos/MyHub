<?php

namespace Modules\FreeFire\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\FreeFire\Models\FreefireSpinSession;

class FreefireSpinLog extends Model
{
    public $timestamps = false;

    protected $table = 'freefire_spin_logs';

    protected $fillable = [
        'session_id',
        'spin_number',
        'diamond_spent',
        'result',
        'token_gained',
    ];

    public function session()
    {
        return $this->belongsTo(FreefireSpinSession::class, 'session_id');
    }
}
