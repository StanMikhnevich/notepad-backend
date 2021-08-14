<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\BaseFormRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\LoginRequest;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Validator;


class UserController extends Controller
{

    /**
     * @param RegisterRequest|BaseFormRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create(array_merge($request->only('name', 'email'), [
            'password' => bcrypt($request->input('password')),
        ]));

        $user->sendEmailVerificationNotification('app');

        Auth::login($user);

        return response()->json([
            'success' => true,
            'user' => $user->only('id', 'name', 'email', 'email_verified_at'),
            'message' => 'You were successfully registered. Use your email and password to sign in.',
        ]);

    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function verify(Request $request, User $user): JsonResponse
    {
        if (!$request->hasValidSignature()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid/Expired url provided',
            ], 401);
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return response()->json([
            'success' => true,
            'message' => 'Email is verified',
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resend(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = User::find($request->input('user_id'));
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'verified' => true,
                'message' => 'Email already verified',
            ]);
        }

        $user->sendEmailVerificationNotification('app');

        return response()->json([
            'success' => true,
            'verified' => false,
            'message' => 'Email verification link sent on your email',
        ]);

    }

    /**
     * @param LoginRequest|BaseFormRequest $request
     * @return JsonResponse
     */
    public function login(BaseFormRequest $request): JsonResponse
    {
        $validation = Validator::make($request->only('email', 'password'), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 202);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot sign with those credentials',
                'errors' => 'Unauthorised'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken(config('app.name'));

        $token->token->expires_at = $request->only('remember_me')
            ? Carbon::now()->addMonth()
            : Carbon::now()->addDay();

        $token->token->save();

        return response()->json([
            'success' => true,
            'user' => $user->only('id', 'name', 'email', 'email_verified_at'),
            'token_type' => 'Bearer',
            'token' => $token->accessToken,
            'expires_at' => Carbon::parse($token->token->expires_at)->toDateTimeString()
        ]);

    }

    /**
     * @param LoginRequest|BaseFormRequest $request
     * @return JsonResponse
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return response()->json([
            'success' => ($status == Password::RESET_LINK_SENT),
        ]);
    }

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

