<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $permission = 'create';

        // EDIT
        if ($this->input('_method') == 'PATCH') {
            $permission = 'edit';
        }

        if ($this->input('_method') == 'DELETE') {
            $permission = 'delete';
        }

        return RequestUtils::havePermission('usuarios', $permission);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->segment(3) == 'changepass') {
            return [
                'password' => [
                    'required',
                    'min:6',
                    'max:50',
                    'regex:/[a-z]/', // debe contener al menos letra minúscula
                    'regex:/[A-Z]/', // debe contener al menos una letra mayúscula
                    'regex:/[0-9]/', // debe contener al menos un número
                ],
            ];
        }

        $filter = '';
        if ($this->segment(2) != '' and $this->input('_method') == 'PATCH') {
            $filter = ','.$this->segment(2);
        }

        return [
            'sede_id' => 'required',
            'rol_id' => 'required',
            'documento' => 'required|numeric',
            'genero' => 'required',
            'nombres' => 'required|min:2|max:250',
            'apellidos' => 'required|min:2|max:250',
            'username' => 'required|min:3|max:50|unique:usuarios,username'.$filter,
            'email' => 'required|email|unique:usuarios,email'.$filter,
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
