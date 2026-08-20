<?php

namespace Database\Seeders;

use App\Models\Accidente;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RespuestasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Usuario a asignar
        $usuario = Usuario::with(['sede', 'sede.institucion'])->where('username', '=', 'policia_usuario')->first();

        // Agregar Accidente
        $accidente = Accidente::create([
            'caratula' => 'Choque de Motos en Las Carmelitas',
            'expediente_judicial' => '3252/15',
            'sumario_policial' => '7362',
            'otro' => '4023',
            'dependencia' => '4ta. Compañía',
            'fecha_accidente' => Carbon::now()->subDays(8)->toDateTimeString(),
            'fecha_apertura' => Carbon::now()->subDays(8)->toDateString().' 17:44:56',
            'fecha_cierre' => Carbon::now()->subDays(2)->toDateString().' 17:44:56',
            'lugar' => 'Boqueron esquina Isla Poi',
            'altura' => 600,
            'tipo_camino' => 'calle',
            'localidad_id' => 2396,
            'codigo_postal' => null,
            'gps_latitud' => '-27.337134400000004',
            'gps_longitud' => '-55.8505767',
            'heridos' => 2,
            'fallecidos' => 0,
            'ilesos' => 0,
            'participantes_siniestro' => 2,
            'estado_id' => 1,
            'usuario_id' => $usuario->id,
            'sede_id' => $usuario->sede->id,
            'institucion_id' => $usuario->sede->institucion->id,
        ]);

        /**
         * Agregar Respuestas
         */

        // Tipo de Siniestro
        $accidente->respuestas()->create([
            'pregunta_id' => 1,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Tipo de Siniestro',
            'pregunta_tipo' => 'check',
            'respuesta_texto' => 'Choque frontal',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);
        $accidente->respuestas()->create([
            'pregunta_id' => 1,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Tipo de Siniestro',
            'pregunta_tipo' => 'check',
            'respuesta_texto' => 'Caida de Motociclista',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Caída del Vehículo
        $accidente->respuestas()->create([
            'pregunta_id' => 2,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Caída del Vehículo',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'No se registra',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
            
        ]);

        // Posibles Factores Causales del Accidente de Transito
        $accidente->respuestas()->create([
            'pregunta_id' => 3,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Posibles Factores Causales del Accidente de Transito',
            'pregunta_tipo' => 'check',
            'respuesta_texto' => 'Exceso de velocidad',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Imprudencia de Peatones
        $accidente->respuestas()->create([
            'pregunta_id' => 4,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Imprudencia de Peatones',
            'pregunta_tipo' => 'check',
            'respuesta_texto' => 'No hubo imprudencia de peatones',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Elementos Distractores
        $accidente->respuestas()->create([
            'pregunta_id' => 5,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Elementos Distractores',
            'pregunta_tipo' => 'check',
            'respuesta_texto' => 'No se registran elementos distractores',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Lugar de la Vía Pública
        $accidente->respuestas()->create([
            'pregunta_id' => 6,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Lugar de la Vía Pública',
            'pregunta_tipo' => 'check',
            'respuesta_texto' => 'Recta',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Estado de la Vía
        $accidente->respuestas()->create([
            'pregunta_id' => 7,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Estado de la Vía',
            'pregunta_tipo' => 'check',
            'respuesta_texto' => 'Bueno',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Superficie de la Vía
        $accidente->respuestas()->create([
            'pregunta_id' => 8,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Superficie de la Vía',
            'pregunta_tipo' => 'check',
            'respuesta_texto' => 'Asfalto',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Tiempo
        $accidente->respuestas()->create([
            'pregunta_id' => 9,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Tiempo',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'Bueno',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Luminosidad
        $accidente->respuestas()->create([
            'pregunta_id' => 10,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Luminosidad',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'Día',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Vía pública dividida por
        $accidente->respuestas()->create([
            'pregunta_id' => 11,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Vía pública dividida por',
            'pregunta_tipo' => 'check',
            'respuesta_texto' => 'Ninguno',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Tránsito restringido por
        $accidente->respuestas()->create([
            'pregunta_id' => 12,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Tránsito restringido por',
            'pregunta_tipo' => 'check',
            'respuesta_texto' => 'Sin restricción',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Señalización
        $accidente->respuestas()->create([
            'pregunta_id' => 13,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Señalización',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'Completa',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Semáforo
        $accidente->respuestas()->create([
            'pregunta_id' => 14,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Semáforo',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'Funcionaba',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Visibilidad
        $accidente->respuestas()->create([
            'pregunta_id' => 15,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Visibilidad',
            'pregunta_tipo' => 'check',
            'respuesta_texto' => 'Buena',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Prioridad regulada por
        $accidente->respuestas()->create([
            'pregunta_id' => 16,
            'usuario_id' => $usuario->id,
            'agrupador' => 0,
            'pregunta_texto' => 'Prioridad regulada por',
            'pregunta_tipo' => 'check',
            'respuesta_texto' => 'Ninguna',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Vehículo Nro.
        $accidente->respuestas()->create([
            'pregunta_id' => 17,
            'usuario_id' => $usuario->id,
            'agrupador' => 1,
            'pregunta_texto' => 'Vehículo Nro.',
            'pregunta_tipo' => 'number',
            'respuesta_texto' => null,
            'respuesta_numero' => 1,
            'respuesta_fecha' => null,
        ]);

        // Número de Ocupantes
        $accidente->respuestas()->create([
            'pregunta_id' => 18,
            'usuario_id' => $usuario->id,
            'agrupador' => 1,
            'pregunta_texto' => 'Número de Ocupantes',
            'pregunta_tipo' => 'number',
            'respuesta_texto' => null,
            'respuesta_numero' => 1,
            'respuesta_fecha' => null,
        ]);

        // Conductor Propietario
        $accidente->respuestas()->create([
            'pregunta_id' => 19,
            'usuario_id' => $usuario->id,
            'agrupador' => 1,
            'pregunta_texto' => 'Conductor Propietario',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'Si',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Chapa
        $accidente->respuestas()->create([
            'pregunta_id' => 21,
            'usuario_id' => $usuario->id,
            'agrupador' => 1,
            'pregunta_texto' => 'Chapa',
            'pregunta_tipo' => 'text',
            'respuesta_texto' => 'FGE345',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Marca
        $accidente->respuestas()->create([
            'pregunta_id' => 22,
            'usuario_id' => $usuario->id,
            'agrupador' => 1,
            'pregunta_texto' => 'Marca',
            'pregunta_tipo' => 'text',
            'respuesta_texto' => 'Yamaha',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Color
        $accidente->respuestas()->create([
            'pregunta_id' => 26,
            'usuario_id' => $usuario->id,
            'agrupador' => 1,
            'pregunta_texto' => 'Color',
            'pregunta_tipo' => 'text',
            'respuesta_texto' => 'Verde oscuro',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Transportado en vehículo Nro
        $accidente->respuestas()->create([
            'pregunta_id' => 32,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Transportado en vehículo Nro',
            'pregunta_tipo' => 'number',
            'respuesta_texto' => null,
            'respuesta_numero' => 1,
            'respuesta_fecha' => null,
        ]);

        // Ubicación en Vehículo
        $accidente->respuestas()->create([
            'pregunta_id' => 33,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Ubicación en Vehículo',
            'pregunta_tipo' => 'number',
            'respuesta_texto' => null,
            'respuesta_numero' => 1,
            'respuesta_fecha' => null,
        ]);

        // Estado
        $accidente->respuestas()->create([
            'pregunta_id' => 36,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Estado',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'Herido',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Documento
        $accidente->respuestas()->create([
            'pregunta_id' => 37,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Documento',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'CI',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Número
        $accidente->respuestas()->create([
            'pregunta_id' => 38,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Número',
            'pregunta_tipo' => 'text',
            'respuesta_texto' => '7654321',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Emisor
        $accidente->respuestas()->create([
            'pregunta_id' => 39,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Emisor',
            'pregunta_tipo' => 'text',
            'respuesta_texto' => 'Policia Nacional',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Apellidos
        $accidente->respuestas()->create([
            'pregunta_id' => 40,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Apellidos',
            'pregunta_tipo' => 'text',
            'respuesta_texto' => 'Benedetti',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Nombres
        $accidente->respuestas()->create([
            'pregunta_id' => 41,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Nombres',
            'pregunta_tipo' => 'text',
            'respuesta_texto' => 'Lorenzo',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Fecha de Nacimiento
        $accidente->respuestas()->create([
            'pregunta_id' => 42,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Fecha de Nacimiento',
            'pregunta_tipo' => 'date',
            'respuesta_texto' => null,
            'respuesta_numero' => null,
            'respuesta_fecha' => '1990-01-09 00:00:00',
        ]);

        // Sexo
        $accidente->respuestas()->create([
            'pregunta_id' => 43,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Sexo',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'Masculino',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Estado Civil
        $accidente->respuestas()->create([
            'pregunta_id' => 44,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Estado Civil',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'Soltero',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Nacionalidad
        $accidente->respuestas()->create([
            'pregunta_id' => 45,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Nacionalidad',
            'pregunta_tipo' => 'text',
            'respuesta_texto' => 'Paraguaya',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Cobertura Médica
        $accidente->respuestas()->create([
            'pregunta_id' => 55,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Cobertura Médica',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'Si',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Uso chaleco reflectivo
        $accidente->respuestas()->create([
            'pregunta_id' => 57,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Uso chaleco reflectivo',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'Si',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Alcotest Positivo
        $accidente->respuestas()->create([
            'pregunta_id' => 58,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Alcotest Positivo',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'No',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Uso casco
        $accidente->respuestas()->create([
            'pregunta_id' => 59,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Uso casco',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'Si',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Licencia conductor
        $accidente->respuestas()->create([
            'pregunta_id' => 60,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Licencia conductor',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'Si',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Habilitación
        $accidente->respuestas()->create([
            'pregunta_id' => 61,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Habilitación',
            'pregunta_tipo' => 'radio',
            'respuesta_texto' => 'Si',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Clase
        $accidente->respuestas()->create([
            'pregunta_id' => 62,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Clase',
            'pregunta_tipo' => 'text',
            'respuesta_texto' => 'Particular',
            'respuesta_numero' => null,
            'respuesta_fecha' => null,
        ]);

        // Número
        $accidente->respuestas()->create([
            'pregunta_id' => 63,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Número',
            'pregunta_tipo' => 'number',
            'respuesta_texto' => null,
            'respuesta_numero' => 4235,
            'respuesta_fecha' => null,
        ]);

        // Fecha de Expedición
        $accidente->respuestas()->create([
            'pregunta_id' => 64,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Fecha de Expedición',
            'pregunta_tipo' => 'date',
            'respuesta_texto' => null,
            'respuesta_numero' => null,
            'respuesta_fecha' => '2015-01-01 00:00:00',
        ]);

        // Fecha de Vencimiento
        $accidente->respuestas()->create([
            'pregunta_id' => 65,
            'usuario_id' => $usuario->id,
            'agrupador' => 2,
            'pregunta_texto' => 'Fecha de Vencimiento',
            'pregunta_tipo' => 'date',
            'respuesta_texto' => null,
            'respuesta_numero' => null,
            'respuesta_fecha' => '2016-01-02 00:00:00',
        ]);
    }
}
