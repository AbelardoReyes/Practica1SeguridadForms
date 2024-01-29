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

    /**
     * Verifica el email del usuario.
     * Crear un codigo de verificación y lo envia al usuario por SMS
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function verifyEmail(Request $request)
    {
        // Verifica que la ruta tenga una firma valida
        if (!$request->hasValidSignature()) {
            abort(401);
        }
        $nRandom = rand(1000, 9999);
        $user = User::find($request->id);
        $user->code_phone = $nRandom;
        $user->save();
        // Crear una ruta temporal firmada para verificar el email y el telefono
        $url = URL::temporarySignedRoute(
            'sendCodeVerifyEmailAndPhone',
            now()->addMinutes(30),
            ['id' => $user->id]
        );
        // ProcessSendSMS::dispatch($user, $nRandom)->onConnection('database')->onQueue('sendSMS')->delay(now()->addseconds(30));
        return Inertia::render('VerifyEmailForm', ['user' => $user, 'url' => $url]);
    }

    /**
     * Recibe el codigo de verificación
     * y verifica que coincida con el codigo enviado al usuario por SMS
     * Activa la cuenta del usuario
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendCodeVerifyEmailAndPhone(Request $request)
    {
        // Verifica que la ruta tenga una firma valida
        if (!$request->hasValidSignature()) {
            abort(401);
        }
        $user = User::find($request->id);
        // Verifica que el codigo de verificación coincida con el codigo enviado al usuario por SMS
        if ($user->code_phone != $request->code_phone) {
            return Redirect::back()->withErrors('El codigo no coincide, se te enviara otro codigo');
        }
        $user->status = true;
        $user->save();
        return Redirect::route('login');
    }
}
