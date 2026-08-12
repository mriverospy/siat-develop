<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Estado extends Model
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Un estado tiene muchos accidentes
     *
     * @return
     */
    public function accidentes()
    {
        return $this->hasMany(\App\Models\Accidente::class);
    }

    /**
     * Realiza el paginado para las listas de los ABM's
     * en caso que se le pase un filtro, filtra por los campos
     * establecidos de la forma 'campo' like '%filtro%'
     */
    public function scopeFilteredPaginate($query, $filter = '', $count = 10)
    {
        if ($filter !== '') {
            $query->where('nombre', 'like', '%'.$filter.'%')
                  ->orWhere('id', 'like', '%'.$filter.'%')
                  ->orWhere('descripcion', 'like', '%'.$filter.'%');
        }

        return $query->paginate($count);
    }
}
