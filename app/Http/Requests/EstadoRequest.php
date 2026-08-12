<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EstadoRequest extends FormRequest
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

        return RequestUtils::havePermission('estados', $permission);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nombre' => 'required|min:2|max:250',
            'descripcion' => 'max:250',
        ];
    }
}
