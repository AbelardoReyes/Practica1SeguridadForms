<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\PersonalAccessTokens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyMail;
use App\Jobs\ProcessVerifyEmail;
use App\Jobs\SendMail;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

class AuthController extends Controller
{

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return Inertia::render('LoginForm', [
                'error.email' => 'User not found'
            ]);
        }
        if (!Hash::check($request->password, $user->password)) {
            return Inertia::render('LoginForm', [
                'error.password' => 'Password not match'
            ]);
        }
        if (!$user->status) {
            return Inertia::render('LoginForm', [
                'error.status' => 'User not verified'
            ]);
        }
        $TOKEN_EXIST = PersonalAccessToken::where('tokenable_id', $user->id)->first();
        if ($TOKEN_EXIST) {
            PersonalAccessTokens::where('tokenable_id', $user->id)->delete();
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return Inertia::render('RegisterForm');
    }

    public function register(RegisterRequest $request)
    {
        $user = new User();
        $user->fill($request->all());
        // $user->password = Hash::make($request->password);
        $userExist = User::where('role_id', 1)->first();
        $user->role_id = $userExist ? 2 : 1;
        $user->status = false;
        $url = URL::temporarySignedRoute(
            'verifyEmail',
            now()->addMinutes(30),
            ['id' => $user->id]
        );
        SendMail::dispatch($user, $url)->onConnection('database')->onQueue('verifyEmail')->delay(now()->addseconds(5));
        $user->save();

        return response()->json([
            "msg" => "User created",
            "user" => $user,
            "url" => $url
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "message" => "User logged out"
        ], 200);
    }
    public function verifyEmail(Request $request)
    {
        $user = User::findOrFail($request->id);
        $user->status = true;
        $user->save();
        return response()->json([
            "message" => "User verified"
        ], 200);
    }
}
