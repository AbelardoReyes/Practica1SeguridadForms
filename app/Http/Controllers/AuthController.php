<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use App\Jobs\ProcessVerifyEmail;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use PDOException;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;

use function Laravel\Prompts\error;

class AuthController extends Controller
{
    private $adminRole = 1;
    private $userRole = 2;
    /**
     * Método para iniciar sesión.
     * Valida los datos recibidos y realiza el inicio de sesión.
     * Si el usuario es administrador, se le envia un codigo de verificación por SMS.
     * Implementa Google reCaptcha v2.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @return \Illuminate\Http\Response La respuesta HTTP.
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            // Verifica el captcha haciendo una solicitud a la API de Google
            $recaptcha = Http::asForm()->post(env('API_GOOGLE_RECAPTCHA'), [
                'secret' => env('SECRET_RECAPTCHA'),
                'response' => $request->gRecaptchaResponse
            ]);
            // Si el captcha es invalido, retorna el formulario de inicio de sesion con el error
            if (!$recaptcha->json()['success']) {
                Log::channel('slackinfo')->warning('Intento de inicio de sesion con captcha invalido');
                return Redirect::back()->withErrors([
                    'gRecaptchaResponse' => 'Captcha Invalido'
                ]);
            }
            // Si el usuario es administrador, envia un codigo de verificacion por SMS
            if ($user->role_id == $this->adminRole) {
                Log::channel('slackinfo')->warning('Intento de inicio de sesion de ' . $user->email . ' como administrador');
                $url = URL::temporarySignedRoute(
                    'twoFactorAuth',
                    now()->addMinutes(30),
                    ['id' => $user->id]
                );
                return Inertia::location($url);
            }
            // Si el usuario no es administrador, inicia sesion directamente
            $credentials = $request->only('email', 'password');
            if (!Auth::attempt($credentials)) {
                return Redirect::back()->withErrors([
                    'password' => 'Credeciales Invalidas', 'email' => 'Credeciales Invalidas'
                ]);
            }
            $request->session()->put('user', $user);
            $request->session()->regenerate();
            Log::channel('slackinfo')->info('Inicio de sesion de ' . $user->email);
            return Redirect::route('Home');
        } catch (PDOException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.PDO' => 'Hubo un error de inesperado, intente registrarse mas tarde'
            ]);
        } catch (QueryException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.QueryE' => 'Datos Invalidos'
            ]);
        } catch (ValidationException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.ValidationE' => 'Datos Invalidos'
            ]);
        } catch (Exception $e) {
            Log::channel('slackinfo')->critical($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.Exception' => 'Ocurrio un error'
            ]);
        }
    }

    /**
     * Realiza el registro de un usuario.
     * Envia un correo de verificación al usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $request)
    {
        try {
            // Verifica el captcha haciendo una solicitud a la API de Google
            $recaptcha = Http::asForm()->post(env('API_GOOGLE_RECAPTCHA'), [
                'secret' => env('SECRET_RECAPTCHA'),
                'response' => $request->gRecaptchaResponse
            ]);
            // Si el captcha es invalido, retorna el formulario de inicio de sesion con el error
            if (!$recaptcha->json()['success']) {
                Log::channel('slackinfo')->warning('Intento de registro con captcha invalido');
                return Redirect::back()->withErrors([
                    'gRecaptchaResponse' => 'Captcha Invalido'
                ]);
            }
            $user = new User();
            // Llena el modelo con los datos recibidos
            $user->fill($request->all());
            $user->save();
            // Genera la url para verificar el correo
            $url = URL::temporarySignedRoute(
                'verifyEmail',
                now()->addMinutes(60),
                ['id' => $user->id]
            );

            Log::channel('slackinfo')->info('Se registro ' . $user->email);
            // Envia el correo de verificación
            ProcessVerifyEmail::dispatch($user, $url)->onConnection('database')->onQueue('verifyEmail')->delay(now()->addseconds(10));
            return Redirect::route('login');
        } catch (PDOException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'PDO' => 'Hubo un error de inesperado, intente registrarse mas tarde'
            ]);
        } catch (QueryException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'QueryE' => 'Datos Invalidos'
            ]);
        } catch (ValidationException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'ValidationE' => 'Datos Invalidos'
            ]);
        } catch (Exception $e) {
            Log::channel('slackinfo')->critical($e->getMessage());
            return Redirect::route('login')->withErrors([
                'Exception' => 'Ocurrio un error'
            ]);
        }
    }

    /**
     * Meotodo para cerrar sesión.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function logout(Request $request)
    {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerate();
            return Redirect::route('login');
        } catch (\Exception $e) {
            Log::channel('slackerror')->critical($e->getMessage());
            return Redirect::route('login')->withErrors([
                'Exception' => 'Ocurrio un error'
            ]);
        }
    }
}
