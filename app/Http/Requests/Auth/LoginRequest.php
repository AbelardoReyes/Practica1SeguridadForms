<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;

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
    public function attributes()
    {
        return [
            'email' => 'email',
            'password' => 'contraseña',
            'gRecaptchaResponse' => 'captcha'
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verificar si el usuario está activado
            $user = User::where('email', $this->email)->where('status', 1)->first();

            if (!$user) {
                $validator->errors()->add('email', 'Cuenta no activada');
            } elseif (!Hash::check($this->password, $user->password)) {
                $validator->errors()->add('password', 'Credenciales Inválidas');
            }
        });
    }
}
