<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\NoteResource;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\BaseFormRequest;
use App\Http\Requests\Api\Notes\IndexNotesRequest;
use App\Http\Requests\Api\Notes\ShareNoteRequest;
use App\Http\Requests\Notes\StoreNoteRequest;
use App\Http\Requests\Notes\UpdateNoteRequest;
use App\Http\Requests\Notes\UnShareNoteRequest;
use App\Models\User;
use App\Models\Note;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param IndexNotesRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(IndexNotesRequest $request): AnonymousResourceCollection
    {
        return NoteResource::collection(
            Note::searchQuery($request->only('show'))->latest()->paginate($request->input('per_page'))
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreNoteRequest $request
     * @return NoteResource
     * @throws AuthorizationException
     */
    public function store(StoreNoteRequest $request): NoteResource
    {
        $this->authorize('store', Note::class);

        $note = $request->user()->addNote($request->only('title', 'text', 'private'));

        if ($request->hasfile('attachment')) {
            $note->attachFile($request->file('attachment'));
        }

        return new NoteResource($note);

    }

    /**
     * Display the specified resource.
     *
     * @param Note $note
     * @return NoteResource
     * @throws AuthorizationException
     */
    public function show(Note $note): NoteResource
    {
//        $note = Note::where('uid', $note)->first();
        $this->authorize('view', $note);

        return new NoteResource($note);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateNoteRequest $request
     * @param Note $note
     * @return NoteResource
     * @throws AuthorizationException
     */
    public function update(UpdateNoteRequest $request, Note $note): NoteResource
    {
//        dd($note);
        $this->authorize('update', $note);
        $note->update($request->only(['title', 'text', 'private']));

        if ($request->hasfile('attachment')) {
            $note->attachFile($request->file('attachment'));
        }

        return new NoteResource($note);
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

        return response()->json();
    }

    /**
     * @param ShareNoteRequest $request
     * @param Note $note
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function share(ShareNoteRequest $request, Note $note): JsonResponse
    {
        $this->authorize('share', $note);

        $user = User::findBy($request->input('email'), 'email');

        $note->addUser($user);

        return response()->json();
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
            'success' => $note->unlinkAttachment($request->input('attachment'))
        ]);
    }
}
