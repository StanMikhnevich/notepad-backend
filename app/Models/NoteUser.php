<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\NoteUser
 *
 * @property int $id
 * @property int $note_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Note $note
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser whereNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser whereUserId($value)
 * @mixin \Eloquent
 */
class NoteUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'note_id',
        'user_id'
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
