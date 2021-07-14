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

    public function notes()
    {
        return view('notes.notes');
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


    public function getNotes(Request $request)
    {
        if(Auth::check()) {
            $notes = Note::all();
        } else {
            $notes = Note::where('private', 0)->get();
        }

        return $notes;
    }



    public function share(Request $request)
    {



        return redirect('notes');

    }

    public function create(Request $request)
    {
        $note = $request->toArray();
        unset($note['_token']);
        $note['id'] = Str::uuid()->toString();
        $note['author'] = Auth::user()->id;
        $note['private'] = isset($note['private']);

        Note::create($note);

        return redirect('notes');

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

        return redirect('notes');

    }
}
