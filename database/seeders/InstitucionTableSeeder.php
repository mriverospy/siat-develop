<?php

namespace Database\Seeders;

use App\Models\Institucion;
use App\Models\Sede;
use Illuminate\Database\Seeder;

class InstitucionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $instituciones = [
            'Policía Nacional' => [
                'Comisaría 1ra',
                'Comisaría 2da',
            ],
            'Bomberos Voluntarios' => [
                '1ra Compañia - San Lorenzo',
                '2da Compañia - Fernando de la Mora',
            ],
            'Policía de Tránsito' => [],
            'Policía Caminera' => [],
            'Bomberos Policía' => [],
            'Emergencias Médicas' => [],
            'Hospital Nacional' => [],
            'Institución 1' => [
                'Sede 1',
            ],
            'Institución 2' => [
                'Sede 1',
            ],
        ];

        foreach ($instituciones as $institucion => $sedes) {
            $i = Institucion::create(['nombre' => $institucion]);
            if (count($sedes) > 0) {
                foreach ($sedes as $sede) {
                    $i->sedes()->create([
                        'institucion_id' => $i->id,
                        'nombre' => $sede,
                        'descripcion' => $sede,
                    ]);
                }
            } else {
                Sede::factory()->count(10)->create(['institucion_id' => $i->id]);
            }
        }
    }
}
