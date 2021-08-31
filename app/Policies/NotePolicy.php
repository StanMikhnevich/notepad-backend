<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class NotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User|null $user
     * @return bool
     */
    public function viewAny(?User $user): bool
    {
        return (bool) $user;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User|null $user
     * @param Note $note
     * @return bool
     */
    public function view(?User $user, Note $note): bool
    {
        return ($this->viewAny($user) && !$user->hasVerifiedEmail())
            || Note::queryByAccess()->where('id', $note->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return bool
     */
    public function store(User $user): bool
    {
        return (bool) $user;
    }

    /**
     * Determine whether the user can edit the model.
     *
     * @param User $user
     * @param Note $note
     * @return Response|bool
     */
    public function edit(User $user, Note $note): bool
    {
        return (bool) $note->user_id == $user->id;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Note $note
     * @return Response|bool
     */
    public function update(User $user, Note $note): bool
    {
        return (bool) $note->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Note $note
     * @return Response|bool
     */
    public function delete(User $user, Note $note): bool
    {
        return (bool) $note->user_id == $user->id;
    }

    /**
     * Determine whether the user can share the model.
     *
     * @param User $user
     * @param Note $note
     * @return Response|bool
     */
    public function share(User $user, Note $note): bool
    {
        return (bool) $note->user_id == $user->id;
    }

    /**
     * Determine whether the user can unshare the model.
     *
     * @param User $user
     * @param Note $note
     * @return Response|bool
     */
    public function unshare(User $user, Note $note): bool
    {
        return (bool) $note->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete attachments of the model.
     *
     * @param User $user
     * @param Note $note
     * @return Response|bool
     */
    public function detach(User $user, Note $note): bool
    {
        return (bool) $note->user_id == $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Note $note
     * @return Response|bool
     */
    public function forceDelete(User $user, Note $note): bool
    {
        return (bool) $note->user_id == $user->id;
    }
}
