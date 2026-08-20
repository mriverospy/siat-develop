<?php

namespace App\Http\Requests;

use App\Models\Autorizacion;
use Auth;
use Illuminate\Foundation\Http\FormRequest;

class AccidenteRequest extends FormRequest
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

        // Verificar si hay autorizaciones para este accidente y usuario
        $autorizacion = Autorizacion::where('accidente_id', $this->accidentes)
            ->where('recibe_usuario_id', Auth::user()->id)
            ->where('fecha_inicio_autorizacion', '<=', date('Y-m-d'))
            ->where('fecha_fin_autorizacion', '>=', date('Y-m-d'))
            ->first();

        return RequestUtils::havePermission('accidentes', $permission) or ! is_null($autorizacion);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
