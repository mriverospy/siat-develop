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
            'codigo' => 'nullable|string|max:50',
            'activo' => 'sometimes|boolean',
            'tipo' => 'required|in:text,number,radio,check,date',
            'detalles' => 'prohibited',
        ];

        if (in_array($this->input('tipo'), ['radio', 'check'])) {
            if ($this->has('detalles')) {
                $rules['detalles'] = 'required|array|min:1';
                $rules['detalles.*.activo'] = 'sometimes|boolean';
                $rules['detalles.*.opcion'] = ['required', 'string', 'max:255', 'distinct:strict', 'not_regex:/[|]/'];
                $rules['detalles.*.codigo'] = ['nullable', 'string', 'max:50', 'distinct:ignore_case',
                    \Illuminate\Validation\Rule::unique('preguntas_detalles', 'codigo')
                        ->where(function ($query) {
                            $query->where('pregunta_id', $this->route('pregunta') ?? 0);
                            $ids = collect($this->input('detalles', []))->pluck('id')->filter()->all();
                            if ($ids) $query->whereNotIn('id', $ids);
                        })];
                $rules['detalles.*.id'] = ['nullable', 'integer', 'distinct',
                    \Illuminate\Validation\Rule::exists('preguntas_detalles', 'id')->where('pregunta_id', $this->route('pregunta') ?? $this->route('id') ?? 0)];
            } else {
                $rules['opciones'] = 'required|string';
            }
        }

        return $rules;
    }
}
