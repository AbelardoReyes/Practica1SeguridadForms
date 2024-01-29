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

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $rules = [
                'email' => 'required|email',
                'password' => 'required',
                'gRecaptchaResponse' => 'required'
            ];
            $messages = [
                'email.required' => 'El email es requerido',
                'email.email' => 'El email no es valido',
                'password.required' => 'La contraseña es requerida',
                'gRecaptchaResponse.required' => 'El captcha es requerido'
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                Log::error($validator->errors());
                return Inertia::render('LoginForm', [
                    'errors' => $validator->errors()
                ]);
            }
            // $recaptcha = Http::post('https://www.google.com/recaptcha/api/siteverify', [
            //     'secret' => '6Lelul4pAAAAADiBihVlhWcWVLZ9QnqwZyeSCMMc',
            //     'response' => $request->gRecaptchaResponse
            // ]);
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                Log::error('User not found');
                return Inertia::render('LoginForm', [
                    'error.email' => 'Credenciales Invalidas'
                ]);
            }
            if (!Hash::check($request->password, $user->password)) {
                return Inertia::render('LoginForm', [
                    'error.password' => 'Credenciales Invalidas'
                ]);
            }
            if (!$user->status) {
                return Inertia::render('LoginForm', [
                    'errors.status' => 'Usuario no verificado'
                ]);
            }

            if ($user->role_id == 2) {
                $credentials = $request->only('email', 'password');
                if (Auth::attempt($credentials)) {
                    $request->session()->put('user', $user);
                    $request->session()->regenerate();
                }
                return Redirect::route('Home');
            } else {
                $url = URL::temporarySignedRoute(
                    'twoFactorAuth',
                    now()->addMinutes(30),
                    ['id' => $user->id]
                );
                return Inertia::location($url);
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return Inertia::render('LoginForm', [
                'error' => 'Algo salio mal'
            ]);
        }
    }

    public function register(Request $request)
    {
        $rules = [
            'name' => 'required',
            'last_name' => 'required',
            'phone' => 'required|unique:users',
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
        return Redirect::route('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerate();
        return Redirect::route('login');
    }

}
