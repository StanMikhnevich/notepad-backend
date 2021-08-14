<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\LoginRequest;

use App\Models\User;
use Validator;


class UserController extends Controller
{

    /**
     * @param BaseFormRequest $request
     * @return \Illuminate\Http\JsonResponse
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
