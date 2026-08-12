<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DerivacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return RequestUtils::havePermission('accidentes', 'send');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'institucion_id' => 'required|exists:instituciones,id',
            'sede_id' => 'required|exists:sedes,id',
            'usuario_id' => 'required|exists:usuarios,id,activo,1',
        ];
    }
}
