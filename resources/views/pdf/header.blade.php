<!DOCTYPE html>
<html>
    <head>
        <title>Cabecera</title>
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
        </style>
    </head>
    <body>
        <table width="100%">
            <tr>
                <td style="text-align: center;">
                    <img src="{{ public_path($pdfData['logo_membrete']) }}" alt="Logo SIAT" />
                </td>
                <td class="text-right">
                    @if (trim($pdfData['user']->sede->institucion->logo) != '')
                    <img src="{{ public_path($pdfData['user']->sede->institucion->logo) }}" alt="Logo Institucion" />
                    @endif
                </td>
            </tr>
        </table>
    </body>
</html>
