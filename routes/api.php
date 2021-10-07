<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/public/notes', 'Api\NoteController@index');
Route::get('/public/notes/{note}', 'Api\NoteController@show');

Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::resource('notes', 'Api\NoteController');
    Route::get('/notes/{note}/edit', 'Api\NoteController@edit');
    Route::post('/notes/{note}/share', 'Api\NoteController@share');
    Route::post('/notes/{note}/unshare', 'Api\NoteController@unshare');
    Route::post('/notes/{note}/deleteNoteAttachment', 'Api\NoteController@detach');
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/register', 'Api\UserController@register');
Route::get('/auth/email/verify/{id}', 'Api\UserController@verify');
Route::post('/auth/email/resend', 'Api\UserController@resend');
Route::post('/auth/login', 'Api\UserController@login');
Route::post('/auth/password/reset-send', 'Api\UserController@sendResetLink');

