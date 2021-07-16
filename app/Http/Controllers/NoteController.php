<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use Notification;

use App\Notifications\NoteShareNotification;

use App\Models\User;
use App\Models\Note;
use App\Models\NoteShared;
use App\Models\NoteAttachment;

class NoteController extends Controller
{

    public function allNotes()
    {
        return view('notes.notes');
    }

    public function myNotes()
    {
        return view('notes.my');
    }

    public function sharedNotes()
    {
        return view('notes.shared');
    }


    public function search(Request $request)
    {
        $notes = [];

        if($request->has('search')) {
            if(Auth::check()) {
                $notes = Note::where('title', 'LIKE', '%' . $request->search . '%')
                ->orWhere('text', 'LIKE', '%' . $request->search . '%')
                ->with('_author')->get();
            } else {
                $notes = Note::where([['private', 0], ['title', 'LIKE', '%' . $request->search . '%']])
                ->orWhere([['private', 0], ['text', 'LIKE', '%' . $request->search . '%']])
                ->with('_author')->get();
            }
        }

        return view('notes.search', [
            'notes' => $notes
        ]);

    }

    public function note($note)
    {
        $note = Note::where('id', $note)->with('_author', 'attachments')->first();

        if(!$note) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Note is not found']);
        }

        if($note->private && !Auth::check()) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Need authentication']);
        }

        if(Auth::check()) {
            if($note->author == Auth::user()->id) {

                $note->text = Str::markdown($note->text);

                return view('notes.note', [
                    'note' => $note,
                    'isAuthor' => true,
                ]);
            }

            $canRead = NoteShared::where([['note_id', $note->id], ['user_id', Auth::user()->id]])->get()->isNotEmpty();

            if($note->private && !$canRead) {
                return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'This note is private']);
            }
        }

        $note->text = Str::markdown($note->text);

        return view('notes.note', [
            'note' => $note,
            'isAuthor' => false,
        ]);
    }

    public function edit($note)
    {
        $note = Note::where('id', $note)->with('_author')->first();

        if(!$note) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Note is not found']);
        }

        if($note->private && !Auth::check()) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Need authentication']);
        }

        if(Auth::check()) {
            if($note->author != Auth::user()->id) {
                return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Only the author can edit the note']);
            }

            $shared = NoteShared::where('note_id', $note->id)->with('user')->get();

            return view('notes.edit', [
                'note' => $note,
                'shared' => $shared
            ]);

        }

    }


    public function getAllNotes(Request $request)
    {
        if(Auth::check()) {
            $notes = Note::with('_author')->get();
        } else {
            $notes = Note::with('_author')->where('private', 0)->get();
        }

        return $notes;
    }

    public function getMyNotes(Request $request)
    {
        $notes = Note::with('_author')->where('author', Auth::user()->id)->get();

        return $notes;
    }

    public function getSharedNotes(Request $request)
    {
        $shared = NoteShared::where('user_id', Auth::user()->id)->with('note', 'note._author')->get();

        return $shared;
    }



    public function share(Request $request)
    {
        $sender = Auth::user();

        $user = User::where('email', $request->email)->get()->first();
        $note = Note::find($request->note_id);

        $alreadyShared = NoteShared::where([['note_id', $request->note_id], ['user_id', $user->id]])->get()->isNotEmpty();

        if($alreadyShared) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'This note already shared with <starong>' . $user->name . '</starong>']);
        }

        if(!$user) {
            return redirect('/my')->with(['notification' => true, 'type' => 'warning', 'msg' => 'User not found']);
        }

        if($sender->id == $user->id) {
            return redirect('/my')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Can\'t share with yourself']);
        }

        if($sender->id != $note->author) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Only the author can share the note']);
        }


        NoteShared::create([
            'note_id' => $request->note_id,
            'user_id' => $user->id
        ]);

        $notificationData = [
            'name' => 'Note sharing notification',
            'action' => 'Check',
            'url' => url('/note') . '/' . $note->id,
            'msg' => '<strong>' . $sender->name . '</strong> has been shared note <strong>' . $note->title . '</strong> with you.',
        ];

        Notification::send($user, new NoteShareNotification($notificationData));

        return redirect('/my')->with(['notification' => true, 'type' => 'success', 'msg' => 'Note is shared with ' . $user->name]);

    }

    public function unshare(Request $request)
    {
        $notesharing = NoteShared::where([['note_id', $request->note_id], ['user_id', $request->user_id]])->with('note', 'note._author', 'user')->first();

        $notificationData = [
            'name' => 'Note sharing notification',
            'msg' => '<strong>' . $notesharing->note->_author->name . '</strong> has stopped sharing the note <strong>' . $notesharing->note->title . '</strong> with you.',
        ];

        $notesharing->delete();

        Notification::send($notesharing->user, new NoteShareNotification($notificationData));

        return response()->json(['success' => true]);

    }

    public function create(Request $request)
    {
        // dd($request->attachment);
        $note_id = Str::uuid()->toString();

        $note = Note::create([
            'id' => $note_id,
            'author' => Auth::user()->id,
            'title' => $request->title,
            'text' => $request->text,
            'private' => isset($request->private)
        ]);

        if($request->hasfile('attachment')) {

            foreach ($request->file('attachment') as $file) {
                $name = time() . '_' . $note_id . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('note_attachments', $name, 'public');

                NoteAttachment::create([
                    'note_id' => $note_id,
                    '_name' => $file->getClientOriginalName(),
                    'name' => $name,
                    'path' => $path
                ]);

            }

        }

        return redirect()->route('notes.my');

    }

    public function update(Request $request)
    {
        $note = Note::find($request->id);

        $note->title = $request->title;
        $note->text = $request->text;
        $note->private = isset($request->private);

        $note->save();

        if($request->hasfile('attachment')) {

            foreach ($request->file('attachment') as $file) {
                $name = time() . '_' . $request->id . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('note_attachments', $name, 'public');

                NoteAttachment::create([
                    'note_id' => $request->id,
                    '_name' => $file->getClientOriginalName(),
                    'name' => $name,
                    'path' => $path
                ]);

            }

        }


        return redirect(route('notes') . '/note/' . $note->id)->with(['notification' => true, 'type' => 'success', 'msg' => 'Note has been updated']);

    }

    public function deleteNoteAttachment(Request $request)
    {
        $attachment = NoteAttachment::find($request->file_id);

        if(Storage::delete('public/' . $attachment->path)) {

            $attachment->delete();
            return response()->json(['success' => true]);

        }

        return response()->json(['success' => false]);

    }
}
