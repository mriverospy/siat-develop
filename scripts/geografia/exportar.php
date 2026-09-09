<?php

// Exporta únicamente catálogos de referencia; no incluye accidentes ni usuarios.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if ($argc !== 2) {
    fwrite(STDERR, "Uso: php scripts/geografia/exportar.php /ruta/catalogos.json\n");
    exit(1);
}
file_put_contents($argv[1], json_encode([
    'departamentos' => App\Models\Departamento::all(['id', 'nombre']),
    'distritos' => App\Models\Distrito::all(['id', 'nombre', 'departamento_id']),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
