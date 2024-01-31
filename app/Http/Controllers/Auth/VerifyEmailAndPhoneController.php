<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostCodePhoneRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use App\Jobs\ProcessSendSMS;
use App\Jobs\ProcessFactorAuthSMS;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

use PDOException;
use App\Jobs\ProcessEmailSucces;


class VerifyEmailAndPhoneController extends Controller
{
    /**
     * Verifica que el correo sea valido
     * Envia un mensaje de texto con un codigo de verificación
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response La respuesta de Inertia con los datos del usuario y la URL de verificación.
     */
    public function verifyEmail(Request $request)
    {
        try {
            if (!$request->hasValidSignature()) {
                abort(401);
            }
            $nRandom = rand(1000, 9999);
            $user = User::find($request->id);
            $url = URL::temporarySignedRoute(
                'sendCodeVerifyEmailAndPhone',
                now()->addMinutes(30),
                ['id' => $user->id]
            );
            if ($user->code_phone == null) {
                $user->code_phone = $nRandom;
                ProcessSendSMS::dispatch($user, $nRandom)->onConnection('database')->onQueue('sendSMS')->delay(now()->addseconds(30));
            }
            $user->save();
            return Inertia::render('VerifyEmailForm', ['user' => $user, 'url' => $url]);
        } catch (PDOException $e) {
            Log::channel('slackinfo')->error($e->getMessage());
            return Redirect::route('login')->withErrors([
                'PDO' => 'Hubo un error de inesperado, intente mas tarde'
            ]);
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
     * Recibe el código de verificación y verifica que sea correcto
     * Si es correcto, activa la cuenta del usuario
     *
     * @param  \App\Http\Requests\PostCodePhoneRequest  $request  La solicitud de código de teléfono.
     * @return \Illuminate\Http\RedirectResponse  La respuesta de redirección a la página de inicio de sesión.
     */
    public function sendCodeVerifyEmailAndPhone(PostCodePhoneRequest $request)
    {
        try {
            // Verifica que la ruta tenga una firma valida
            if (!$request->hasValidSignature()) {
                abort(401);
            }
            $user = User::find($request->id);
            $user->status = true;
            $user->code_phone = null;
            $user->save();
            if ($user->role_id == 1) {
                Log::channel('slackinfo')->warning('Se activo la cuenta del usuario administrador' . $user->name);
            }
            ProcessEmailSucces::dispatch($user)->onConnection('database')->onQueue('sendEmailSucces')->delay(now()->addseconds(30));
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
}
