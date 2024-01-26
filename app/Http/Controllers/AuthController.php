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

class AuthController extends Controller
{

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                "message" => "Password incorrect"
            ], 401);
        }
        if (!$user->status) {
            return response()->json([
                "message" => "User not active"
            ], 403);
        }
        $TOKEN_EXIST = PersonalAccessToken::where('tokenable_id', $user->id)->first();
        if ($TOKEN_EXIST) {
            DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            "message" => "User logged",
            "user" => $user,
            "token" => $token
        ], 200);
    }

    public function register(RegisterRequest $request)
    {
        $user = new User();
        $user->fill($request->all());
        // $user->password = Hash::make($request->password);
        $userExist = User::where('role_id', 1)->first();
        if (!$userExist) {
            $user->role_id = 1;
            $user->status = false;
            $user->save();
            return response()->json([
                "msg" => "Admin created",
                "user" => $user
            ], 200);
        }
        $user->role_id = 2;
        $user->status = false;
        $user->save();
        return response()->json([
            "msg" => "User created",
            "user" => $user
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "message" => "User logged out"
        ], 200);
    }
}
