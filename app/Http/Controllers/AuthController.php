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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\error;

class AuthController extends Controller
{

    public function validCaptcha(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gRecaptchaResponse' => 'required|captcha'
        ]);
        if ($validator->fails()) {
            return Redirect::route('login')->withErrors($validator->errors());
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'gRecaptchaResponse' => 'required'
        ]);
        // $recaptcha = Http::post('https://www.google.com/recaptcha/api/siteverify', [
        //     'secret' => '6Lelul4pAAAAADiBihVlhWcWVLZ9QnqwZyeSCMMc',
        //     'response' => $request->gRecaptchaResponse
        // ]);
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
        return Redirect::route('Home');
    }

    public function register(Request $request)
    {
        $rules = [
            'name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'password_confirmation' => 'required',
        ];
        $messages = [
            'email.required' => 'El email es requerido',
            'email.unique' => 'El email no esta disponible',
            'email.email' => 'El email no es valido',
            'password.required' => 'La contraseña es requerida',
            'name.required' => 'El nombre es requerido',
            'last_name.required' => 'El apellido es requerido',
            'phone.required' => 'El numero de telefono es requerido',
            'password_confirmation.required' => 'La confirmación de la contraseña es requerida',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($request->password != $request->password_confirmation) {
            return Inertia::render('RegisterForm', [
                'errors.password_confirmation' => 'Las contraseñas no coinciden'
            ]);
        }
        if ($validator->fails()) {
            return Inertia::render('RegisterForm', [
                'errors' => $validator->errors()
            ]);
        }
        $user = new User();
        $user->fill($request->all());
        $user->save();
        $url = URL::temporarySignedRoute(
            'verifyEmail',
            now()->addMinutes(30),
            ['id' => $user->id]
        );
        ProcessVerifyEmail::dispatch($user, $url)->onConnection('database')->onQueue('verifyEmail')->delay(now()->addseconds(10));
        return Inertia::render('LoginForm');
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
        ProcessSendSMS::dispatch($user, $nRandom)->onConnection('database')->onQueue('sendSMS')->delay(now()->addseconds(30));
        return Inertia::render('VerifyEmailForm', ['user' => $user, 'url' => $url]);
    }


    public function sendCodeVerifyEmailAndPhone(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(401);
        }
        $user = User::find($request->id);
        if ($user->code_phone != $request->code_phone) {
            return redirect()->back()->withErrors(['error.code_phone' => 'El codigo no coincide']);
        }
        $user->status = true;
        $user->save();
        return Redirect::route('login');
    }
}
