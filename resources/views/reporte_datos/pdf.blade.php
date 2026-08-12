<!DOCTYPE html>
<html>
    <head>
        <title>Reporte de Datos</title>
        <style type="text/css">
            body {
                margin: 0;
                padding: 0;
            }

            .text-center {
                text-align: center;
            }

            .text-right {
                text-align: right;
            }

            table {
                border-collapse: collapse;
                border-spacing: 0;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-row-group;
            }

            tr {
                page-break-inside: avoid;
            }

            th {
                border: 1px solid #000;
                vertical-align: bottom;
            }

            td {
                border: 1px solid #000;
                vertical-align: top;
            }
        </style>
    </head>
    <body>
        <p class="text-center">{{ $reporte->nombre }} &mdash; Del {{ $fecha_inicio }} al {{ $fecha_fin }}</p>
        <table width="100%">
            <thead>
                <tr>
                    @foreach($columns as $column_key => $column_name)
                    <th class="text-center">{{ $column_name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($reporteDatos as $item)
                <tr>
                    @foreach($columns as $column_key => $column_name)
                    <td class="{{ (stristr($column_key, 'total') !== FALSE OR in_array($column_key, ['heridos', 'fallecidos', 'ilesos', 'participantes_siniestro'])) ? 'text-right' : '' }}">
                        {{ $item->$column_key }}
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
