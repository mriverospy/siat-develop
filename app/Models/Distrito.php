<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Distrito extends Model
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = [
        'departamento_id',
        'nombre',
        'descripcion',
    ];

    /**
     * Un Distrito pertenece a un Departamento
     *
     * @return Departamento
     */
    public function departamento()
    {
        return $this->belongsTo(\App\Models\Departamento::class);
    }

    public function localidades()
    {
        return $this->hasMany(\App\Models\Localidad::class);
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
                  ->orWhereHas('departamento', function ($q) use ($filter) {
                      $q->where('nombre', 'like', '%'.$filter.'%');
                  });
            $query->with('departamento');
        }

        return $query->paginate($count);
    }
}
