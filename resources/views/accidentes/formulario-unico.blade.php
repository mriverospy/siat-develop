<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sistema de Información de Accidentes de Tránsito</title>
    <style type="text/css">
        * { font-family: Arial, sans-serif; }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            font-weight: normal;
            /*margin: 0 auto;*/
            /*width: 700px;*/
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 0;
            color: red;
        }
        h2 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 0;
        }
        h3 {
            font-size: 18px;
            font-weight: bold;
            border: 3px solid #0088cb;
            padding: 5px;
        }
        .no-break {
            page-break-inside: avoid;
        }
        table {
            width: 100%;
            margin: 0;
            padding: 0;
        }
        table.bordered {
            border: 2px solid #808080;
        }
        table.row {
            margin-bottom: 5px;
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
        td.col-key {
            font-weight: bold;
        }
        td.subcol-key {
            border-bottom: 1px solid #ededed;
        }
        td.col-value {

        }
        td.col-underline {
            border-bottom: 1px solid #333;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <p class="text-center">
        <img src="{{ public_path($logo_membrete) }}" alt="Cabecera PDF" />
    </p>
    <h1 class="text-center">Sistema de Información de Accidentes de Tránsito</h1>
    <table class="row">
        <tr>
            <td class="col-key" style="width: 155px;">Institución Interviniente:</td>
            <td class="col-underline">{{ $currentUser->sede->institucion->nombre }}</td>
        </tr>
    </table>
    
    <div class="no-break">
        <table class="row bordered">
            <tr>
                <th>BLOQUE 24HS.</th>
            </tr>
            <tr>
                <td>
                    <table class="row">
                        <tr>
                            <td class="col-key" style="width: 30px;">Fecha</td>
                            <td class="col-underline"></td>
                            <td class="" style="width: 10px;"></td>
                            <td class="col-key" style="width: 30px;">Hora</td>
                            <td class="col-underline"></td>
                            <td class="" style="width: 10px;"></td>
                            <td class="col-key" style="width: 60px;">Dirección</td>
                            <td class="col-underline"></td>
                            <td class="" style="width: 10px;"></td>
                            <td class="col-key" style="width: 50px;">Num/Km</td>
                            <td class="col-underline"></td>
                        </tr>
                    </table>
                    <table class="row">
                        <tr>
                            <td class="col-key" style="width: 30px;">Lugar</td>
                            <td class="" style="width: 10px;"></td>
                            <td>
                                Calle <img src="{{ public_path('img/formulario_unico/radio_off.gif') }}" alt="" />
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                Ruta <img src="{{ public_path('img/formulario_unico/radio_off.gif') }}" alt="" />
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                Avda. <img src="{{ public_path('img/formulario_unico/radio_off.gif') }}" alt="" />
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                Ramal <img src="{{ public_path('img/formulario_unico/radio_off.gif') }}" alt="" />
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                Camino Vecinal <img src="{{ public_path('img/formulario_unico/radio_off.gif') }}" alt="" />
                            </td>
                        </tr>
                    </table>
                    <table class="row">
                        <tr>
                            <td class="col-key" style="width: 30px;">Otro</td>
                            <td class="col-underline"></td>
                            <td class="" style="width: 10px;"></td>
                            <td class="col-key" style="width: 30px;">Localidad/Ciudad/Pueblo</td>
                            <td class="col-underline"></td>
                        </tr>
                    </table>
                    <table class="row">
                        <tr>
                            <td class="" style="width: 10px;"></td>
                            <td class="col-key" style="width: 60px;">Municipio</td>
                            <td class="col-underline"></td>
                            <td class="" style="width: 10px;"></td>
                            <td class="col-key" style="width: 30px;">Dpto</td>
                            <td class="col-underline"></td>
                        </tr>
                    </table>
                    <table class="row">
                        <tr>
                            <td class="col-key" style="width: 170px;">Coordenadas GPS/Latitud</td>
                            <td class="col-underline"></td>
                            <td class="" style="width: 10px;"></td>
                            <td class="col-key" style="width: 180px;">Coordenadas GPS/Longitud</td>
                            <td class="col-underline"></td>
                        </tr>
                    </table>
                    <table class="row">
                        <tr>
                            <td class="col-key" style="width: 90px;">Total Heridos</td>
                            <td class="col-underline"></td>
                            <td class="" style="width: 10px;"></td>
                            <td class="col-key" style="width: 100px;">Total Fallecidos</td>
                            <td class="col-underline"></td>
                            <td class="" style="width: 10px;"></td>
                            <td class="col-key" style="width: 100px;">Total de Ilesos</td>
                            <td class="col-underline"></td>
                        </tr>
                    </table>
                    <table class="row">
                        <tr>
                            <td class="col-key" style="width: 220px;">Total de Participantes del Siniestro</td>
                            <td class="col-underline" style="width: 50px;"></td>
                            <td class="" style="width: 10px;"></td>
                            <td><!-- (Automóvil - Peatón - Bicicleta - Obj. Fijo - etc) --></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    @foreach($filas as $fila)
    <table class="row">
        <tr>
            <th style="border-bottom: 2px solid #808080;">{{ $fila['nombre'] }}</th>
        </tr>
        <tr>
            <td>
                @foreach($fila['rows'] as $row)
                    <?php
                        $col_width = round(100 / $row['colCount']);
                    ?>
                <table class="no-break">
                    <tr>
                    @foreach($row['cols'] as $col)
                        <td style="width: {{ $col_width }}%; text-align: left; font-weight: bold; border-bottom: 1px solid black;">
                            {{ $col['pregunta'] }} <small>(Cód. {{ $col['id'] }})</small>
                        </td>
                    @endforeach
                    </tr>
                    <tr>
                    @foreach($row['cols'] as $col)
                        <td style="width: {{ $col_width }}%; text-align: left;">
                            @if ($col['tipo'] == 'check')
                                @foreach ($col['opciones'] as $opcion)
                                    <img src="{{ public_path('img/formulario_unico/checkbox_off.gif') }}" alt="" /> {{ $opcion }} &nbsp;
                                @endforeach
                                @if ($col['mostrar_otro'])
                                <br />
                                {{ trim($col['mostrar_otro']) == '' ? 'Otro' : $col['titulo_otro'] }}: _______________
                                @endif
                            @elseif ($col['tipo'] == 'radio')
                                @foreach ($col['opciones'] as $opcion)
                                    <img src="{{ public_path('img/formulario_unico/radio_off.gif') }}" alt="" /> {{ $opcion }} &nbsp;
                                @endforeach
                                @if ($col['mostrar_otro'])
                                <br />
                                {{ trim($col['mostrar_otro']) == '' ? 'Otro' : $col['titulo_otro'] }}: _______________
                                @endif
                            @else
                            ________________
                            @endif
                        </td>
                    @endforeach
                    </tr>
                </table>
                @endforeach
            </td>
        </tr>
    </table>
    @endforeach

</body>
</html>
