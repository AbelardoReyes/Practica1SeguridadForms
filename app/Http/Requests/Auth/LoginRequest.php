<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
            'gRecaptchaResponse' => 'required'
        ];
    }
    /**
     * Retorna un arreglo con los mensajes de error.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => 'El email es requerido',
            'email.email' => 'El email no es valido',
            'email.exists' => 'El email no esta registrado',
            'password.required' => 'La contraseña es requerida',
            'gRecaptchaResponse.required' => 'El captcha es requerido'
        ];
    }
    /**
     * Retorna un arreglo con los nombres de los atributos del formulario.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'email' => 'email',
            'password' => 'contraseña',
            'gRecaptchaResponse' => 'captcha'
        ];
    }
    /**
     * Ejecuta validaciones adicionales después de que se hayan realizado las validaciones principales.
     * Verifica que el usuario esté activado y que la contraseña sea correcta.
     * Verifica que el captcha sea correcto.
     * Si alguna de las validaciones falla, se agrega un error al validador.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
       try{
        $validator->after(function ($validator) {
            // Verificar si el usuario está activado
            $user = User::where('email', $this->email)->where('status', 1)->first();

            if (!$user) {
                $validator->errors()->add('email', 'Cuenta no activada');
            }
            if (!Hash::check($this->password, $user->password)) {
                $validator->errors()->add('password', 'Credenciales Inválidas');
            }
            $recaptcha = Http::asForm()->post(env('API_GOOGLE_RECAPTCHA'), [
                'secret' => env('SECRET_RECAPTCHA'),
                'response' => $this->gRecaptchaResponse
            ]);
            if (!$recaptcha->json()['success']) {
                Log::channel('slackinfo')->warning('Intento de inicio de sesion con captcha invalido');
                $validator->errors()->add('gRecaptchaResponse', 'Captcha Inválido');
            }
        });
       } catch (Exception $e) {
        Log::channel('slackinfo')->critical($e->getMessage());
       }
    }
}
