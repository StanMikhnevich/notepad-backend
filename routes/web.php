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

Route::get('/', 'NoteController@allNotes')->name('notes');
Route::get('/note/{note}', 'NoteController@note');


Route::post('/api/getAllNotes', 'NoteController@getAllNotes');
Route::post('/api/getMyNotes', 'NoteController@getMyNotes');
Route::post('/api/getSharedNotes', 'NoteController@getSharedNotes');
Route::post('/api/checkUserByEmail', 'UserController@checkUserByEmail');



Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/note/{note}/edit', 'NoteController@edit');

    Route::get('/my', 'NoteController@myNotes')->name('notes.my');
    Route::get('/shared', 'NoteController@sharedNotes')->name('notes.shared');

    Route::post('/notes/share', 'NoteController@share')->name('notes.share');
    Route::post('/notes/create', 'NoteController@create')->name('notes.create');
    Route::post('/notes/update', 'NoteController@update')->name('notes.update');
    Route::post('/notes/delete', 'NoteController@delete')->name('notes.delete');

});

require __DIR__.'/auth.php';
