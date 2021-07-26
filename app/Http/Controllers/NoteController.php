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

use App\Notifications\NoteShareNotification;

use App\Models\User;
use App\Models\Note;
use App\Models\NoteShared;
use App\Models\NoteAttachment;

class NoteController extends Controller
{

    protected $messages = [
        'user.empty' => [
            'notification' => true,
            'type' => 'warning',
            'msg' => 'User is not found',
        ],
        'note.empty' => [
            'notification' => true,
            'type' => 'warning',
            'msg' => 'Note is not found',
        ],
        'note.available' => [
            'notification' => true,
            'type' => 'warning',
            'msg' => 'Need authentication',
        ],
        'note.private' => [
            'notification' => true,
            'type' => 'warning',
            'msg' => 'This note is private',
        ],
        'note.owned' => [
            'notification' => true,
            'type' => 'warning',
            'msg' => 'Only the author can edit this note',
        ],
        'note.updated' => [
            'notification' => true,
            'type' => 'success',
            'msg' => 'Note has been updated',
        ],
        'note.deleted' => [
            'success' => true,
            'notification' => true,
            'type' => 'success',
            'msg' => 'Note has been deleted',
        ],
        'note.share.exists' => [
            'notification' => true,
            'type' => 'warning',
            'msg' => 'This note is already shared with this user',
        ],
        'note.share.self' => [
            'notification' => true,
            'type' => 'warning',
            'msg' => 'Can\'t share with yourself',
        ],
        'note.share.done' => [
            'notification' => true,
            'type' => 'success',
            'msg' => 'Note was shared with user',
        ],
    ];

    /**
     * Show all|my|shared notes
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|View|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function index(BaseFormRequest $request)
    {
        $notes = Note::query()->with('user')->latest();
        $filters = $request->only('show');

        // Show shared notes
        if (($filters['show'] ?? '') == 'shared') {
            $notes = $notes->whereHas('shared', function (Builder $builder) use ($request) {
                $builder->where('user_id', $request->authUserStrict()->id);
            });
        }

        // Show my notes
        if (($filters['show'] ?? '') == 'my') {
            $notes = $request->authUserStrict()->notes();
        }

        // Show all notes
        if (($filters['show'] ?? '') == 'public') {
            if(!$request->isAuthenticated()) {
                $notes = $notes->where('private', 0);
            } else {
                // Checking users email verification
                if (!$request->authUserStrict()->hasVerifiedEmail()) {
                    return redirect(route('verification.notice'));
                }
            }
        }

        //
        if (($filters['show'] ?? '') == '') {
            if($request->isAuthenticated()) {
                return redirect(route('notes.public'));
            }

            $notes = $notes->where('private', 2);
        }

        return view('notes.index', [
            'filters' => $filters,
            'notes' => $notes->orderBy('created_at', 'desc')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     */
    public function create()
    {
        abort(404);
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

        $note = $request->authUser()->notes()->create([
            'uid' => Str::uuid()->toString(),
            'title' => $request->input('title', 'Untitled note'),
            'text' => $request->input('text', ''),
            'private' => $request->input('private', 0)
        ]);

        // Checking attached files
        if ($request->hasfile('attachment')) {
            $note->attachFile($request->file('attachment'));
        }

        return redirect(route('notes.my'));
    }

