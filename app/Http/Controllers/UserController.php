<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

class UserController extends Controller
{

    // Проверка наличия юзера по email
    public function checkUserByEmail(Request $req)
    {
        $user = User::where('email', $req->email)->get()->first();

        if (!$user) {
            return response()->json(['success' => false, 'msg' => 'Пользователь не найден']);
        }

        if (!$user->email_verified_at) {
            return response()->json(['success' => false, 'msg' => 'Пользователь не верифицирован']);
        }

        return response()->json(['success' => true, 'user' => $user]);
    }
}
