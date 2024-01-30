<?php

namespace App\Http\Controllers;


use App\Http\Requests\PostTwoFactorsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use App\Jobs\ProcessSendSMS;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use PDOException;

use function Laravel\Prompts\error;

class TwoFactorAuthController extends Controller
{

    /**
     * Método para autenticación de dos factores.
     * Envia un código aleatorio al usuario por WhatsApp.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @return \Inertia\Response La respuesta de Inertia con los datos del usuario y la URL de verificación.
     */
    public function twoFactorAuth(Request $request)
    {
        try {
            // Verificar si la solicitud tiene una firma válida
            if (!$request->hasValidSignature()) {
                abort(401);
            }
            $nRandom = rand(1000, 9999);
            $user = User::find($request->id);
            $url = URL::temporarySignedRoute(
                'verifyTwoFactorAuth',
                now()->addMinutes(30),
                ['id' => $user->id]
            );
            // Si el código de teléfono del usuario es nulo, enviar un mensaje de texto con el código aleatorio
            if ($user->code_phone == null) {
                ProcessSendSMS::dispatch($user, $nRandom)->onConnection('database')->onQueue('sendSMS')->delay(now()->addseconds(30));
                $user->code_phone = $nRandom;
            }
            $user->save();
            return Inertia::render('twoFactorAuth', ['user' => $user, 'url' => $url]);
        } catch (PDOException $e) {
            // Manejar excepción de PDO
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'PDO' => 'Hubo un error inesperado, intente más tarde'
            ]);
        } catch (QueryException $e) {
            // Manejar excepción de QueryException
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'QueryE' => 'Datos inválidos'
            ]);
        } catch (ValidationException $e) {
            // Manejar excepción de ValidationException
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'ValidationE' => 'Datos inválidos'
            ]);
        } catch (Exception $e) {
            // Manejar excepción genérica
            Log::channel('slackinfo')->critical($e->getMessage());
            return Redirect::route('login')->withErrors([
                'Exception' => 'Ocurrió un error'
            ]);
        }
    }

    /**
     * Verifica el código de autenticación de dos factores.
     * Recibe el código de autenticación de dos factores y verifica que coincida con el código enviado al usuario por WhatsApp.
     *
     * @param PostTwoFactorsRequest $request La solicitud HTTP recibida.
     * @return \Illuminate\Http\RedirectResponse La respuesta de redirección a la página de inicio.
     */
    public function verifyTwoFactorAuth(PostTwoFactorsRequest $request)
    {
        try {
            if (!$request->hasValidSignature()) {
                abort(401);
            }
            $user = User::find($request->id);
            $user->code_phone = null;
            $credentials = $request->only('email', 'password');
            if (Auth::attempt($credentials)) {
                Log::channel('slackinfo')->warning('El usuario ' . $user->email . ' se logueo como administrador');
                $request->session()->put('user', $user);
                $request->session()->regenerate();
            }
            $user->save();
            return Redirect::route('Home');
        } catch (PDOException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'PDO' => 'Hubo un error de inesperado, intente mas tarde'
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
}
