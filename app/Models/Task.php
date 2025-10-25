<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'priority',
    ];

    /**
     * Always order by priority ascending by default when querying lists.
     */
    protected static function booted()
    {
        static::addGlobalScope('priority', function ($query) {
            $query->orderBy('priority');
        });
    }
}
