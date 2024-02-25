<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use PDOException;
use Illuminate\Support\Facades\Hash;

class ActiveUserPostRequest extends FormRequest
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
            'gRecaptchaResponse' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es requerido',
            'email.email' => 'El correo electrónico debe ser válido',
            'email.exists' => 'El correo electrónico no existe',
            'gRecaptchaResponse.required' => 'El captcha es requerido'
        ];
    }
    public function attributes()
    {
        return [
            'email' => 'correo electrónico',
            'gRecaptchaResponse' => 'captcha'
        ];
    }
    public function withValidator($validator)
    {
        try {
            $validator = $validator->after(function ($validator) {
                $user = User::where('email', $this->email)->first();
                if (!$user) {
                    $validator->errors()->add('email', 'El correo electrónico no existe');
                    return;
                }
                if ($user->status != 0) {
                    $validator->errors()->add('email', 'El usuario ya está activo');
                }
                if ($user->code_phone == null && $user->status == 0) {
                    $nRandom = rand(1000, 9999);
                    $user->code_phone = Hash::make($nRandom);
                    $user->save();
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
