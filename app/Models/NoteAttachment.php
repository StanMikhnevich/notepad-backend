<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * App\Models\NoteAttachment
 *
 * @property int $id
 * @property int $note_id
 * @property string $original
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
 * @method static \Illuminate\Database\Eloquent\Builder|NoteAttachment whereOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteAttachment wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoteAttachment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NoteAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'note_id',
        'original',
        'name',
        'type',
        'path',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'note_id');
    }

    /**
     * @return bool|null
     */
    public function deleteWithFile(): bool
    {
        return ($this->unlinkFile()) ? $this->delete() : false;
    }

    /**
     * @return bool
     */
    public function unlinkFile(): bool
    {
        $storage = Storage::disk('public');

        return $storage->exists($this->path) && $storage->delete($this->path);
    }


}
