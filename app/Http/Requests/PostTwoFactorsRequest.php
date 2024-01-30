<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class PostTwoFactorsRequest extends FormRequest
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
            'code_phone' => 'required|numeric|digits:4',
            'password' => 'required|string|min:8',
        ];
    }
    /**
     * Retorna un array con los mensajes de validación personalizados para el formulario de autenticación de dos factores.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'code_phone.required' => 'El codigo es requerido',
            'code_phone.numeric' => 'El codigo debe ser numerico',
            'code_phone.digits' => 'El codigo debe tener 4 digitos',
            'password.required' => 'La contraseña es requerida',
            'password.string' => 'La contraseña debe ser una cadena de caracteres',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
        ];
    }
    /**
     * Retorna un array con los atributos y sus nombres personalizados.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'code_phone' => 'Codigo de verificación',
            'password' => 'Contraseña',
        ];
    }

    /**
     * Validar el código de verificación y la contraseña del usuario.
     * Si el código de verificación o la contraseña son inválidos, se agrega un error al validador.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        try{
            $validator->after(function ($validator) {
                // Verificar si el usuario está activado
                $user = User::where('id', $this->id)->first();
                if ($this->code_phone != $user->code_phone) {
                    $validator->errors()->add('code_phone', 'Código Inválido');
                }
                if (!Hash::check($this->password, $user->password)) {
                    $validator->errors()->add('password', 'Credenciales Inválidas');
                }
            });
        } catch(Exception $e){
            Log::channel('slackinfo')->critical($e->getMessage());
        }
    }
}
