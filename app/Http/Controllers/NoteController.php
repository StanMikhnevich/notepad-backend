<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

use App\Http\Requests\BaseFormRequest;

use App\Http\Requests\Notes\StoreNoteRequest;
use App\Http\Requests\Notes\UpdateNoteRequest;
use App\Http\Requests\Notes\EditNoteRequest;
use App\Http\Requests\Notes\ShareNoteRequest;

use App\Notifications\NoteShareNotification;

use App\Models\User;
use App\Models\Note;
use App\Models\NoteShared;
use App\Models\NoteAttachment;

class NoteController extends Controller
{
    /**
     * Show all|my|shared notes
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|View|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function index(BaseFormRequest $request)
    {
        $query = Note::with('user')->latest();
        $filters = $request->only(['show', 'search']);

        if (($filters['show'] ?? '') == 'shared') {
            $query->whereHas('shared', function (Builder $builder) use ($request) {
                $builder->where('user_id', $request->authUserStrict()->id);
            });
        }

        if (($filters['show'] ?? '') == 'my') {
            $query = $request->authUserStrict()->notes();
        }

        if (($filters['show'] ?? '') == 'public') {
            if (!$request->isAuthenticated()) {
                $query->where('private', 0);
            } elseif (!$request->authUserStrict()->hasVerifiedEmail()) {
                return redirect(route('verification.notice'));
            }
        }

        if (($filters['show'] ?? '') == '' && !($filters['search'] ?? '')) {
            if ($request->isAuthenticated()) {
                return redirect(route('notes.public'));
            }

            $query = $query->where('private', 2);
        }

        if ($filters['search'] ?? '') {
            $query->where('title', 'LIKE', '%' . $filters['search'] . '%');
            $query->orWhere('text', 'LIKE', '%' . $filters['search'] . '%');
        }

        return view('notes.index', [
            'filters' => $filters,
            'notes' => $query->orderBy('created_at', 'desc')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreNoteRequest $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(StoreNoteRequest $request)
    {
        $this->authorize('store', Note::class);

        /** @var Note $note */
        $note = $request->authUser()->notes()->create([
            'uid' => Str::uuid()->toString(),
            'title' => $request->input('title', 'Untitled note'),
            'text' => $request->input('text', ''),
            'private' => $request->input('private', 0)
        ]);

        if ($request->hasfile('attachment')) {
            $note->attachFile($request->file('attachment'));
        }

        return redirect(route('notes.my'));
    }

    /**
     * Display the specified resource.
     *A
     * @param BaseFormRequest $request
     * @param Note $note
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|View|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(BaseFormRequest $request, Note $note)
    {
        $this->authorize('view', [$note]);
        $isOwner = false;

        if ($request->isAuthenticated()) {
            //TODO изменить
            $isOwner = ($note->user_id == $request->authUserStrict()->id);

            // Checking access to private shared note
            $canRead = NoteShared::where([
                ['note_id', $note->id],
                ['user_id', $request->authUserStrict()->id]
            ])->doesntExist();

            if (!$isOwner && $note->private && !$canRead) {
                return redirect(route('notes.public'))
                    ->with('warning', trans('validation.custom.note.is_private'));
            }
        }

        // Getting users with access to note
        $shared = ($isOwner) ? NoteShared::where('note_id', $note->id)->with('user')->get() : [];

        $note->text = Str::markdown($note->text);

        return view('notes.note', [
            'note' => $note,
            'shared' => $shared,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * @param EditNoteRequest $request
     * @param Note $note
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(EditNoteRequest $request, Note $note)
    {
        $this->authorize('edit', $note);

        return view('notes.edit', [
            'note' => $note->load('shared.user'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateNoteRequest $request
     * @param Note $note
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateNoteRequest $request, Note $note)
    {
        $this->authorize('update', $note);
        $note->update($request->only(['title', 'text', 'private']));

        if ($request->hasfile('attachment')) {
            $note->attachFile($request->file('attachment'));
        }

        return redirect(route('notes.show', $note->uid))
            ->with('success', trans('validation.custom.note.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param BaseFormRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(BaseFormRequest $request): JsonResponse
    {
        //TODO 123asrasfr
        $note = Note::find($request->input('id'));
        $this->authorize('delete', $note);

        $note->deleteAll();

        return response()->json([
            'success' => true,
            'msg' => trans('validation.custom.note.deleted'),
        ]);
    }

    /**
     * Search note
     *
     * @param BaseFormRequest $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|View
     */
    public function search(BaseFormRequest $request)
    {
        $query = Note::with('user')->latest();
        $search = $request->input('search');

        if ($search) {
            $query->where('title', 'LIKE', '%' . $search . '%');
            $query->orWhere('text', 'LIKE', '%' . $search . '%');
        }

        return view('notes.search', [
            'notes' => $query->with('user')->orderBy('created_at', 'desc')->get()
        ]);

    }

    /**
     * Share note with user by email
     *
     * @param ShareNoteRequest $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function share(ShareNoteRequest $request)
    {
        $note = Note::find($request->input('note_id'));

        $this->authorize('share', $note);

        $sender = $request->authUserStrict();
        $user = User::where('email', $request->input('email'))->first();

        $user->shared_notes()->attach($note);

        Notification::send($user, new NoteShareNotification([
            'name' => 'Note sharing notification',
            'action' => 'Check',
            'url' => url('/note') . '/' . $note->id,
            'username' => $sender->name,
            'title' => $note->title,
            'msg' => '<strong>' . e($sender->name) . '</strong> has been shared note <strong>' . e($note->title) . '</strong> with you.',
        ]));

        return redirect(route('notes.show', $note->uid))
            ->with('success', trans('validation.custom.note.shared'));
    }

    /**
     * Terminate note sharing
     *
     * @param BaseFormRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unshare(BaseFormRequest $request): JsonResponse
    {
        $note = Note::find($request->input('note_id'));

        $this->authorize('unshare', $note);

        $sharing = $note->shared()->with('user')->where('user_id', $request->input('user_id'))->first();
        $sharing->delete();

        Notification::send($sharing->user, new NoteShareNotification([
            'name' => 'Note sharing notification',
            'msg' => '<strong>' . $request->authUserStrict()->name . '</strong> has stopped sharing the note <strong>' . $note->title . '</strong> with you.',
        ]));

        return response()->json(['success' => true]);
    }

    /**
     * Remove note attachment with file
     *
     * @param BaseFormRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteNoteAttachment(BaseFormRequest $request): JsonResponse
    {
       $attachment = NoteAttachment::find($request->file_id);

        $this->authorize('delete_attachment', $attachment->note);

        if (Storage::disk('public')->delete($attachment->path)) {
            $attachment->delete();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }
}
