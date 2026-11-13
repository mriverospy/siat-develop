<?php

return [
    // Las coordenadas se conservan como texto decimal; respuesta_numero redondea a 2 decimales.
    'preguntas' => [
        'CA' => ['pregunta' => 'Carátula', 'tipo' => 'text'],
        'A_1' => ['pregunta' => 'Identificador SIAT del accidente', 'tipo' => 'text'],
        'A_2' => ['pregunta' => 'Fecha del siniestro', 'tipo' => 'date'],
        'A_3' => ['pregunta' => 'Hora del siniestro', 'tipo' => 'text'],
        'APY_01' => ['pregunta' => 'Departamento del siniestro', 'tipo' => 'text'],
        'APY_02' => ['pregunta' => 'Distrito del siniestro', 'tipo' => 'text'],
        'APY_03' => ['pregunta' => 'Barrio o localidad del siniestro', 'tipo' => 'text'],
        'APY_04' => ['pregunta' => 'Dirección del siniestro', 'tipo' => 'text'],
        'A_8' => ['pregunta' => 'Latitud geodésica', 'tipo' => 'text'],
        'A_9' => ['pregunta' => 'Longitud geodésica', 'tipo' => 'text'],
        'A_10' => ['pregunta' => 'Área geográfica', 'tipo' => 'radio', 'opciones' => ['Rural' => '1', 'Urbana' => '2', 'Se desconoce' => '99']],
        'A_11' => ['pregunta' => 'Tipo de vía', 'tipo' => 'radio', 'opciones' => [
            'Autopista' => '1', 'Vía no urbana' => '2', 'Vía urbana, doble sentido' => '3',
            'Vía urbana, sentido único' => '4',
            'Calles sin salida, vías de acceso particulares, callejones, calle privada' => '5',
            'Otra' => '6', 'Desconocida' => '99',
        ]],
    ],
    // Códigos INE por nombre, independientes de las claves internas del catálogo.
    'departamentos' => [
        'ASUNCION' => '00', 'CONCEPCION' => '01', 'SAN PEDRO' => '02', 'CORDILLERA' => '03',
        'GUAIRA' => '04', 'CAAGUAZU' => '05', 'CAAZAPA' => '06', 'ITAPUA' => '07',
        'MISIONES' => '08', 'PARAGUARI' => '09', 'ALTO PARANA' => '10', 'CENTRAL' => '11',
        'NEEMBUCU' => '12', 'AMAMBAY' => '13', 'CANINDEYU' => '14', 'PRESIDENTE HAYES' => '15',
        'ALTO PARAGUAY' => '16', 'BOQUERON' => '17',
    ],
];
