<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Localidad;
use Illuminate\Database\Seeder;

class LocalidadTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Los datos de departamentos, distritos y localidades
        // se extrajeron de la base de datos de establecimientos
        // educativos del Ministerio de Educación y Cultura
        // URL: http://datos.mec.gov.py
        if (($handle = fopen(storage_path('seed_data/localidades.csv'), 'r')) !== false) {
            $departamentoModel = $distritoModel = $localidadModel = null;
            $codigoDepartamento = null;
            $departamento = $distrito = $localidad = '';
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                if ('LOCALIDAD A CONFIRMAR' == strtoupper(trim($data[3]))) {
                    continue;
                }

                if ($codigoDepartamento !== (int) trim($data[0])) {
                    $departamentoModel = new Departamento([
                        'orden' => (int) trim($data[0]),
                        'nombre' => str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], trim($data[1])),
                    ]);
                    $departamentoModel->save();
                }

                if ($distrito !== trim($data[2])) {
                    $distritoModel = new Distrito([
                        'nombre' => str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], trim($data[2])),
                        'descripcion' => str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], trim($data[2])),
                    ]);
                    $departamentoModel->distritos()->save($distritoModel);
                }

                $localidadModel = new Localidad([
                    'nombre' => trim($data[3]),
                    'descripcion' => str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], trim($data[3])),
                ]);
                $distritoModel->localidades()->save($localidadModel);

                $codigoDepartamento = (int) trim($data[0]);
                $departamento = trim($data[1]);
                $distrito = trim($data[2]);
                $localidad = trim($data[3]);
            }
        }
    }
}
