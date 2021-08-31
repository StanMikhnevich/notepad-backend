<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Rfc4122\UuidV4;
use App\Events\NoteShareEvent;

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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NoteUser[] $shared
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
 * @mixin Eloquent
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

    public static function queryByAccess(Builder $builder = null)
    {
        $builder = $builder ?: Note::query();

        $builder->where(function (Builder $builder) {
            $builder->where('user_id', auth()->id());
            $builder->orWhere('private', 0);
            $builder->orWhereHas('shared', function (Builder $builder) {
                $builder->where('user_id', auth()->id());
            });
        });

        return $builder;
    }

    /**
     * @param array $filters
     * @param Builder|null $builder
     * @return Builder|Note
     */
    public static function searchQuery(array $filters = [], Builder $builder = null)
    {
        $builder = $builder ?: Note::query();

        $show = (string) ($filters['show'] ?? 'all');
        $search = (string) ($filters['search'] ?? '');

        if ($show == 'my' && $show != 'all') {
            $builder->where('user_id', auth()->id());
        }

        if ($show == 'public' && $show != 'all') {
            return $builder->where('private', 0);
        }

        if ($show == 'shared' && $show != 'all') {
            return $builder->whereHas('shared', function (Builder $builder) {
                $builder->where('user_id',  auth()->id());
            });
        }

        if ($search) {
            $builder->where('title', 'LIKE', "%$search%");
            $builder->orWhere('text', 'LIKE', "%$search%");
        }

        return $builder;

    }

    /**
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'uid';
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function getTextMdAttribute(): string
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
        return $this->hasMany(NoteUser::class);
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, NoteUser::class);
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
    public function hasShared(): bool
    {
        return (bool) $this->shared->isNotEmpty();
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
            $type = explode('/', $file->getMimeType())[0];
            $name = UuidV4::uuid4() . '.' . $ext;
            $path = $file->storeAs('note-attachments', $name, 'public');

            NoteAttachment::create([
                'note_id' => $this->id,
                'original' => $original_name,
                'name' => $name,
                'type' => $type,
                'path' => $path
            ]);
        }
    }

    /**
     * Detach & delete files
     *
     * @return $this
     */
    public function detachAllFiles(): Note
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
     * @param User $user
     * @return NoteUser
     */
    public function addUser(User $user): NoteUser
    {
        /** @var NoteUser $noteUser */
        $noteUser = $this->shared()->create(['user_id' => $user->id]);

        event(new NoteShareEvent($noteUser));

        return $noteUser;

    }

    /**
     * @param array|numeric $sharingId
     * @return bool
     */
    public function removeUser($sharingId = []): bool
    {
        $allSharing = $this->shared->whereIn('id', (array) $sharingId);

        foreach ($allSharing as $sharing) {
            $sharing->delete();
            event(new NoteShareEvent($sharing, false));
        }

        return $allSharing->count() > 0;
    }

    /**
     * Terminate all shares
     *
     * @return $this
     */
    public function removeAllSharing(): Note
    {
        if ($this->hasShared()) {
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
        return $this->detachAllFiles()->removeAllSharing()->delete();
    }

    /**
     * @param array|numeric $attachments
     */
    public function unlinkAttachment($attachments = []): bool
    {
        $attachments = $this->attachments->whereIn('id', (array) $attachments);

        foreach ($attachments as $attachment) {
            $attachment->deleteWithFile();
        }

        return $attachments->count() > 0;
    }

}
