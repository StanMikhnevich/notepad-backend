<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Redirector;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\BaseFormRequest;
use App\Http\Requests\Notes\IndexNotesRequest;
use App\Http\Requests\Notes\StoreNoteRequest;
use App\Http\Requests\Notes\UpdateNoteRequest;
use App\Http\Requests\Notes\ShareNoteRequest;
use App\Http\Requests\Notes\UnShareNoteRequest;
use App\Models\User;
use App\Models\Note;

class NoteController extends Controller
{
    /**
     * Show all|my|shared notes
     *
     * @return Application|Factory|View
     */
    public function index(IndexNotesRequest $request)
    {
        $filters = $request->only(['show', 'search']);

        return view('notes.index', [
            'filters' => $filters,
            'notes' => Note::searchQuery($filters)->latest()->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreNoteRequest $request
     * @return Application|RedirectResponse|Redirector
     * @throws AuthorizationException
     */
    public function store(StoreNoteRequest $request)
    {
        $this->authorize('store', Note::class);
        $note = $request->authUserStrict()->addNote($request->only('title', 'text', 'private'));

        if ($request->hasfile('attachment')) {
            $note->attachFile($request->file('attachment'));
        }

        return redirect(route('notes.my'));
    }

    /**
     * Display the specified resource.
     *A
     * @param Note $note
     * @return Application|Factory|View
     * @throws AuthorizationException
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
     * @return Application|Factory|View
     * @throws AuthorizationException
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
     * @return Application|RedirectResponse|Redirector
     * @throws AuthorizationException
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
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Note $note): JsonResponse
    {
        $this->authorize('delete', $note);

        $note->deleteAll();

        return response()->json(['success' => true]);
    }

    /**
     * Share note with user by email
     *
     * @param ShareNoteRequest $request
     * @param Note $note
     * @return Application|RedirectResponse|Redirector
     * @throws AuthorizationException
     */
    public function share(ShareNoteRequest $request, Note $note)
    {
        $this->authorize('share', $note);

        $note->addUser(User::findBy($request->input('email'), 'email'));

        return redirect(route('notes.show', $note->uid))
            ->with('success', trans('validation.custom.note.shared'));
    }

    /**
     * Terminate note sharing
     *
     * @param UnShareNoteRequest $request
     * @param Note $note
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function unshare(UnShareNoteRequest $request, Note $note): JsonResponse
    {
        $this->authorize('unshare', $note);

        return response()->json([
            'success' => $note->removeUser($request->input('sharing_id')),
        ]);
    }

    /**
     * Remove note attachment with file
     *
     * @param BaseFormRequest $request
     * @param Note $note
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function detachFile(BaseFormRequest $request, Note $note): JsonResponse
    {
        $this->authorize('detach', $note);

        return response()->json([
            'success' =>  $note->unlinkAttachment($request->input('attachment'))
        ]);
    }
}
