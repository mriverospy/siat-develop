<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = [
        'usuario_id',
        'accion',
        'direccion_ip',
    ];

    /**
     * Un Login pertenece a un Usuario
     *
     * @return Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class);
    }

    /**
     * Realiza el paginado para las listas de los ABM's
     * en caso que se le pase un filtro, filtra por los campos
     * establecidos de la forma 'campo' like '%filtro%'
     */
    public function scopeFilteredPaginate($query, $filters = [], $count = 10)
    {
        $query->with('usuario');

        if (count($filters) > 0) {
            if (
                isset($filters['fecha_inicio']) and trim($filters['fecha_inicio']) != ''
                and
                isset($filters['fecha_fin']) and trim($filters['fecha_fin']) != '') {
                $query->where('created_at', '>=', $filters['fecha_inicio'].' 00:00:00');
                $query->where('created_at', '<=', $filters['fecha_fin'].' 23:59:59');
            }

            if (isset($filters['accion']) and trim($filters['accion']) != '') {
                $query->where('accion', 'like', '%'.trim($filters['accion']).'%');
            }

            if (isset($filters['usuario']) and trim($filters['usuario']) != '') {
                $query->where('usuario_id', $filters['usuario']);
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($count);
    }
}
