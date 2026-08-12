<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Derivacion extends Model
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'derivaciones';

    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = [
        'institucion_id',
        'sede_id',
        'usuario_id',
        'deriva_a_institucion_id',
        'deriva_a_sede_id',
        'deriva_a_usuario_id',
        'fecha_derivacion',
        'estado',
    ];

    protected $casts = [
        'fecha_derivacion' => 'datetime',
        'fecha_aceptacion' => 'datetime',
    ];

    /**
     * Una Derivación tiene un Accidente
     *
     * @return Accidente
     */
    public function accidente()
    {
        return $this->belongsTo(\App\Models\Accidente::class);
    }

    /**
     * Una Derivación tiene un Institucion que inicia la derivación
     *
     * @return Institución
     */
    public function institucion_emisor()
    {
        return $this->belongsTo(\App\Models\Institucion::class, 'institucion_id');
    }

    /**
     * Una Derivación tiene un Institucion que recibe la derivación
     *
     * @return Institución
     */
    public function institucion_receptor()
    {
        return $this->belongsTo(\App\Models\Institucion::class, 'deriva_a_institucion_id');
    }

    /**
     * Una Derivación tiene un Sede que inicia la derivación
     *
     * @return Sede
     */
    public function sede_emisor()
    {
        return $this->belongsTo(\App\Models\Sede::class, 'sede_id');
    }

    /**
     * Una Derivación tiene un Sede que recibe la derivación
     *
     * @return Sede
     */
    public function sede_receptor()
    {
        return $this->belongsTo(\App\Models\Sede::class, 'deriva_a_sede_id');
    }

    /**
     * Una Derivación tiene un Usuario que inicia la derivación
     *
     * @return Usuario
     */
    public function usuario_emisor()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'usuario_id');
    }

    /**
     * Una Derivación tiene un Usuario que recibe la derivación
     *
     * @return Usuario
     */
    public function usuario_receptor()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'deriva_a_usuario_id');
    }

    /**
     * Realiza el paginado para las listas de los ABM's
     * en caso que se le pase un filtro, filtra por los campos
     * establecidos de la forma 'campo' like '%filtro%'
     */
    public function scopeFilteredPaginate($query, $filter = '', $count = 10)
    {
        if (trim($filter) != '') {
            $txt = str_replace(' ', '%', $filter);
            $txt = '%'.$txt.'%';
            $query->where('id', '=', $filter)
                  ->orWhere('fecha_derivacion', '=', $txt)
                  ->orWhere('estado', 'like', $txt);
        }

        return $query->with(['accidente', 'institucion_receptor', 'sede_receptor', 'usuario_receptor'])->latest()->paginate($count);
    }
}
