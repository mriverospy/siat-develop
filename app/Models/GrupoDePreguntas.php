<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class GrupoDePreguntas extends Model
{
    use HasFactory;
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'grupos_de_preguntas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre', 'descripcion', 'master', 'orden', 'puede_duplicar', 'visible'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'visible' => 'boolean',
        'master' => 'boolean',
        'puede_duplicar' => 'boolean',
    ];

    public function getTotalPreguntasAttribute()
    {
        $this->with('preguntas');

        return $this->preguntas->count();
    }

    /**
     * Un Grupo de Preguntas tiene una o varias preguntas
     *
     * @return listado de preguntas
     */
    public function preguntas()
    {
        return $this->hasMany(\App\Models\Pregunta::class);
    }

    /**
     * Relacion mucho a mucho con instituciones
     *
     * @return listado de instutuciones
     */
    public function instituciones()
    {
        return $this->belongsToMany(\App\Models\Institucion::class, 'grupos_por_instituciones', 'institucion_id', 'grupo_de_preguntas_id');
    }

    /**
     * Un Grupo de Preguntas tiene uno o varios grupos por institucion
     *
     * @return listado de grupos por institucion
     */
    public function grupoPorInstitucion()
    {
        return $this->hasMany(\App\Models\GrupoPorInstitucion::class);
    }

    /**
     * Realiza el paginado para las listas de los ABM's
     * en caso que se le pase un filtro, filtra por los campos
     * establecidos de la forma 'campo' like '%filtro%'
     */
    public function scopeFilteredPaginate($query, $filter = '', $count = 10)
    {
        if (trim($filter) !== '') {
            $query->where('nombre', 'like', '%'.$filter.'%')
                    ->orWhere('id', 'like', '%'.$filter.'%');
        }

        return $query->paginate($count);
    }

    /**
     * Obtiene el número de orden mayor para los grupos masters
     *
     * @return valor máximo de "orden" + 1
     */
    public function scopeOrdenActual($query)
    {
        $actual = $query->where('master', true)->max('orden');
        if (! $actual) {
            $actual = 0;
        }

        return $actual + 1;
    }
}
