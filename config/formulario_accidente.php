<?php

return [
    'vias' => [
        'autopista' => 'Autopista',
        'urbana_doble_sentido' => 'Vía urbana, doble sentido',
        'urbana_sentido_unico' => 'Vía urbana, sentido único',
        'no_urbana' => 'Vía no urbana',
        'acceso_particular' => 'Calles sin salida, vías de acceso particulares, callejones, calle privada',
        'otra' => 'Otra',
        'desconocida' => 'Desconocida',
    ],
    'areas' => ['urbana' => 'Urbana', 'rural' => 'Rural', 'se_desconoce' => 'Se desconoce'],
    // IDs del catálogo Formulario 70 verificado en la base de datos.
    'condiciones' => [
        131 => ['sin' => 130],
        136 => ['roles' => ['Pasajero']],
        137 => ['roles' => ['Conductor', 'Pasajero', 'Ciclista'], 'vehiculos' => ['Bicicleta', 'Motocicleta', 'Motocarro']],
        138 => ['roles' => ['Conductor', 'Pasajero'], 'vehiculos' => ['Motocicleta', 'Motocarro']],
        139 => ['roles' => ['Conductor', 'Pasajero'], 'vehiculos' => ['Automóvil', 'Camioneta o furgón', 'Autobús (transporte público, viajes turísticos, etc.).', 'Vehículo para transporte de mercancías ligero (<3,5 tn)', 'Vehículo para transporte de mercancías pesado (>3,5 tn)', 'Vehículo para transporte de mercancías pesado (≥3,5 tn)']],
        140 => ['roles' => ['Conductor']],
        141 => ['roles' => ['Conductor'], 'pregunta' => 140, 'valores' => ['Positivo']],
        142 => ['roles' => ['Conductor']],
        143 => ['roles' => ['Conductor']],
        144 => ['roles' => ['Conductor']],
        145 => ['roles' => ['Conductor'], 'pregunta' => 144, 'valores' => ['Si', 'Sí']],
        146 => ['roles' => ['Conductor'], 'pregunta' => 144, 'valores' => ['Si', 'Sí']],
        147 => ['roles' => ['Conductor'], 'pregunta' => 144, 'valores' => ['Si', 'Sí']],
        148 => ['roles' => ['Conductor'], 'pregunta' => 144, 'valores' => ['Si', 'Sí']],
        149 => ['roles' => ['Peatón']],
    ],
    'obligatorias' => [132, 140, 142, 144],
];
