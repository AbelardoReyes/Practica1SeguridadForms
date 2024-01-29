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
use App\Jobs\ProcessSendSMS;
use App\Jobs\ProcessFactorAuthSMS;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use function Laravel\Prompts\error;

class VerifyEmailAndPhoneController extends Controller
{

    public function verifyEmail(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(401);
        }
        $nRandom = rand(1000, 9999);
        $user = User::find($request->id);
        $user->code_phone = $nRandom;
        $user->save();
        $url = URL::temporarySignedRoute(
            'sendCodeVerifyEmailAndPhone',
            now()->addMinutes(30),
            ['id' => $user->id]
        );
        // ProcessSendSMS::dispatch($user, $nRandom)->onConnection('database')->onQueue('sendSMS')->delay(now()->addseconds(30));
        return Inertia::render('VerifyEmailForm', ['user' => $user, 'url' => $url]);
    }

    public function sendCodeVerifyEmailAndPhone(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(401);
        }
        $user = User::find($request->id);
        if ($user->code_phone != $request->code_phone) {
            return Redirect::back()->withErrors('El codigo no coincide, se te enviara otro codigo');
        }
        $user->status = true;
        $user->save();
        return Redirect::route('login');
    }
}
