<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreguntaDetalle extends Model
{
    protected $table = 'preguntas_detalles';
    protected $fillable = ['opcion', 'codigo', 'orden', 'activo'];
    protected $casts = ['activo' => 'boolean'];

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class);
    }
}
