<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Pregunta extends Model
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    protected static function booted()
    {
        static::saved(function (Pregunta $pregunta) {
            if ($pregunta->wasChanged('opciones') || $pregunta->wasRecentlyCreated) {
                $pregunta->sincronizarDetallesLegacy();
            }
        });
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function permiteRespuesta($valor): bool
    {
        if (!$this->activo) return false;
        if (!in_array($this->tipo, ['radio', 'check'])) return true;
        $valor = trim((string) $valor);
        // La obligatoriedad se valida por separado; vacío no selecciona una opción.
        if ($valor === '') return true;
        $detalles = $this->detalles;
        $coincidentes = $detalles->filter(fn ($detalle) => trim($detalle->opcion) === $valor);
        if ($coincidentes->isNotEmpty()) return $coincidentes->contains(fn ($detalle) => $detalle->activo);
        // Compatibilidad con catálogos anteriores aún sin detalles.
        if ($detalles->isEmpty() && in_array($valor, explode('|', $this->opciones ?? ''), true)) return true;
        return (bool) $this->mostrar_otro;
    }

    public function detalles()
    {
        return $this->hasMany(PreguntaDetalle::class)->orderBy('orden')->orderBy('id');
    }

    public function sincronizarDetallesLegacy()
    {
        if (!in_array($this->tipo, ['radio', 'check'])) return;
        $existentes = $this->detalles()->get();
        $filas = [];
        foreach (explode('|', $this->opciones ?? '') as $opcion) {
            if ($opcion === '') continue;
            $detalle = $existentes->first(fn ($detalle) => $detalle->opcion === $opcion);
            $filas[] = ['id' => $detalle->id ?? null, 'opcion' => $opcion, 'codigo' => $detalle->codigo ?? null];
        }
        $this->guardarDetalles($filas);
    }

    public function guardarDetalles(array $filas)
    {
        $this->getConnection()->transaction(function () use ($filas) {
            $existentes = $this->detalles()->get()->keyBy('id');
            // Libera códigos para permitir intercambiarlos dentro del mismo guardado.
            $this->detalles()->whereIn('id', array_filter(array_column($filas, 'id')))->update(['codigo' => null]);
            $this->detalles()->update(['activo' => false]);
            foreach ($filas as $orden => $fila) {
                $detalle = !empty($fila['id']) ? $existentes->get($fila['id']) : new PreguntaDetalle;
                if (!$detalle) throw new \InvalidArgumentException('Opción ajena a la pregunta.');
                if ($detalle->exists) {
                    $detalle->codigo = null;
                    $detalle->activo = false;
                    $detalle->syncOriginalAttributes(['codigo', 'activo']);
                }
                $detalle->fill([
                    'opcion' => $fila['opcion'], 'codigo' => ($fila['codigo'] ?? '') === '' ? null : $fila['codigo'],
                    'orden' => $orden + 1, 'activo' => (bool) ($fila['activo'] ?? true),
                ]);
                $this->detalles()->save($detalle);
            }
            $this->opciones = implode('|', array_column(array_filter($filas, fn ($fila) => (bool) ($fila['activo'] ?? true)), 'opcion'));
            $this->saveQuietly();
            $this->unsetRelation('detalles');
        });
    }

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'preguntas';

    protected $casts = ['activo' => 'boolean'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'grupo_de_preguntas_id',
        'pregunta',
        'codigo',
        'activo',
        'orden',
        'tipo',
        'opciones',
        'instrucciones',
        'mostrar_otro',
        'titulo_otro',
        'mostrar_selector',
        'obligatorio',
    ];

    /**
     * Una Pregunta pertenece a un Grupo de Preguntas
     *
     * @return GrupoDePreguntas
     */
    public function grupoDePreguntas()
    {
        return $this->belongsTo(\App\Models\GrupoDePreguntas::class);
    }

    /**
     * Una pregunta tiene muchas respuestas
     *
     * @return Respuesta
     */
    public function respuestas()
    {
        return $this->hasMany(\App\Models\Respuesta::class);
    }

    /**
     * Obtiene el número de orden mayor para la pregunta actual
     *
     * @return valor máximo de "orden" + 1
     */
    public function scopeOrdenActual($query)
    {
        $actual = $query->max('orden');
        if (! $actual) {
            $actual = 0;
        }

        return $actual + 1;
    }

    /**
     * Realiza el paginado para las listas de los ABM's
     * en caso que se le pase un filtro, filtra por los campos
     * establecidos de la forma 'campo' like '%filtro%'
     */
    public function scopeFilteredPaginate($query, $filter = '', $count = 10)
    {
        if (trim($filter) !== '') {
            $query->where('pregunta', 'like', '%'.$filter.'%')
                    ->orWhere('tipo', 'like', '%'.$filter.'%')
                    ->orWhere('opciones', 'like', '%'.$filter.'%')
                    ->orWhere('id', 'like', '%'.$filter.'%');
        }

        return $query->paginate($count);
    }
}