    /**
     * Display the specified resource.
     *
     * @param BaseFormRequest $request
     * @param Note $note
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|View|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function show(BaseFormRequest $request, Note $note)
    {
        $isOwner = false;

        // Checking note existence
        if (!$note) {
            return redirect(route('notes.public'))->with($this->messages['note.empty']);
        }

        // Checking private note accessibility for guest user
        if ($note->private && !$request->isAuthenticated()) {
            return redirect(route('notes.public'))->with($this->messages['note.available']);
        }

        // Checking authenticated user
        if ($request->isAuthenticated()) {
            // Checking note ownership
            // isOwner flag hides edit & share buttons, modAls and note sharing from other users
            $isOwner = ($note->user_id == $request->authUserStrict()->id);

            // Checking access to private shared note
            $canRead = NoteShared::where([['note_id', $note->id], ['user_id', $request->authUserStrict()->id]])->get()->isNotEmpty();
            if (!$isOwner && $note->private && !$canRead) {
                return redirect(route('notes.public'))->with($this->messages['note.private']);
            }
        }

        // Getting users with access to note
        $shared = ($isOwner) ? NoteShared::where('note_id', $note->id)->with('user')->get() : [];

        // Markdown translate
        $note->text = Str::markdown($note->text);

        return view('notes.note', [
            'note' => $note,
            'shared' => $shared,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Edit note
     *
     * @param BaseFormRequest $request
     * @param Note $note
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|View|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(BaseFormRequest $request, Note $note)
    {
        $this->authorize('edit', $note);

        $shared = [];

        // Checking note existence
        if (!$note) {
            return redirect(route('notes.index'))->with($this->messages['note.empty']);
        }

        // Checking private note accessibility for guest user
        if ($note->private && !$request->isAuthenticated()) {
            return redirect(route('notes.index'))->with($this->messages['note.available']);
        }

        // Checking authenticated user
        if ($request->isAuthenticated()) {

            // Getting users with access to note
            $shared = NoteShared::where('note_id', $note->id)->with('user')->get();
        }

        return view('notes.edit', [
            'note' => $note,
            'shared' => $shared
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

        $note->title = $request->input('title', 'Untitled note');
        $note->text = $request->input('text', '');
        $note->private = $request->input('private', 0);

        $note->save();

        // Checking attached files
        if ($request->hasfile('attachment')) {
            $note->attachFile($request->file('attachment'));
        }

        return redirect(route('notes.show', $note->uid))->with($this->messages['note.updated']);
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
        $note = Note::find($request->input('id'));

        // Checking target note existence
        if (!$note) {
            return response()->json($this->messages['note.empty']);
        }

        $this->authorize('delete', [$note]);

        // Delete note attachments
        if ($note->hasAttachments()) {
            foreach ($note->attachments as $att) {
                if(Storage::disk('public')->exists($att->path)) {
                    if (Storage::disk('public')->delete($att->path)) {
                        $att->delete();
                    }
                }
            }
        }

        // Delete note sharing
        if ($note->isShared()) {
            foreach ($note->shared as $sharing) {
                $sharing->delete();
            }
        }

        $note->delete();

        return response()->json($this->messages['note.deleted']);
    }



    /**
     * Search note
     *
     * @param BaseFormRequest $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|View
     */
    public function search(BaseFormRequest $request)
    {
        $notes = [];

        // Checking search query existence
        if ($request->has('search')) {
            $notes = Note::query()->with('user')->latest();
            $search = $request->input('search');
            $conditions[] = ['title', 'LIKE', '%' . $search . '%'];
//            $conditions[] = ['private', 0];
//            $conditions[] = ['user_id', $request->authUserStrict()->id];

            // Checking user auth
//            if (!$request->isAuthenticated()) {
//            }

            // Getting notes
            $notes = $notes->where($conditions);
            $conditions[0][0] = 'text';
            $notes = $notes->orWhere($conditions);
        }

        return view('notes.search', [
            'notes' => $notes->with('user')->orderBy('created_at', 'desc')->get()
        ]);

    }

    /**
     * Share note with user by email
     *
     * @param BaseFormRequest $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function share(BaseFormRequest $request)
    {
        $note = Note::find($request->input('note_id'));

        $this->authorize('share', $note);

        // Getting sender and target user
        $sender = $request->authUserStrict();
        $user = User::where('email', $request->input('email'))->first();

        // Checking target user existence
        if (!$user) {
            return redirect(route('notes.show', $note->uid))->with($this->messages['user.empty']);
        }

        // Checking user access existence
        $alreadyShared = NoteShared::where([
            ['note_id', $request->input('note_id')],
            ['user_id', $user->id],
        ])->get()->isNotEmpty();

        if ($alreadyShared) {
            return redirect(route('notes.show', $note->uid))->with($this->messages['note.share.exists']);
        }

        // Checking sender is not a target user
        if ($sender->id == $user->id) {
            return redirect(route('notes.show', $note->uid))->with($this->messages['note.share.self']);
        }

        // Creating of sharing
        NoteShared::create([
            'note_id' => $request->note_id,
            'user_id' => $user->id
        ]);

        // Notification user via email
        Notification::send($user, new NoteShareNotification([
            'name' => 'Note sharing notification',
            'action' => 'Check',
            'url' => url('/note') . '/' . $note->id,
            'msg' => '<strong>' . $sender->name . '</strong> has been shared note <strong>' . $note->title . '</strong> with you.',
        ]));

        return redirect(route('notes.show', $note->uid))->with($this->messages['note.share.done']);
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

        // Remove sharing note
        $sharing = NoteShared::where([
            ['note_id', $request->input('note_id')],
            ['user_id', $request->input('user_id')]
        ])->with('note', 'user')->first();

        $this->authorize('unshare', $sharing->note);

        $sharing->delete();

        // Notification user via email
        Notification::send($sharing->user, new NoteShareNotification([
            'name' => 'Note sharing notification',
            'msg' => '<strong>' . $request->authUserStrict()->name . '</strong> has stopped sharing the note <strong>' . $sharing->note->title . '</strong> with you.',
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
        // Get note attachment
        $attachment = NoteAttachment::find($request->file_id);

        $this->authorize('delete_attachment', $attachment->note);

        // Checking file successfully deleted
        if (Storage::disk('public')->delete($attachment->path)) {
            $attachment->delete();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

}
