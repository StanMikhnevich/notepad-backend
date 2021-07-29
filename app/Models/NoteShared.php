<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\NoteShared
 *
 * @property int $id
 * @property int $note_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Note $note
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|NoteShared newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoteShared newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoteShared query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoteShared whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteShared whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteShared whereNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteShared whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteShared whereUserId($value)
 * @mixin \Eloquent
 */
class NoteShared extends Model
{
    use HasFactory;

    protected $fillable = [
        'note_id',
        'user_id'
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'note_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
