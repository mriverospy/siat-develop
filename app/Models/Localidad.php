<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Localidad extends Model
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'localidades';

    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = [
        'distrito_id',
        'nombre',
        'descripcion',
    ];

    /**
     * Una Localidad pertenece a un Distrito
     *
     * @return Distrito
     */
    public function distrito()
    {
        return $this->belongsTo(\App\Models\Distrito::class);
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
                    ->orWhere('id', 'like', '%'.$filter.'%')
                    ->orWhereHas('distrito', function ($q) use ($filter) {
                        $q->where('nombre', 'like', '%'.$filter.'%');
                    })
                    ->orWhereHas('distrito.departamento', function ($q) use ($filter) {
                        $q->where('nombre', 'like', '%'.$filter.'%');
                    });
        }
        $query->with(['distrito', 'distrito.departamento']);

        return $query->paginate($count);
    }

    /**
     * Obtiene todas las localidades
     * Se usa este método para evitar 3 queries a la BD en la interfaz de carga de accidente
     *
     * @return [type] [description]
     */
    public function getLocalidadesFull()
    {
        return DB::select('
            SELECT
                localidades.id,
                localidades.nombre AS nombre_localidad,
                distritos.nombre AS nombre_distrito,
                departamentos.nombre AS nombre_departamento
            FROM localidades
            LEFT JOIN distritos ON distritos.id = localidades.distrito_id
            LEFT JOIN departamentos ON departamentos.id = distritos.departamento_id
            ORDER BY departamentos.orden, distritos.nombre, localidades.nombre
            ');
    }
}
