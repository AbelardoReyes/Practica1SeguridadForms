<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required',
            'last_name' => 'required',
            'phone' => 'required|unique:users|digits:10',
            'email' => 'required|email|unique:users',
            'password' => 'required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/',
            'password_confirmation' => 'required|same:password',
            'gRecaptchaResponse' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'email.required' => 'El email es requerido',
            'email.unique' => 'El email no esta disponible',
            'email.email' => 'El email no es valido',
            'password.required' => 'La contraseña es requerida',
            'password.regex' => 'La contraseña debe contener al menos una mayuscula, una minuscula, un numero y 8 caracteres',
            'name.required' => 'El nombre es requerido',
            'last_name.required' => 'El apellido es requerido',
            'phone.required' => 'El numero de telefono es requerido',
            'phone.unique' => 'El numero de telefono ya esta registrado',
            'phone.digits' => 'El numero de telefono debe tener 10 digitos',
            'password_confirmation.required' => 'La confirmación de la contraseña es requerida',
            'password_confirmation.same' => 'Las contraseñas no coinciden',
            'gRecaptchaResponse.required' => 'El captcha es requerido'
        ];
    }
    public function attributes()
    {
        return [
            'name' => 'nombre',
            'last_name' => 'apellidos',
            'phone' => 'telefono',
            'email' => 'correo',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
            'gRecaptchaResponse' => 'captcha'
        ];
    }
}
