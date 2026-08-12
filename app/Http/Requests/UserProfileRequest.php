<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Http\FormRequest;

class UserProfileRequest extends FormRequest
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->auth->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'documento' => 'required|numeric',
            'genero' => 'required',
            'nombres' => 'required|min:2|max:250',
            'apellidos' => 'required|min:2|max:250',
            'email' => 'email|unique:usuarios,email,'.$this->auth->user()->id,
            'password' => [
                'confirmed',
                'required',
                'min:6',
                'max:50',
                'regex:/[a-z]/', // debe contener al menos letra minúscula
                'regex:/[A-Z]/', // debe contener al menos una letra mayúscula
                'regex:/[0-9]/', // debe contener al menos un número
            ],
        ];
    }

    public function messages()
    {
        return [
            'password.required' => 'La Contraseña es requerida',
            'password.min' => 'La Contraseña debe ser de al menos 6 caracteres',
            'password.max' => 'La Contraseña no puede ser más que 50 caracteres',
            'password.regex' => 'La contraseña debe incluir al menos una letra mayúscula, una letra minúscula y un número',
        ];
    }
}
