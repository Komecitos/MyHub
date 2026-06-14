<?php

namespace Modules\Todo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Todo\Database\Factories\TodoFactory;

class Todo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'category',
        'due_date',
        'due_time',
        'is_recurring',
        'recur_type',
        'recur_interval',
        'parent_id',
        'completed_at',
    ];
    // protected static function newFactory(): TodoFactory
    // {
    //     // return TodoFactory::new();
    // }
}
