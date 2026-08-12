<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocalidadRequest extends FormRequest
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

        return RequestUtils::havePermission('localidades', $permission);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'distrito_id' => 'required',
            'nombre' => 'required|min:3|max:250',
            'descripcion' => 'max:250',
        ];
    }
}
