<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Institucion extends Model
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'instituciones';

    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'dias_edicion',
        'logo',
        'grupos_agrupados',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'dias_edicion' => 'integer',
        'grupos_agrupados' => 'array',
    ];

    /**
     * Una Institucion tiene una o varias sedes
     *
     * @return listado de sedes
     */
    public function sedes()
    {
        return $this->hasMany(\App\Models\Sede::class);
    }

    /**
     * Una Institucion tiene uno o varios grupos por institucion
     *
     * @return listado de grupos por institucion
     */
    public function gruposPorInstitucion()
    {
        return $this->hasMany(\App\Models\GrupoPorInstitucion::class);
    }

    /**
     * Relacion mucho a mucho con grupo de preguntas
     *
     * @return listado de grupos de preguntas
     */
    public function grupoDePreguntas()
    {
        return $this
                ->belongsToMany(\App\Models\GrupoDePreguntas::class, 'grupos_por_instituciones', 'institucion_id', 'grupo_de_preguntas_id')
                ->withPivot('orden')
                ->withTimestamps();
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
}
