<?php

namespace Database\Seeders;

use App\Models\Estado;
use Illuminate\Database\Seeder;

class EstadoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Estado::create([
            'nombre' => 'Abierto',
            'descripcion' => 'Nuevo accidente abierto recientemente',
        ]);

        Estado::create([
            'nombre' => 'Procesamiento',
            'descripcion' => 'Procesamiento de datos',
        ]);

        Estado::create([
            'nombre' => 'Cerrado',
            'descripcion' => 'El accidente ha finalizado',
        ]);
    }
}
