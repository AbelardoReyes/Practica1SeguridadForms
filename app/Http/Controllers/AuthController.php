<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
class AuthController extends Controller
{

    public function login()
    {
        return response()->json([
            "message" => "Login success"
        ]);
    }
    public function register(Request $request)
    {
        $user = new User();
        return response()->json([
            "message" => $request->all()
        ]);
    }
}
