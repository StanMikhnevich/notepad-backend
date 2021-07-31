<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

use App\Http\Requests\BaseFormRequest;

use App\Http\Requests\Notes\StoreNoteRequest;
use App\Http\Requests\Notes\UpdateNoteRequest;
use App\Http\Requests\Notes\ShareNoteRequest;

use App\Models\User;
use App\Models\Note;

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
     * @param Note $note
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Note $note)
    {
        $this->authorize('view', $note);

        return view('notes.note', [
            'note' => $note,
        ]);
    }

    /**
     * @param Note $note
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Note $note)
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
     * @param Note $note
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Note $note): JsonResponse
    {
        $this->authorize('delete', $note);

        $note->deleteAll();

        return response()->json(['success' => true]);
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
     * @param Note $note
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function share(ShareNoteRequest $request, Note $note)
    {
        $this->authorize('share', $note);

        $user = User::whereEmail($request->input('email'))->first();
        $user->shared_notes()->attach($note);
        $user->notifyAboutNoteSharing($note, $request->authUserStrict());

        return redirect(route('notes.show', $note->uid))
            ->with('success', trans('validation.custom.note.shared'));
    }

    /**
     * Terminate note sharing
     *
     * @param BaseFormRequest $request
     * @param Note $note
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unshare(BaseFormRequest $request, Note $note): JsonResponse
    {
        $this->authorize('unshare', $note);

        $sharing = $note->shared()->with('user')->find($request->only('sharing_id'))->first();
        $sharing->delete();
        $sharing->user->notifyAboutNoteUnsharing($note, $request->authUserStrict());

        return response()->json(['success' => true]);
    }

    /**
     * Remove note attachment with file
     *
     * @param BaseFormRequest $request
     * @param Note $note
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function detachFile(BaseFormRequest $request, Note $note): JsonResponse
    {
        $this->authorize('detach', $note);

        /** @var  $success */
        $success = $note->attachments
            ->find($request->only('attachment'))->first()
            ->deleteWithFile();

        return response()->json(['success' => $success]);
    }
}
