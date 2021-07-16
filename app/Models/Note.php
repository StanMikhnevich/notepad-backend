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

    public function _author()
    {
        return $this->belongsTo(User::class, 'author');
    }

    public function attachments()
    {
        return $this->hasMany(NoteAttachment::class);
    }

}