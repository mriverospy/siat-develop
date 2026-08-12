<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Respuesta extends Model
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = [
        'pregunta_id',
        'accidente_id',
        'usuario_id',
        'agrupador',
        'pregunta_texto',
        'pregunta_tipo',
        'respuesta_texto',
        'respuesta_numero',
        'respuesta_fecha',
    ];

    protected $casts = [
        'respuesta_fecha' => 'datetime',
    ];

    /**
     * Una Respuesta pertenece a un Accidente
     *
     * @return Usuario
     */
    public function accidente()
    {
        return $this->belongsTo(\App\Models\Accidente::class);
    }

    /**
     * Una Respuesta pertenece a un Usuario
     *
     * @return Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class);
    }

    /**
     * Una Respuesta pertenece a una Pregunta
     *
     * @return Usuario
     */
    public function pregunta()
    {
        return $this->belongsTo(\App\Models\Pregunta::class);
    }
}
