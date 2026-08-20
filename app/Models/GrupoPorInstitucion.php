<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class GrupoPorInstitucion extends Model
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'grupos_por_instituciones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['institucion_id', 'grupo_de_preguntas_id', 'orden'];

    /**
     * Un GrupoPorInstitucion pertenece a una Institucion
     *
     * @return GrupoDePreguntas
     */
    public function institucion()
    {
        return $this->belongsTo(\App\Models\Institucion::class);
    }

    /**
     * Describe el Grupo de preguntas para la institucion
     *
     * @return GrupoDePreguntas
     */
    public function grupoDePreguntas()
    {
        return $this->belongsTo(\App\Models\GrupoDePreguntas::class);
    }

    /**
     * Obtiene el número de orden mayor para la institucion actual
     *
     * @return valor máximo de "orden" + 1
     */
    public function scopeOrdenActual($query)
    {
        $actual = $query->where('institucion_id', $this->institucion->id)->max('orden');
        if (! $actual) {
            return 1;
        }

        return $actual + 1;
    }
}
