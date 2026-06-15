<?php

namespace Modules\Todo\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Todo\Models\Todo;

class TodoLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'todo_id',
        'action',
        'todo_title',
        'description',
    ];

    public function todo()
    {
        return $this->belongsTo(Todo::class);
    }
}
