<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Autorizacion extends Model
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'autorizaciones';

    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = [
        'accidente_id',
        'otorga_usuario_id',
        'recibe_usuario_id',
        'descripcion',
        'campos_autorizados',
        'fecha_autorizacion',
        'fecha_inicio_autorizacion',
        'fecha_fin_autorizacion',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'fecha_autorizacion' => 'datetime',
        'fecha_inicio_autorizacion' => 'datetime',
        'fecha_fin_autorizacion' => 'datetime',
        'campos_autorizados' => 'array',    ];

    /**
     * Una Autorización tiene un Accidente
     *
     * @return Accidente
     */
    public function accidente()
    {
        return $this->belongsTo(\App\Models\Accidente::class);
    }

    /**
     * Una Autorización tiene un Usuario que inicia la Autorización
     *
     * @return Usuario
     */
    public function usuario_emisor()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'otorga_usuario_id');
    }

    /**
     * Una Autorización tiene un Usuario que recibe la Autorización
     *
     * @return Usuario
     */
    public function usuario_receptor()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'recibe_usuario_id');
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
            $query->where('id', '=', $filter);
        }

        return $query->with([
            'accidente',
            'usuario_emisor',
            'usuario_emisor.sede',
            'usuario_emisor.sede.institucion',
            'usuario_receptor',
            'usuario_receptor.sede',
            'usuario_receptor.sede.institucion',
        ])->orderBy('created_at', 'desc')->paginate($count);
    }
}
