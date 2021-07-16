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

    // Страница со всеми записками
    public function allNotes()
    {
        $notes = [];

        if(Auth::check()) {

            if(!isset(Auth::user()->email_verified_at)) {
                return redirect()->route('verification.notice');
            }

            $notes = Note::with('_author')->orderBy('created_at', 'desc')->get();
        } else {
            $notes = Note::with('_author')->where('private', 0)->orderBy('created_at', 'desc')->get();
        }

        return view('notes.notes', [
            'notes' => $notes
        ]);
    }

    // Страница с записками текущего юзера
    public function myNotes()
    {
        $notes = Note::with('_author')->where('author', Auth::user()->id)->orderBy('created_at', 'desc')->get();

        return view('notes.my', [
            'notes' => $notes
        ]);
    }

    // Страница с доступными юзеру записками
    public function sharedNotes()
    {
        $share = NoteShared::where('user_id', Auth::user()->id)->with('note', 'note._author')->orderBy('created_at', 'desc')->get();

        return view('notes.shared', [
            'share' => $share
        ]);
    }

    // Страница поиска записок
    public function search(Request $request)
    {
        $notes = [];

        // Проверка наличия запроса для поиска
        if($request->has('search')) {

            // Сокрытие приватных записок от гостей
            if(Auth::check()) {
                // Аутентифицирован
                $notes = Note::where('title', 'LIKE', '%' . $request->search . '%')
                ->orWhere('text', 'LIKE', '%' . $request->search . '%')
                ->with('_author')->orderBy('created_at', 'desc')->get();
            } else {
                // Гость
                $notes = Note::where([['private', 0], ['title', 'LIKE', '%' . $request->search . '%']])
                ->orWhere([['private', 0], ['text', 'LIKE', '%' . $request->search . '%']])
                ->with('_author')->orderBy('created_at', 'desc')->get();
            }
        }

        return view('notes.search', [
            'notes' => $notes
        ]);

    }

    // Страница записки
    public function note($note)
    {
        // Получение записки с автором и файлами
        $note = Note::where('id', $note)->with('_author', 'attachments')->first();

        // Проверка на наличие записки
        if(!$note) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Note is not found']);
        }

        // Проверка на наличие доступа гостя к приватной записки
        // Сделал именно так для того, чтобы все записки были на главной странице
        if($note->private && !Auth::check()) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Need authentication']);
        }

        // Проверка на аутентификацию юзера
        if(Auth::check()) {

            // Проверка на соответствие юзера автору записки
            // Сделал для флага isAuthor, чтобы скрыть кнопки (edit, share) и формы (edit) от других юзеров
            if($note->author == Auth::user()->id) {

                // Преобразование markdown
                $note->text = Str::markdown($note->text);

                return view('notes.note', [
                    'note' => $note,
                    'isAuthor' => true,
                ]);
            }

            // Проверка на наличие доступа к приватной записке
            $canRead = NoteShared::where([['note_id', $note->id], ['user_id', Auth::user()->id]])->get()->isNotEmpty();
            if($note->private && !$canRead) {
                return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'This note is private']);
            }
        }

        // Преобразование markdown
        $note->text = Str::markdown($note->text);

        return view('notes.note', [
            'note' => $note,
            'isAuthor' => false,
        ]);
    }

    // Страница редактирования записки
    public function edit($note)
    {
        $note = Note::where('id', $note)->with('_author')->first();

        // Проверка на наличие записки
        if(!$note) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Note is not found']);
        }

        // Проверка на наличие доступа гостя к приватной записки
        if($note->private && !Auth::check()) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Need authentication']);
        }

        // Проверка на аутентификацию юзера
        if(Auth::check()) {

            // Проверка на соответствие юзера автору записки
            if($note->author != Auth::user()->id) {
                return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Only the author can edit this note']);
            }

            // Получение юзеров с доступом к записке
            $shared = NoteShared::where('note_id', $note->id)->with('user')->get();

            return view('notes.edit', [
                'note' => $note,
                'shared' => $shared
            ]);

        }

    }


    // Поделиться записью с юзером
    public function share(Request $request)
    {
        $sender = Auth::user();

        $user = User::where('email', $request->email)->get()->first();
        $note = Note::find($request->note_id);

        // Проверка на наличие юзера
        if(!$user) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'User not found']);
        }

        // Проверка на наличие доступа юзера к записке
        $alreadyShared = NoteShared::where([['note_id', $request->note_id], ['user_id', $user->id]])->get()->isNotEmpty();

        if($alreadyShared) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'This note already shared with <starong>' . $user->name . '</starong>']);
        }

        // Проверка на sharing самому себе
        if($sender->id == $user->id) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Can\'t share with yourself']);
        }

        // Проверка на sharing чужой записки
        if($sender->id != $note->author) {
            return redirect()->route('notes')->with(['notification' => true, 'type' => 'warning', 'msg' => 'Only the author can share the note']);
        }

        // Создание sharing
        NoteShared::create([
            'note_id' => $request->note_id,
            'user_id' => $user->id
        ]);

        // Отправка оповещения юзеру
        $notificationData = [
            'name' => 'Note sharing notification',
            'action' => 'Check',
            'url' => url('/note') . '/' . $note->id,
            'msg' => '<strong>' . $sender->name . '</strong> has been shared note <strong>' . $note->title . '</strong> with you.',
        ];

        Notification::send($user, new NoteShareNotification($notificationData));

        return redirect()->route('notes.my')->with(['notification' => true, 'type' => 'success', 'msg' => 'Note is shared with ' . $user->name]);

    }

    // Прекращение доступа к записке
    public function unshare(Request $request)
    {
        //Удаление sharing
        $notesharing = NoteShared::where([['note_id', $request->note_id], ['user_id', $request->user_id]])->with('note', 'note._author', 'user')->first();
        $notesharing->delete();

        // Отправка оповещения юзеру
        $notificationData = [
            'name' => 'Note sharing notification',
            'msg' => '<strong>' . $notesharing->note->_author->name . '</strong> has stopped sharing the note <strong>' . $notesharing->note->title . '</strong> with you.',
        ];

        Notification::send($notesharing->user, new NoteShareNotification($notificationData));

        return response()->json(['success' => true]);

    }

    // Создание записки
    public function create(Request $request)
    {
        // Генерация UUIDv4 для записки
        $note_id = Str::uuid()->toString();

        // Создание записки
        $note = Note::create([
            'id' => $note_id,
            'author' => Auth::user()->id,
            'title' => $request->title,
            'text' => $request->text,
            'private' => isset($request->private)
        ]);

        // Проверка на наличие attachment
        if($request->hasfile('attachment')) {

            // Сохранение каждого файла в папку и в БД
            foreach ($request->file('attachment') as $file) {
                // Сборка названия файла
                // <UNIX time>_<UUIDv4>_<Имя файла>
                $name = time() . '_' . $note_id . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('note_attachments', $name, 'public');

                // Создание записи в БД
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

    //Обновление записки
    public function update(Request $request)
    {
        // Обновление записки
        $note = Note::find($request->id);

        $note->title = $request->title;
        $note->text = $request->text;
        $note->private = isset($request->private);

        $note->save();

        // Проверка на наличие attachment
        if($request->hasfile('attachment')) {

            // Сохранение каждого файла в папку и в БД
            foreach ($request->file('attachment') as $file) {
                // Сборка названия файла
                // <UNIX time>_<UUIDv4>_<Имя файла>
                $name = time() . '_' . $request->id . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('note_attachments', $name, 'public');

                // Создание записи в БД
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

    // Удаление прикрепленного файла записки
    public function deleteNoteAttachment(Request $request)
    {
        // Получение прикрепления файла
        $attachment = NoteAttachment::find($request->file_id);

        // Проверка успешного удаления файла
        if(Storage::delete('public/' . $attachment->path)) {

            // Удаление записи из БД
            $attachment->delete();

            // Вывод сообщения
            return response()->json(['success' => true]);

        }

        // Вывод сообщения
        return response()->json(['success' => false]);

    }
}
