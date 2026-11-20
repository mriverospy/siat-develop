<?php

namespace App\Console\Commands;

use App\Models\Accidente;
use App\Models\Pregunta;
use App\Services\Bloque24Horas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class MigrarBloque24Historico extends Command
{
    protected $signature = 'siat:migrar-bloque-historico {--aplicar : Guardar cambios; por defecto solo simula} {--desde=1 : ID inicial} {--hasta= : ID final}';
    protected $description = 'Completa el Bloque 24 horas histórico sin modificar respuestas existentes';

    public function handle(): int
    {
        $codigos = array_keys(config('bloque_24_horas.preguntas'));
        $preguntas = Pregunta::whereHas('grupoDePreguntas', fn ($q) => $q->where('nombre', Bloque24Horas::NOMBRE))
            ->whereIn('codigo', $codigos)->get();
        if ($preguntas->count() !== count($codigos) || $preguntas->pluck('codigo')->unique()->count() !== count($codigos)) {
            $this->error('El catálogo del Bloque 24 horas está incompleto o duplicado.');
            return 1;
        }
        $ids = $preguntas->pluck('id')->all();
        $aplicar = (bool) $this->option('aplicar');
        $totales = ['revisados' => 0, 'completados' => 0, 'sin_cambios' => 0, 'con_alertas' => 0, 'conflictos' => 0];
        $query = Accidente::query()->where('id', '>=', (int) $this->option('desde'));
        if ($this->option('hasta') !== null) $query->where('id', '<=', (int) $this->option('hasta'));
        $nombresPorId = $preguntas->pluck('codigo', 'id');
        $query->orderBy('id')->chunkById(200, function ($accidentes) use ($ids, $nombresPorId, $aplicar, &$totales) {
            foreach ($accidentes as $accidente) {
                $totales['revisados']++;
                DB::beginTransaction();
                try {
                    // El bloqueo también protege contra edición simultánea de la cabecera.
                    $accidente = Accidente::whereKey($accidente->id)->lockForUpdate()->firstOrFail();
                    $antes = DB::table('respuestas')->where('accidente_id', $accidente->id)->whereIn('pregunta_id', $ids)
                        ->lockForUpdate()->orderBy('id')->get()->keyBy('id');
                    $codigoAntes = $accidente->codigo_siat;
                    app(Bloque24Horas::class)->sincronizar($accidente);
                    $despues = DB::table('respuestas')->where('accidente_id', $accidente->id)->whereIn('pregunta_id', $ids)
                        ->orderBy('id')->get()->keyBy('id');
                    foreach ($antes as $id => $respuesta) {
                        // No aceptar ninguna modificación de respuestas previas, incluida auditoría.
                        if (!isset($despues[$id]) || (array) $respuesta !== (array) $despues[$id]) {
                            throw new \RuntimeException('Una respuesta existente difiere de la cabecera; requiere conciliación.');
                        }
                    }
                    $alertas = [];
                    foreach ($despues as $respuesta) {
                        $codigo = $nombresPorId[$respuesta->pregunta_id];
                        if (($respuesta->respuesta_texto === null || trim($respuesta->respuesta_texto) === '') && $respuesta->respuesta_fecha === null && $respuesta->respuesta_numero === null) $alertas[] = 'valor faltante: '.$codigo;
                        if (in_array($codigo, ['APY_01', 'A_10', 'A_11'], true) && ($respuesta->codigo === null || $respuesta->codigo === '')) $alertas[] = 'código no reconocido: '.$codigo;
                        if (in_array($codigo, ['A_8', 'A_9'], true) && (!is_numeric($respuesta->respuesta_texto) || ($codigo === 'A_8' && abs((float) $respuesta->respuesta_texto) > 90) || ($codigo === 'A_9' && abs((float) $respuesta->respuesta_texto) > 180))) $alertas[] = 'coordenada inválida: '.$codigo;
                    }
                    if ($alertas) {
                        $totales['con_alertas']++;
                        $this->warn('Accidente '.$accidente->id.': '.implode('; ', $alertas));
                    }
                    $cambio = !$codigoAntes || $despues->count() > $antes->count();
                    $totales[$cambio ? 'completados' : 'sin_cambios']++;
                    if ($aplicar) DB::commit(); else DB::rollBack();
                } catch (Throwable $e) {
                    DB::rollBack();
                    $totales['conflictos']++;
                    $this->error('Accidente '.$accidente->id.': '.$e->getMessage());
                }
            }
        });
        $this->line(($aplicar ? 'Aplicación' : 'Simulación').' — '.json_encode($totales, JSON_UNESCAPED_UNICODE));
        return $totales['conflictos'] || $totales['con_alertas'] ? 1 : 0;
    }
}
