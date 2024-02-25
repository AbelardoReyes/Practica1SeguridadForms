<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use PDOException;
use Illuminate\Support\Facades\Hash;

class PostCodePhoneRequest extends FormRequest
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
            'code_phone.required' => 'El código es requerido',
            'code_phone.numeric' => 'El código debe ser numérico',
            'code_phone.digits' => 'El código debe tener 4 dígitos',
        ];
    }
    /**
     * Retorna los atributos personalizados para las reglas de validación.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'code_phone' => 'Código de verificación',
        ];
    }
    /**
     * Verifica si el código de verificación es válido.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        try {
            $validator->after(function ($validator) {
                // Verificar si el usuario está activado
                $user = User::where('id', $this->id)->first();
                if (Hash::check($this->code_phone, $user->code_phone) == false) {
                    $validator->errors()->add('code_phone', 'Código Inválido');
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
