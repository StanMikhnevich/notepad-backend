<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect(route('notes.root', ['show' => 'all']));
})->name('index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('notes', 'NoteController')->except('show');

    Route::get('notes?show=my', 'NoteController@index')->name('notes.my');
    Route::get('notes?show=shared', 'NoteController@index')->name('notes.shared');

    Route::post('/notes/{note}/share', 'NoteController@share');
    Route::post('/notes/{note}/unshareNote', 'NoteController@unshare');
    Route::post('/notes/{note}/deleteNoteAttachment', 'NoteController@detach');

    Route::post('/api/checkUserByEmail', 'UserController@checkUserByEmail');
});

Route::get('/public/notes', 'NoteController@index')->name('notes.root');

Route::middleware(['authButNotVerified'])->group(function () {
    Route::get('/notes/{note}', 'NoteController@show');
});


require __DIR__.'/auth.php';
