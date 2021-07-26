<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\NoteAttachment
 *
 * @property int $id
 * @property string $note_id
 * @property string $_name
 * @property string $name
 * @property string $path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Note $note
 * @method static \Illuminate\Database\Eloquent\Builder|NoteAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoteAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoteAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoteAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteAttachment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteAttachment whereNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteAttachment wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteAttachment whereUpdatedAt($value)
 */
class NoteAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'note_id',
        'original',
        'name',
        'path',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'note_id');
    }

}
