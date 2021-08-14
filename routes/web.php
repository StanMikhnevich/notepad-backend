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

Route::get('/', 'NoteController@index')->name('index');
Route::get('notes/?show=all', 'NoteController@index')->name('notes.all');
Route::get('notes/?show=public', 'NoteController@index')->name('notes.public');

Route::middleware(['notVerified'])->group(function () {
    Route::get('/notes/{note}', 'NoteController@show');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('notes', 'NoteController')->except('show');

    Route::get('notes?show=my', 'NoteController@index')->name('notes.my');
    Route::get('notes?show=shared', 'NoteController@index')->name('notes.shared');

    Route::post('/notes/{note}/share', 'NoteController@share');
    Route::post('/notes/{note}/unshareNote', 'NoteController@unshare');
    Route::post('/notes/{note}/deleteNoteAttachment', 'NoteController@detach');

    Route::post('/api/checkUserByEmail', 'UserController@checkUserByEmail');
});

require __DIR__.'/auth.php';
