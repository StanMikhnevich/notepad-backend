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

Route::get('/', 'NoteController@index')->name('notes');

Route::get('notes/?show=public', 'NoteController@index')->name('notes.public');

Route::resource('notes', 'NoteController');
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('notes?show=my', 'NoteController@index')->name('notes.my');
    Route::get('notes?show=shared', 'NoteController@index')->name('notes.shared');

    Route::post('/notes/share', 'NoteController@share')->name('notes.share');

    Route::post('/api/checkUserByEmail', 'UserController@checkUserByEmail');

    Route::post('/api/unshareNote', 'NoteController@unshare');
    Route::post('/api/deleteNote', 'NoteController@destroy');
    Route::post('/api/deleteNoteAttachment', 'NoteController@deleteNoteAttachment');
});

require __DIR__.'/auth.php';
