<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SedeRequest extends FormRequest
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

        return RequestUtils::havePermission('sedes', $permission);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $verify = '';
        if (RequestUtils::isSysAdmin()) {
            $verify = 'required';
        }

        return [
            'institucion_id' => $verify,
            'nombre' => 'required|min:3|max:250',
            'descripcion' => 'max:250',
        ];
    }
}
