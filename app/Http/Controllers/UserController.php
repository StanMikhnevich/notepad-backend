<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\BaseFormRequest;

use App\Models\User;

class UserController extends Controller
{

    /**
     * @param BaseFormRequest $request
     * @return JsonResponse
     */
    public function checkUserByEmail(BaseFormRequest $request): JsonResponse
    {
//        $this->authorize('check', $user);

        $user = User::findBy($request->input('email'), 'email');

        if (!$user) {
            return response()->json(['success' => false, 'msg' => 'Пользователь не найден']);
        }

        if (!$user->email_verified_at) {
            return response()->json(['success' => false, 'msg' => 'Пользователь не верифицирован']);
        }

        return response()->json(['success' => true, 'user' => $user]);
    }
}
