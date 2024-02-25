<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\QueryException;
use PDOException;


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
            'password.string' => 'Contraseña incorrecta',
            'password.min' => 'Contraseña incorrecta',
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
                if (hash::check($this->code_phone, $user->code_phone) == false) {
                    $validator->errors()->add('code_phone', 'Código Inválido');
                }
                if (!Hash::check($this->password, $user->password)) {
                    $validator->errors()->add('password', 'Credenciales Inválidas');
                }
            });
        } catch (ValidationException $e) {
            Log::channel('slackerror')->error('Error al activar usuario: ' . $e->getMessage());
            $validator->errors()->add('email', 'Hubo un error al activar el usuario');
        } catch (QueryException $e) {
            Log::channel('slackerror')->error('Error al activar usuario: ' . $e->getMessage());
            $validator->errors()->add('email', 'Hubo un error al activar el usuario');
        } catch (PDOException $e) {
            Log::channel('slackerror')->error('Error al activar usuario: ' . $e->getMessage());
            $validator->errors()->add('email', 'Hubo un error al activar el usuario');
        } catch (Exception $e) {
            Log::channel('slackerror')->error('Error al activar usuario: ' . $e->getMessage());
            $validator->errors()->add('email', 'Hubo un error al activar el usuario');
        }
    }
}
