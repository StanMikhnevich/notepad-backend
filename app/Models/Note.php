<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'author',
        'private',
        'title',
        'text',
    ];

    protected $casts = [
        'id' => 'string'
    ];

}
