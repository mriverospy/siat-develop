<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notificaciones';

    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = [
        'usuario_id',
        'fecha',
        'asunto',
        'mensaje',
        'leido',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'fecha' => 'datetime',
        'leido' => 'boolean',    ];

    /**
     * Campos de tipo fecha
     *
     * @var array
     */
    /**
     * Un Reporte pertenece a un Usuario
     *
     * @return Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class);
    }

    /**
     * Scope para notificaciones leídas
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLeidos($query)
    {
        return $query->where('leido', true);
    }

    /**
     * Scope para notificaciones leídas
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNoLeidos($query)
    {
        return $query->where('leido', false);
    }

    /**
     * Realiza el paginado para las listas de los ABM's
     * en caso que se le pase un filtro, filtra por los campos
     * establecidos de la forma 'campo' like '%filtro%'
     */
    public function scopeFilteredPaginate($query, $filter = '', $count = 10)
    {
        if (trim($filter) !== '') {
            $query->where('asunto', 'like', '%'.$filter.'%')
                    ->orWhere('mensaje', 'like', '%'.$filter.'%');
        }

        return $query->paginate($count);
    }
}
