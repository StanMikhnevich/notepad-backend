<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


/**
 * App\Models\Note
 *
 * @property int $id
 * @property string $uid
 * @property int $user_id
 * @property int $private
 * @property string $title
 * @property string $text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NoteAttachment[] $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NoteShared[] $shared
 * @property-read int|null $shared_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $shared_users
 * @property-read int|null $shared_users_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Note newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Note newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Note query()
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note wherePrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Note whereUserId($value)
 * @mixin \Eloquent
 */
class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'user_id',
        'private',
        'title',
        'text',
    ];

    protected $with = ['user', 'attachments', 'shared.user'];

    /**
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'uid';
    }

    /**
     * @return string
     */
    public function getTextMarkdownedAttribute(): string
    {
        return Str::markdown($this->text);
    }

    /**
     * Get note author
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get note sharing
     *
     * @return HasMany
     */
    public function shared(): HasMany
    {
        return $this->hasMany(NoteShared::class);
    }

    /**
     * @return BelongsToMany
     */
    public function shared_users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, NoteShared::class);
    }

    /**
     * Get note attachments
     *
     * @return HasMany
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(NoteAttachment::class);
    }

    /**
     * Check note attachments existence
     *
     * @return bool
     */
    public function hasAttachments(): bool
    {
        return (bool)$this->attachments->isNotEmpty();
    }

    /**
     * Check note sharing existence
     *
     * @return bool
     */
    public function isShared(): bool
    {
        return (bool)$this->shared->isNotEmpty();
    }

    /**
     * Attach & store files
     *
     * @param array|null $files
     */
    public function attachFile(?array $files)
    {
        foreach ($files as $file) {
            $ext = $file->getClientOriginalExtension();
            $original_name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;

            // <UUIDv4>_<count>.<extension>
            $name = Str::uuid()->toString() . '.' . $ext;
            $path = $file->storeAs('note-attachments', $name, 'public');

            NoteAttachment::create([
                'note_id' => $this->id,
                'original' => $original_name,
                'name' => $name,
                'path' => $path
            ]);
        }
    }

    /**
     * Detach & delete files
     *
     * @return $this
     */
    public function detachFiles(): Note
    {
        if ($this->hasAttachments()) {
            foreach ($this->attachments as $att) {
                if (Storage::disk('public')->exists($att->path)) {
                    if (Storage::disk('public')->delete($att->path)) {
                        $att->delete();
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Terminate all shares
     *
     * @return $this
     */
    public function unshareAll(): Note
    {
        if ($this->isShared()) {
            foreach ($this->shared as $sharing) {
                $sharing->delete();
            }
        }

        return $this;
    }

    /**
     * Delete note with files and sharing
     *
     * @return bool|null
     */
    public function deleteAll(): ?bool
    {
        return $this->detachFiles()->unshareAll()->delete();
    }

}
