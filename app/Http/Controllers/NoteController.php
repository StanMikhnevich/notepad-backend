<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

use Notification;

use App\Notifications\NoteShareNotification;

use App\Models\User;
use App\Models\Note;
use App\Models\NoteShared;

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


    public function note($note)
    {
        $note = Note::where('id', $note)->with('_author')->first();

        if(!$note) {
            return redirect('/')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Note is not found']);
        }

        if($note->private && !Auth::check()) {
            return redirect('/')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Need authentication']);
        }

        if(Auth::check()) {
            if($note->author == Auth::user()->id) {
                return view('notes.note', [
                    'note' => $note,
                    'isAuthor' => true,
                ]);
            }

            $canRead = NoteShared::where([['note_id', $note->id], ['user_id', Auth::user()->id]])->get()->isNotEmpty();

            if(!$canRead) {
                return redirect('/')->with(['notification' => true, 'type' => 'warning', 'msg' => 'This note is private']);
            }
        }


        return view('notes.note', [
            'note' => $note,
            'isAuthor' => false,
        ]);
    }

    public function edit($note)
    {
        // code...
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
        $shared = NoteShared::where('user_id', Auth::user()->id)->with('note', 'note.author')->get();

        return $shared;
    }



    public function share(Request $request)
    {
        $sender = Auth::user();

        $user = User::where('email', $request->email)->get()->first();
        $note = Note::find($request->note_id);

        if(!$user) {
            return redirect('/my')->with(['notification' => true, 'type' => 'warning', 'msg' => 'User not found']);
        }

        if($sender->id == $user->id) {
            return redirect('/my')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Can\'t share with yourself']);
        }

        if($sender->id != $note->author) {
            return redirect('/')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Only the author can share the note']);
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

    public function create(Request $request)
    {
        $note = $request->toArray();
        unset($note['_token']);
        $note['id'] = Str::uuid()->toString();
        $note['author'] = Auth::user()->id;
        $note['private'] = isset($note['private']);

        Note::create($note);

        return redirect('/my');

    }

    public function update(Request $request)
    {
        // $note = $request->toArray();
        // unset($note['_token']);
        // $note['id'] = Str::uuid()->toString();
        // $note['author'] = Auth::user()->id;
        // $note['private'] = isset($note['private']);
        //
        // Note::create($note);

        return redirect('/my');

    }
}
