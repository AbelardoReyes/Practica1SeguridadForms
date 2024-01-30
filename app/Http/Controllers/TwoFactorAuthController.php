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
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use PDOException;

use function Laravel\Prompts\error;

class TwoFactorAuthController extends Controller
{
    /**
     * Genera un codigo de verificación y lo envia al usuario por SMS
     * Crea una ruta temporal firmada para verificar el email y el telefono
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function twoFactorAuth(Request $request)
    {
        try {
            if (!$request->hasValidSignature()) {
                abort(401);
            }
            $nRandom = rand(1000, 9999);
            $user = User::find($request->id);
            $user->code_phone = $nRandom;
            $url = URL::temporarySignedRoute(
                'verifyTwoFactorAuth',
                now()->addMinutes(30),
                ['id' => $user->id]
            );
            $user->save();
            ProcessFactorAuthSMS::dispatch($user, $nRandom)->onConnection('database')->onQueue('twoFactorAuth')->delay(now()->addseconds(30));
            return Inertia::render('twoFactorAuth', ['user' => $user, 'url' => $url]);
        } catch (PDOException $e) {
            Log::channel('slackerror')->error($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.PDO' => 'Error de Conexion'
            ]);
        } catch (QueryException $e) {
            Log::channel('slackerror')->error($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.QueryE' => 'Datos Invalidos'
            ]);
        } catch (ValidationException $e) {
            Log::channel('slackerror')->error($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.ValidationE' => 'Datos Invalidos'
            ]);
        } catch (Exception $e) {
            Log::channel('slackerror')->critical($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.Exception' => 'Ocurrio un error'
            ]);
        }
    }
    /**
     * Verifica el codigo de verificación y realiza el inicio de sesión
     * Si el codigo es incorrecto, se le envia otro codigo al usuario
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyTwoFactorAuth(Request $request)
    {
        try {
            if (!$request->hasValidSignature()) {
                abort(401);
            }
            $validator = Validator::make($request->all(), [
                'code_phone' => 'required|numeric|digits:4',
                'password' => 'required|',

            ]);
            if ($validator->fails()) {
                return Redirect::back()->withErrors($validator);
            }
            $user = User::find($request->id);
            if ($user->code_phone != $request->code_phone) {
                return Redirect::back()->withErrors('El codigo no coincide, se te enviara otro codigo');
            }
            if (!Hash::check($request->password, $user->password)) {
                return Redirect::back()->withErrors('Contraseña incorrecta');
            }
            // Crea las credenciales para iniciar sesión
            $credentials = $request->only('email', 'password');
            if (Auth::attempt($credentials)) {
                Log::channel('slackinfo')->warning('El usuario ' . $user->email . ' se logueo como administrador');
                $request->session()->put('user', $user);
                $request->session()->regenerate();
                return Redirect::route('Home');
            }
        } catch (PDOException $e) {
            Log::channel('slackerror')->error($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.PDO' => 'Error de Conexion'
            ]);
        } catch (QueryException $e) {
            Log::channel('slackerror')->error($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.QueryE' => 'Datos Invalidos'
            ]);
        } catch (ValidationException $e) {
            Log::channel('slackerror')->error($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.ValidationE' => 'Datos Invalidos'
            ]);
        } catch (Exception $e) {
            Log::channel('slackerror')->critical($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.Exception' => 'Ocurrio un error'
            ]);
        }
    }
}
