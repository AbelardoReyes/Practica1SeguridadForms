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

use function Laravel\Prompts\error;

class AuthController extends Controller
{
    /**
     * Método para iniciar sesión.
     * Valida los datos recibidos y realiza el inicio de sesión.
     * Si el usuario es administrador, se le envia un codigo de verificación por SMS.
     * Implementa Google reCaptcha v2.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @return \Illuminate\Http\Response La respuesta HTTP.
     */
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
            // Valida los datos recibidos
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return Inertia::render('LoginForm', [
                    'errors' => $validator->errors()
                ]);
            }
            // Verifica el captcha haciendo una solicitud a la API de Google
            $recaptcha = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => '6Lelul4pAAAAADiBihVlhWcWVLZ9QnqwZyeSCMMc',
                'response' => $request->gRecaptchaResponse
            ]);
            // Si el captcha es invalido, retorna el formulario de inicio de sesion con el error
            if (!$recaptcha->json()['success']) {
                Log::channel('slackinfo')->error('Intento de inicio de sesion con captcha invalido');
                return Inertia::render('LoginForm', [
                    'error.gRecaptchaResponse' => 'Captcha Invalido'
                ]);
            }
            // Busca el usuario en la base de datos por su email y verifica que este activo
            $user = User::where('email', $request->email)->where('status', 1)->first();
            if (!$user) {
                return Inertia::render('LoginForm', [
                    'error.email' => 'Credenciales Invalidas', 'errors.status' => 'Puede que su usuario no este verificado'
                ]);
            }
            // Verifica que la contraseña sea correcta
            if (!Hash::check($request->password, $user->password)) {
                return Inertia::render('LoginForm', [
                    'error.password' => 'Credenciales Invalidas'
                ]);
            }
            // Si el usuario no es administrador, inicia sesion directamente
            if ($user->role_id == 2) {
                $credentials = $request->only('email', 'password');
                if (!Auth::attempt($credentials)) {
                    return Inertia::render('LoginForm', [
                        'error.email' => 'Credenciales Invalidas', 'error.password' => 'Credenciales Invalidas'
                    ]);
                }
                $request->session()->put('user', $user);
                $request->session()->regenerate();
                Log::channel('slackinfo')->info('Inicio de sesion de ' . $user->email);
                return Redirect::route('Home');
            }
            // Si el usuario es administrador, envia un codigo de verificacion por SMS
            else {
                Log::channel('slackinfo')->info('Intento de inicio de sesion de ' . $user->email . ' como administrador');
                $url = URL::temporarySignedRoute(
                    'twoFactorAuth',
                    now()->addMinutes(30),
                    ['id' => $user->id]
                );
                return Inertia::location($url);
            }
        } catch (PDOException $e) {
            Log::channel('slackerror')->error($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.PDO' => 'Error de Conexion'
            ]);
        } catch (QueryException $e) {
            Log::channel('slackerror')->error($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.QE' => 'Datos Invalidos'
            ]);
        } catch (ValidationException $e) {
            Log::channel('slackerror')->error($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.VE' => 'Datos Invalidos'
            ]);
        } catch (Exception $e) {
            Log::channel('slackerror')->critical($e->getMessage());
            return Inertia::render('LoginForm', [
                'error.E' => 'Ocurrio un error'
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
    public function register(Request $request)
    {
        try {
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
            // Valida los datos recibidos
            $validator = Validator::make($request->all(), $rules, $messages);
            // Si hay errores, retorna el formulario de registro con los errores
            if ($validator->fails()) {
                return Inertia::render('RegisterForm', [
                    'errors' => $validator->errors()
                ]);
            }
            // Verifica que las contraseñas coincidan
            if ($request->password != $request->password_confirmation) {
                return Inertia::render('RegisterForm', [
                    'errors.password_confirmation' => 'Las contraseñas no coinciden'
                ]);
            }
            $user = new User();
            // Llena el modelo con los datos recibidos
            $user->fill($request->all());
            $user->save();
            // Genera la url para verificar el correo
            $url = URL::temporarySignedRoute(
                'verifyEmail',
                now()->addMinutes(30),
                ['id' => $user->id]
            );

            Log::channel('slackinfo')->info('Se registro ' . $user->email);
            // Envia el correo de verificación
            ProcessVerifyEmail::dispatch($user, $url)->onConnection('database')->onQueue('verifyEmail')->delay(now()->addseconds(10));
            return Redirect::route('login');
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
            return Inertia::render('LoginForm', [
                'error.Exception' => $e->getMessage()
            ]);
        }
    }
}
