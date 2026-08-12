<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreguntaRequest extends FormRequest
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

        return RequestUtils::havePermission('preguntas', $permission);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'pregunta' => 'required|min:3|max:250',
            'tipo' => 'required|min:3|max:250',
        ];

        if (in_array($this->input('tipo'), ['radio', 'check'])) {
            $rules['opciones'] = 'required';
        }

        return $rules;
    }
}
