<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Pregunta extends Model
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'preguntas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'grupo_de_preguntas_id',
        'pregunta',
        'orden',
        'tipo',
        'opciones',
        'instrucciones',
        'mostrar_otro',
        'titulo_otro',
        'mostrar_selector',
        'obligatorio',
    ];

    /**
     * Una Pregunta pertenece a un Grupo de Preguntas
     *
     * @return GrupoDePreguntas
     */
    public function grupoDePreguntas()
    {
        return $this->belongsTo(\App\Models\GrupoDePreguntas::class);
    }

    /**
     * Una pregunta tiene muchas respuestas
     *
     * @return Respuesta
     */
    public function respuestas()
    {
        return $this->hasMany(\App\Models\Respuesta::class);
    }

    /**
     * Obtiene el número de orden mayor para la pregunta actual
     *
     * @return valor máximo de "orden" + 1
     */
    public function scopeOrdenActual($query)
    {
        $actual = $query->max('orden');
        if (! $actual) {
            $actual = 0;
        }

        return $actual + 1;
    }

    /**
     * Realiza el paginado para las listas de los ABM's
     * en caso que se le pase un filtro, filtra por los campos
     * establecidos de la forma 'campo' like '%filtro%'
     */
    public function scopeFilteredPaginate($query, $filter = '', $count = 10)
    {
        if (trim($filter) !== '') {
            $query->where('pregunta', 'like', '%'.$filter.'%')
                    ->orWhere('tipo', 'like', '%'.$filter.'%')
                    ->orWhere('opciones', 'like', '%'.$filter.'%')
                    ->orWhere('id', 'like', '%'.$filter.'%');
        }

        return $query->paginate($count);
    }
}
