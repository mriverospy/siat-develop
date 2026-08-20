<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revisions extends Model
{
    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'user_id', 'id');
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

            if (isset($filters['objeto']) and trim($filters['objeto']) != '') {
                $query->where('revisionable_type', 'like', '%'.trim($filters['objeto']).'%');
            }

            if (isset($filters['usuario']) and trim($filters['usuario']) != '') {
                $query->where('user_id', $filters['usuario']);
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($count);
    }
}
