<?php

namespace Database\Seeders;

use App\Models\GrupoDePreguntas;
use App\Models\Institucion;
use Illuminate\Database\Seeder;

class PreguntasYGruposSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $grupoIndex = 1;

        /**
         * Grupo 1
         * TIPO, PARTICIPANTES Y CAUSALES DEL SINIESTRO
         */
        $grupo1 = GrupoDePreguntas::create([
            'nombre' => 'Tipo, Participantes y Causales del Siniestro',
            'descripcion' => '',
            'master' => 1,
            'orden' => $grupoIndex,
        ]);
        $grupoIndex++;

        $grupo1->preguntas()->create([
            'pregunta' => 'Tipo de Siniestro',
            'orden' => 1,
            'tipo' => 'check',
            'opciones' => 'Roce de vehiculos|Vuelco|Caida de Motociclista|Choque frontal|Perdida de rueda|Incendio|Choque múltiple|Despiste|Arrollamiento Peaton|Arollamiento de animal|Rotura de Parabrisas|Arrollamiento ciclista',
        ]);

        $grupo1->preguntas()->create([
            'pregunta' => 'Caída del Vehículo',
            'orden' => 2,
            'tipo' => 'radio',
            'opciones' => 'No se registra|Al arroyo|A zanja o cuneta',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        $grupo1->preguntas()->create([
            'pregunta' => 'Posibles Factores Causales del Accidente de Transito',
            'orden' => 3,
            'tipo' => 'check',
            'opciones' => 'Visión obstaculizada|Adelantamiento Indebido|Animales sueltos en ruta|Circular sin luz encendida|Conducir en estado etilico|Cruzar semaforo en rojo|Desperfectos de ruta|Dormir al volante|Giro Indebido|Estacionamiento inapropiado|Via en reparación|Arbol caido en via publica|Torres electricas caidas|Encandilamiento|Choque por obstaculos|Exceso de velocidad|Giro indebido|Neblina|No conservar distancia|Pista mojada o resvaladiza|Reventón de cubierta',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        $grupo1->preguntas()->create([
            'pregunta' => 'Imprudencia de Peatones',
            'orden' => 4,
            'tipo' => 'check',
            'opciones' => 'Cruzar la via sin precausión|No utilizar paso de peatones|No respetar señal de semaforo|No hubo imprudencia de peatones',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        $grupo1->preguntas()->create([
            'pregunta' => 'Elementos Distractores',
            'orden' => 5,
            'tipo' => 'check',
            'opciones' => 'Manipulación de artefactos (terere, mate, comer)|Uso del celular al volante|No se registran elementos distractores',
            'mostrar_otro' => 0,
            'titulo_otro' => 'Otro',
        ]);

        /**
         * Grupo 2
         * CONDICIONES DEL TERRENO Y CLIMA
         */
        $grupo2 = GrupoDePreguntas::create([
            'nombre' => 'Condiciones del Terreno y Climatológicas',
            'descripcion' => '',
            'master' => 1,
            'orden' => $grupoIndex,
        ]);
        $grupoIndex++;

        $grupo2->preguntas()->create([
            'pregunta' => 'Lugar de la Vía Pública',
            'orden' => 1,
            'tipo' => 'check',
            'opciones' => 'Recta|Curva|Intersección|Rotonda|Distribuidor|Paso a Nivel FF.CC.|Puente|Pendiente',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        $grupo2->preguntas()->create([
            'pregunta' => 'Estado de la Vía',
            'orden' => 2,
            'tipo' => 'check',
            'opciones' => 'Bueno|Baches|Ahuellamiento|Mojado|Con Hielo|Banquina inutilizable|Resbaladizo',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        $grupo2->preguntas()->create([
            'pregunta' => 'Superficie de la Vía',
            'orden' => 3,
            'tipo' => 'check',
            'opciones' => 'Hormigón|Asfalto|Ripio|Tierra|Arena',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        $grupo2->preguntas()->create([
            'pregunta' => 'Tiempo',
            'orden' => 4,
            'tipo' => 'radio',
            'opciones' => 'Bueno|Nublado|Llovizna|Lluvia|Granizo|Vientos Fuertes',
            'mostrar_otro' => 0,
            'titulo_otro' => 'Otro',
        ]);

        $grupo2->preguntas()->create([
            'pregunta' => 'Luminosidad',
            'orden' => 5,
            'tipo' => 'radio',
            'opciones' => 'Día|Noche|Amanecer|Atardecer|Luz Artificial',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        /**
         * Grupo 3
         * TIPO, PARTICIPANTES Y CAUSALES DEL SINIESTRO
         */
        $grupo3 = GrupoDePreguntas::create([
            'nombre' => 'Estado y Señalizaciones del lugar',
            'descripcion' => '',
            'master' => 1,
            'orden' => $grupoIndex,
        ]);
        $grupoIndex++;

        $grupo3->preguntas()->create([
            'pregunta' => 'Vía pública dividida por',
            'orden' => 1,
            'tipo' => 'check',
            'opciones' => 'Barrera hormigón|Guardarrail|Barrera alto impacto|Cordón divisorio|Vegetación|Línea div. pintada|Ninguno',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        $grupo3->preguntas()->create([
            'pregunta' => 'Tránsito restringido por',
            'orden' => 2,
            'tipo' => 'check',
            'opciones' => 'Sin restricción|Obra en construcción|Desvío provisorio|Reparación de vía|Accidente previo',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        $grupo3->preguntas()->create([
            'pregunta' => 'Señalización',
            'orden' => 3,
            'tipo' => 'radio',
            'opciones' => 'Completa|Incompleta|Nula',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        $grupo3->preguntas()->create([
            'pregunta' => 'Semáforo',
            'orden' => 4,
            'tipo' => 'radio',
            'opciones' => 'Funcionaba|No Funcionaba|Intermitente|Sin semáforo',
            'mostrar_otro' => 0,
            'titulo_otro' => 'Otro',
        ]);

        $grupo3->preguntas()->create([
            'pregunta' => 'Visibilidad',
            'orden' => 5,
            'tipo' => 'check',
            'opciones' => 'Buena|Reducida por Niebla|Reducida por Polvo|Reducida por Humo|Reducida por Tormenta',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        $grupo3->preguntas()->create([
            'pregunta' => 'Prioridad regulada por',
            'orden' => 6,
            'tipo' => 'check',
            'opciones' => 'Policía de tránsito|Semáforo|Señal Pare|Señal ceda el paso|Marca vial|Paso peatonal|Ninguna',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        /**
         * INFORMACIONES RELACIONADAS A LOS VEHICULOS Y LAS PERSONAS
         * Se crean tres grupos de preguntas
         */
        $orden = 6; // ultimo orden
        for ($index = 1; $index <= 1; $index++) {
            $grupo = GrupoDePreguntas::create([
                'nombre' => 'Datos del Vehículo',
                'descripcion' => '',
                'master' => 1,
                'puede_duplicar' => 1,
                'orden' => $grupoIndex,
            ]);
            $grupoIndex++;

            $grupo->preguntas()->create([
                'pregunta' => 'Vehículo Nro.',
                'orden' => $orden++,
                'tipo' => 'number',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Número de Ocupantes',
                'orden' => $orden++,
                'tipo' => 'number',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Conductor Propietario',
                'orden' => $orden++,
                'tipo' => 'radio',
                'opciones' => 'Si|No',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Vehículo Asegurado',
                'orden' => $orden++,
                'tipo' => 'radio',
                'opciones' => 'Si|No',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Chapa',
                'orden' => $orden++,
                'tipo' => 'text',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Marca',
                'orden' => $orden++,
                'tipo' => 'text',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Chapa Remolcadora',
                'orden' => $orden++,
                'tipo' => 'text',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Modelo',
                'orden' => $orden++,
                'tipo' => 'text',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Año',
                'orden' => $orden++,
                'tipo' => 'number',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Color',
                'orden' => $orden++,
                'tipo' => 'text',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Tipo',
                'orden' => $orden++,
                'tipo' => 'text',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Uso',
                'orden' => $orden++,
                'tipo' => 'text',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Carga transportada',
                'orden' => $orden++,
                'tipo' => 'text',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Rto.',
                'orden' => $orden++,
                'tipo' => 'radio',
                'opciones' => 'Si|No',
                'mostrar_otro' => 0,
            ]);

            $grupo->preguntas()->create([
                'pregunta' => 'Airbags desplegados',
                'orden' => $orden++,
                'tipo' => 'radio',
                'opciones' => 'Conductor|Acompañante|Múltiple|Otro',
                'mostrar_otro' => 0,
            ]);

            for ($subindex = 1; $subindex <= 1; $subindex++) {
                $grupo = GrupoDePreguntas::create([
                    'nombre' => 'Datos de las persona involucrada',
                    'descripcion' => '',
                    'master' => 1,
                    'puede_duplicar' => 1,
                    'orden' => $grupoIndex,
                ]);
                $grupoIndex++;

                $grupo->preguntas()->create([
                    'pregunta' => 'Transportado en vehículo Nro',
                    'orden' => $orden++,
                    'tipo' => 'number',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Ubicación en Vehículo',
                    'orden' => $orden++,
                    'tipo' => 'number',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Ciclista',
                    'orden' => $orden++,
                    'tipo' => 'number',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Peatón',
                    'orden' => $orden++,
                    'tipo' => 'number',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Estado',
                    'orden' => $orden++,
                    'tipo' => 'radio',
                    'opciones' => 'Ileso|Herido|Muerto',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Documento',
                    'orden' => $orden++,
                    'tipo' => 'radio',
                    'opciones' => 'ID|CI|PAS|DOC. EXT.',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Número',
                    'orden' => $orden++,
                    'tipo' => 'text',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Emisor',
                    'orden' => $orden++,
                    'tipo' => 'text',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Apellidos',
                    'orden' => $orden++,
                    'tipo' => 'text',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Nombres',
                    'orden' => $orden++,
                    'tipo' => 'text',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Fecha de Nacimiento',
                    'orden' => $orden++,
                    'tipo' => 'date',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Sexo',
                    'orden' => $orden++,
                    'tipo' => 'radio',
                    'opciones' => 'Masculino|Femenino',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Estado Civil',
                    'orden' => $orden++,
                    'tipo' => 'radio',
                    'opciones' => 'Soltero|Casado|Viudo|Divorciado|Otro',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Nacionalidad',
                    'orden' => $orden++,
                    'tipo' => 'text',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Domicilio',
                    'orden' => $orden++,
                    'tipo' => 'text',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Número',
                    'orden' => $orden++,
                    'tipo' => 'number',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Piso',
                    'orden' => $orden++,
                    'tipo' => 'number',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Departamento',
                    'orden' => $orden++,
                    'tipo' => 'text',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'CP',
                    'orden' => $orden++,
                    'tipo' => 'text',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Localidad',
                    'orden' => $orden++,
                    'tipo' => 'text',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Distrito',
                    'orden' => $orden++,
                    'tipo' => 'text',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Departamento',
                    'orden' => $orden++,
                    'tipo' => 'text',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Hospitalizado en',
                    'orden' => $orden++,
                    'tipo' => 'text',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Cobertura Médica',
                    'orden' => $orden++,
                    'tipo' => 'radio',
                    'opciones' => 'Si|No',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Uso cinturón',
                    'orden' => $orden++,
                    'tipo' => 'radio',
                    'opciones' => 'Si|No|No sabe',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Uso chaleco reflectivo',
                    'orden' => $orden++,
                    'tipo' => 'radio',
                    'opciones' => 'Si|No|No sabe',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Alcotest Positivo',
                    'orden' => $orden++,
                    'tipo' => 'radio',
                    'opciones' => 'Si|No',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Uso casco',
                    'orden' => $orden++,
                    'tipo' => 'radio',
                    'opciones' => 'Si|No|No sabe',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Licencia conductor',
                    'orden' => $orden++,
                    'tipo' => 'radio',
                    'opciones' => 'Si|No|No sabe',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Habilitación',
                    'orden' => $orden++,
                    'tipo' => 'radio',
                    'opciones' => 'Si|No|No sabe',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Clase',
                    'orden' => $orden++,
                    'tipo' => 'text',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Número',
                    'orden' => $orden++,
                    'tipo' => 'number',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Fecha de Expedición',
                    'orden' => $orden++,
                    'tipo' => 'date',
                    'mostrar_otro' => 0,
                ]);

                $grupo->preguntas()->create([
                    'pregunta' => 'Fecha de Vencimiento',
                    'orden' => $orden++,
                    'tipo' => 'date',
                    'mostrar_otro' => 0,
                ]);
            }
        }

        /**
         * Agregar un grupo para institucion 1
         */
        $institucion = Institucion::findOrFail(1);

        $grupo = GrupoDePreguntas::create([
            'nombre' => 'Grupo de Pregunta Específico para Policia Nacional',
            'descripcion' => '',
            'master' => 1,
            'orden' => $grupoIndex,
        ]);
        $grupoIndex++;

        $grupo->preguntas()->create([
            'pregunta' => 'Pregunta Tipo Check',
            'orden' => 1,
            'tipo' => 'check',
            'opciones' => 'Opcion 1|Opcion 2|Opcion 3|Opcion 4|Opcion 5',
        ]);

        $grupo->preguntas()->create([
            'pregunta' => 'Pregunta Tipo Radio',
            'orden' => 2,
            'tipo' => 'radio',
            'opciones' => 'Opcion 1|Opcion 2|Opcion 3|Opcion 4|Opcion 5',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        $grupo->preguntas()->create([
            'pregunta' => 'Pregunta Tipo Texto',
            'orden' => 3,
            'tipo' => 'text',
            'mostrar_otro' => 0,
        ]);

        $institucion->grupoDePreguntas()->attach($grupo, ['orden' => 1]);

        /**
         * Agregar un grupo para institucion 2
         */
        $institucion = Institucion::findOrFail(2);

        $grupo = GrupoDePreguntas::create([
            'nombre' => 'Grupo de Pregunta Específico para Bomberos Voluntarios',
            'descripcion' => '',
            'master' => 1,
            'orden' => $grupoIndex,
        ]);
        $grupoIndex++;

        $grupo->preguntas()->create([
            'pregunta' => 'Pregunta Tipo Check',
            'orden' => 1,
            'tipo' => 'check',
            'opciones' => 'Opcion 1|Opcion 2|Opcion 3|Opcion 4|Opcion 5',
        ]);

        $grupo->preguntas()->create([
            'pregunta' => 'Pregunta Tipo Radio',
            'orden' => 2,
            'tipo' => 'radio',
            'opciones' => 'Opcion 1|Opcion 2|Opcion 3|Opcion 4|Opcion 5',
            'mostrar_otro' => 1,
            'titulo_otro' => 'Otro',
        ]);

        $grupo->preguntas()->create([
            'pregunta' => 'Pregunta Tipo Texto',
            'orden' => 3,
            'tipo' => 'text',
            'mostrar_otro' => 0,
        ]);

        $institucion->grupoDePreguntas()->attach($grupo, ['orden' => 1]);
    }
}
