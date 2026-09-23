<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Respuesta extends Model
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    protected static function booted()
    {
        static::saving(function (Respuesta $respuesta) {
            if (!$respuesta->exists && array_key_exists('codigo', $respuesta->getAttributes())) return;
            if (!$respuesta->exists || $respuesta->isDirty(['pregunta_id', 'respuesta_texto', 'pregunta_tipo'])) {
                $respuesta->codigo = static::codigoPara($respuesta->pregunta_id, $respuesta->respuesta_texto);
            }
        });
    }

    public static function codigoConservado($anteriores, $preguntaId, $texto)
    {
        $anterior = $anteriores->first(fn ($respuesta) => $respuesta->respuesta_texto === $texto);
        return $anterior ? $anterior->codigo : static::codigoPara($preguntaId, $texto);
    }

    public static function codigoPara($preguntaId, $texto)
    {
        if ($texto === null || $texto === '') return null;
        $pregunta = Pregunta::find($preguntaId);
        if (!$pregunta || !in_array($pregunta->tipo, ['radio', 'check'])) return null;
        // Comparación exacta en PHP, independiente de la collation de la base.
        $detalles = $pregunta->detalles()->where('activo', true)->get()
            ->filter(fn ($detalle) => $detalle->opcion === $texto);
        return $detalles->count() === 1 ? $detalles->first()->codigo : null;
    }

    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = [
        'pregunta_id',
        'codigo',
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
