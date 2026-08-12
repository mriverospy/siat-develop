<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Departamento extends Model
{
    use RevisionableTrait;

    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = ['nombre'];

    protected $revisionCreationsEnabled = true;

    /**
     * Un Departamento tiene varios Distritos
     *
     * @return App\Models\Distrito
     */
    public function distritos()
    {
        return $this->hasMany(\App\Models\Distrito::class);
    }

    /**
     * Departamento tiene varias localidades a través de Distritos
     *
     * @return App\Models\Localidad
     */
    public function localidades()
    {
        return $this->hasManyThrough(\App\Models\Localidad::class, \App\Models\Distrito::class);
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
