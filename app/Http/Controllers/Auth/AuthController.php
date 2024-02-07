<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use App\Jobs\ProcessVerifyEmail;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use PDOException;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ActivateUserRequest;
use App\Http\Requests\Auth\ActiveUserPostRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Jobs\ProcessSendSMS;
use App\Jobs\ProcessSendCodeEmail;



class AuthController extends Controller
{
    private $adminRole = 1;
    private $userRole = 2;
    /**
     * Loguea al usuario.
     * Redirecciona al usuario a la pagina de verificación de dos factores si es administrador.
     *
     * @param  LoginRequest  $request
     * @return \Illuminate\Http\Response La respuesta de Inertia con los datos del usuario y la URL de verificación.
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            // Verifica si el usuario es administrador
            if ($user->role_id == $this->adminRole) {
                Log::channel('slackinfo')->warning('Intento de inicio de sesion de ' . $user->email . ' como administrador');
                $url = URL::temporarySignedRoute(
                    'twoFactorAuth',
                    now()->addMinutes(60),
                    ['id' => $user->id]
                );
                return Inertia::location($url);
            }
            // Si no es administrador, inicia sesión normalmente
            $credentials = $request->only('email', 'password');
            if (!Auth::attempt($credentials)) {
                return Redirect::back()->withErrors([
                    'password' => 'Credenciales Inválidas', 'email' => 'Credenciales Inválidas'
                ]);
            }

            // Store the user in the session
            $request->session()->put('user', $user);
            $request->session()->regenerate();
            Log::channel('slackinfo')->info('Inicio de sesión de ' . $user->email);

            return Redirect::route('Home');
        } catch (PDOException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'PDO' => 'Hubo un error inesperado, intente registrarse más tarde'
            ]);
        } catch (QueryException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'QueryE' => 'Datos inválidos'
            ]);
        } catch (ValidationException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'ValidationE' => 'Datos inválidos'
            ]);
        } catch (Exception $e) {
            Log::channel('slackinfo')->critical($e->getMessage());
            return Redirect::route('login')->withErrors([
                'Exception' => 'Ocurrió un error'
            ]);
        }
    }


    /**
     * Registra un nuevo usuario.
     * Envia un correo de verificación al usuario.
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\RedirectResponse La respuesta de redirección a la página de inicio de sesión.
     */
    public function register(RegisterRequest $request)
    {
        try {
            $user = new User();
            $user->fill($request->all());
            $user->save();
            $url = URL::temporarySignedRoute(
                'verifyEmail',
                now()->addMinutes(60),
                ['id' => $user->id]
            );

            Log::channel('slackinfo')->info('El usuario ' . $user->email . ' esta en proceso de registro');
            ProcessVerifyEmail::dispatch($user, $url)->onConnection('database')->onQueue('verifyEmail')->delay(now()->addseconds(10));
            return Redirect::route('login');
        } catch (PDOException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'PDO' => 'Hubo un error inesperado, intente registrarse más tarde'
            ]);
        } catch (QueryException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'QueryE' => 'Datos inválidos'
            ]);
        } catch (ValidationException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'ValidationE' => 'Datos inválidos'
            ]);
        } catch (Exception $e) {
            Log::channel('slackinfo')->critical($e->getMessage());
            return Redirect::route('login')->withErrors([
                'Exception' => 'Ocurrió un error'
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

    /**
     * Activa un usuario y envía un código de verificación por correo electrónico y teléfono.
     *
     * @param ActiveUserPostRequest $request La solicitud de activación de usuario.
     * @return \Inertia\Response La respuesta de la vista VerifyEmailForm con el usuario y la URL de verificación.
     */
    public function activeUser(ActiveUserPostRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            $url = URL::temporarySignedRoute(
                'sendCodeVerifyEmailAndPhone',
                now()->addMinutes(30),
                ['id' => $user->id]
            );
            Log::channel('slackinfo')->info('Se envio un correo con un codigo de activación de cuenta al usuario ' . $user->email);
            ProcessSendCodeEmail::dispatch($user, $user->code_phone)->onConnection('database')->onQueue('sendCodeEmail')->delay(now()->addseconds(30));
            return Inertia::render('VerifyEmailForm', ['user' => $user, 'url' => $url]);
        } catch (PDOException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'PDO' => 'Hubo un error inesperado, intente registrarse más tarde'
            ]);
        } catch (QueryException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'QueryE' => 'Datos inválidos'
            ]);
        } catch (ValidationException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'ValidationE' => 'Datos inválidos'
            ]);
        } catch (Exception $e) {
            Log::channel('slackinfo')->critical($e->getMessage());
            return Redirect::route('login')->withErrors([
                'Exception' => 'Ocurrió un error'
            ]);
        }
    }
}
