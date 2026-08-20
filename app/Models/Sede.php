<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Sede extends Model
{
    use HasFactory;
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = [
        'institucion_id',
        'nombre',
        'descripcion',
    ];

    /**
     * Una Sede pertenece a una Institucion
     *
     * @return Institucion
     */
    public function institucion()
    {
        return $this->belongsTo(\App\Models\Institucion::class);
    }

    /**
     * Realiza el paginado para las listas de los ABM's
     * en caso que se le pase un filtro, filtra por los campos
     * establecidos de la forma 'campo' like '%filtro%'
     */
    public function scopeFilteredPaginate($query, $filter = '', $count = 10)
    {
        //CHECK USER INSTITUTION
        if (! Auth::user()->hasRole('sysadmin')) {
            $query->where('institucion_id', '=', Auth::user()->sede->institucion->id);
        }

        //ADDING FILTER OPTIONS
        if (trim($filter) !== '') {
            $query->where(function ($q) use ($filter) {
                $q->where('nombre', 'like', '%'.$filter.'%')
                      ->orWhere('id', 'like', '%'.$filter.'%')
                      ->orWhereHas('institucion', function ($q) use ($filter) {
                          $q->where('nombre', 'like', '%'.$filter.'%');
                      });
            });
        }
        $query->with('institucion');
        $query->orderBy('nombre');

        return $query->paginate($count);
    }

    public function scopeSedeList($query)
    {
        $query->join('instituciones as i', 'i.id', '=', 'sedes.institucion_id')
                ->selectRaw('CONCAT(i.nombre, " - ", sedes.nombre) as nombres, sedes.id');

        //CHECK USER INSTITUTION
        if (! Auth::user()->hasRole('sysadmin')) {
            $query->where('institucion_id', '=', Auth::user()->sede->institucion->id);
        }

        $query->orderBy('i.nombre')
                ->orderBy('sedes.nombre');
    }
}
