<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\NoteResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\BaseFormRequest;
use App\Http\Requests\Notes\StoreNoteRequest;
use App\Http\Requests\Notes\UpdateNoteRequest;
use App\Http\Requests\Notes\ShareNoteRequest;
use App\Http\Requests\Notes\UnShareNoteRequest;
use App\Models\User;
use App\Models\Note;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return NoteResource::collection(
            Note::searchQuery($request->only('show'))->latest()->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreNoteRequest $request
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(StoreNoteRequest $request): JsonResponse
    {
        $this->authorize('store', Note::class);

        $note = $request->user()->addNote($request->only('title', 'text', 'private'));

        if ($request->hasfile('attachment')) {
            $note->attachFile($request->file('attachment'));
        }

        return response()->json(['success' => $note->exists()]);

    }

    /**
     * Display the specified resource.
     *
     * @param Note $note
     * @return NoteResource
     */
    public function show(Note $note): NoteResource
    {
        return new NoteResource($note);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateNoteRequest $request
     * @param Note $note
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateNoteRequest $request, Note $note): JsonResponse
    {
        $this->authorize('update', $note);
        $note->update($request->only(['title', 'text', 'private']));

        if ($request->hasfile('attachment')) {
            $note->attachFile($request->file('attachment'));
        }

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Note $note
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Note $note): JsonResponse
    {
        $this->authorize('delete', $note);

        $note->deleteAll();

        return response()->json(['success' => true]);
    }

    /**
     * @param ShareNoteRequest $request
     * @param Note $note
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function share(ShareNoteRequest $request, Note $note): JsonResponse
    {
        $this->authorize('share', $note);

        $user = User::findBy($request->input('email'), 'email');

        $note->addUser($user);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Terminate note sharing
     *
     * @param UnShareNoteRequest $request
     * @param Note $note
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
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
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function detachFile(BaseFormRequest $request, Note $note): JsonResponse
    {
        $this->authorize('detach', $note);

        return response()->json([
            'success' =>  $note->unlinkAttachment($request->input('attachment'))
        ]);
    }

}
