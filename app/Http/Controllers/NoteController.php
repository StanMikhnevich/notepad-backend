<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\User;
use App\Models\Note;

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

    public function UserNotes()
    {
        return view('notes.user');
    }


    public function note($note)
    {
        $note = Note::find($note);

        if(!$note) {
            return redirect('/');
        }

        if($note->private && !Auth::check()) {
            return redirect('/');
        }

        return view('notes.note', $note);
    }



    public function getAllNotes(Request $request)
    {
        if(Auth::check()) {
            $notes = Note::with('author')->get();
        } else {
            $notes = Note::with('author')->where('private', 0)->get();
        }

        return $notes;
    }

    public function getMyNotes(Request $request)
    {
        $notes = Note::with('author')->where([['private', 0], ['author', Auth::user()->id]])->get();

        return $notes;
    }



    public function share(Request $request)
    {



        return redirect('/my');

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
