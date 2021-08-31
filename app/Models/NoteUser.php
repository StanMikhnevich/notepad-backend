<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\NoteUser
 *
 * @property int $id
 * @property int $note_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Note $note
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser whereNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteUser whereUserId($value)
 * @mixin Eloquent
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
