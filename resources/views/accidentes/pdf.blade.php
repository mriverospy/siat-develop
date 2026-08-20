<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Datos del Accidente</title>
    <style type="text/css">
        * { font-family: Arial, sans-serif; }
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: normal;
            /*width: 700px;*/
            /*margin: 0 auto;*/
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .no-break {
            page-break-inside: avoid;
        }
        h1 {
            font-size: 22px;
            font-weight: bold;
            color: #006699;
            margin-bottom: 0;
        }
        h2 {
            font-size: 18px;
            font-weight: bold;
            color: #006699;
            margin-bottom: 0;
        }
        h3 {
            font-size: 18px;
            font-weight: bold;
        }
        table {
            width: 100%;
        }
        table.section {
            margin-bottom: 5px;
        }
        tr.row-question td.question-key {
            border-bottom: 1px solid #ededed;
        }
        th {
            font-weight: bold;
            vertical-align: top;
            background-color: #d6dce4;
            color: #44546a;
        }
        td {
            vertical-align: top;
        }
        td.fifty {
            width: 50%;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <h1 class="text-center">Formulario Único</h1>
    <h2 class="text-center">Datos del Sistema de Información de Accidentes de Tránsito</h2>
    <br>
    <table class="section">
        <tr>
            <th>Institucion Interviniente</th>
            <th>Dependencia</th>
            <th>Generado por</th>
            <th>Fecha y Hora</th>
        </tr>
        <tr>
            <td class="text-center">{{ $accidente->usuario->sede->institucion->nombre }}</td>
            <td class="text-center">{{ $accidente->dependencia }}</td>
            <td class="text-center">{{ $currentUser->nombres }} {{ $currentUser->apellidos }}</td>
            <td class="text-center">{{ date('d/m/Y h:i a') }}</td>
        </tr>
    </table>
    <table>
        <tr>
            <th>Carátula</th>
        </tr>
        <tr>
            <td class="text-center">{{ $accidente->caratula }}</td>
        </tr>
    </table>
    <table>
        <tr>
            <th>Nro. Expendiente</th>
            <th>Nro. Sumario Policial</th>
            <th>Otro</th>
        </tr>
        <tr>
            <td class="text-center">{{ $accidente->expediente_judicial }}</td>
            <td class="text-center">{{ $accidente->sumario_policial }}</td>
            <td class="text-center">{{ $accidente->otro }}</td>
        </tr>
    </table>
    <h3 class="text-center" style="padding: 5px; color: #fff; background-color: #44546a; color: #fff;">Bloque 24 Horas</h3>
    <table>
        <tr>
            <th>Fecha y Hora</th>
            <th>Tipo Camino</th>
        </tr>
        <tr>
            <td class="text-center">{{ $accidente->fecha_accidente->format('d/m/Y h:i a') }}</td>
            <td class="text-center">{{ ucwords($accidente->tipo_camino) }}</td>
        </tr>
    </table>
    <table>
        <tr>
            <th>Lugar</th>
            <th>Altura</th>
            <th>Código Postal</th>
        </tr>
        <tr>
            <td class="text-center">{{ $accidente->lugar }}</td>
            <td class="text-center">{{ $accidente->altura }}</td>
            <td class="text-center">{{ $accidente->codigo_postal }}</td>
        </tr>
    </table>
    <table>
        <tr>
            <th>Localidad</th>
            <th>Distrito</th>
            <th>Departamento</th>
        </tr>
        <tr>
            <td class="text-center">{{ $accidente->localidad->nombre }}</td>
            <td class="text-center">{{ $accidente->localidad->distrito->nombre }}</td>
            <td class="text-center">{{ $accidente->localidad->distrito->departamento->nombre }}</td>
        </tr>
    </table>
    <table>
        <tr>
            <th>Heridos</th>
            <th>Fallecidos</th>
            <th>Ilesos</th>
            <th>Participantes</th>
        </tr>
        <tr>
            <td class="text-center">{{ $accidente->heridos }}</td>
            <td class="text-center">{{ $accidente->fallecidos }}</td>
            <td class="text-center">{{ $accidente->ilesos }}</td>
            <td class="text-center">{{ $accidente->participantes_siniestro }}</td>
        </tr>
    </table>

    <div class="page-break"></div>

    @if (count($respuestas) > 0)
        @foreach($respuestas->chunk(2) as $chunks)
    <table class="no-break">
        <tr style="page-break-inside: avoid;">
            @foreach($chunks as $chunk)
            <td class="fifty">
                <table>
                    <tr>
                        <th>{{ $chunk['grupo'] }}</th>
                    </tr>
                @foreach($chunk['respuestas'] as $preguntas)
                    @foreach($preguntas as $pregunta => $_respuestas)
                    <tr>
                        <td>
                            <table>
                                <tr class="row-question">
                                    <?php
                                        // TODO: cambiar esto
                                        $titulo = explode('/', $pregunta);
                                        if (count($titulo) > 1)
                                        {
                                            $titulo = last($titulo);
                                        }
                                        else
                                        {
                                            $titulo = $pregunta;
                                        }
                                    ?>
                                    <td class="fifty text-right">
                                        <strong>{{ $titulo }}</strong>
                                    </td>
                                    <td class="fifty">
                                        <?php
                                            $values = [];
                                            foreach($_respuestas as $respuesta)
                                            {
                                                if (in_array($respuesta->pregunta_tipo, ['radio', 'check', 'text']))
                                                {
                                                    $values[] = $respuesta->respuesta_texto;
                                                }
                                                if ($respuesta->pregunta_tipo == 'number')
                                                {
                                                    $values[] = $respuesta->respuesta_numero;
                                                }
                                                if ($respuesta->pregunta_tipo == 'date')
                                                {
                                                    $values[] = $respuesta->respuesta_fecha->format('d/m/Y');
                                                }
                                            }
                                        ?>
                                        <?= implode(', ', $values) ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                @endforeach
                </table>
            </td>
            @endforeach

            @if (count($chunks) == 1)
            <td class="fifty">&nbsp;</td>
            @endif
        </tr>
    </table>
        @endforeach
    @endif
</body>
</html>
