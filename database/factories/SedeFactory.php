<?php

namespace Database\Factories;

use App\Models\Sede;
use Illuminate\Database\Eloquent\Factories\Factory;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

// Factory de Usuarios
// El Factory de Usuarios se movión a UsuarioTableSeeder, la creación de
// usuarios en el sistema depende de que existan sedes creadas y éstas aún
// no están creadas cuando este archivo (ModelFactory) se ejecuta a traves
// del db:seed

// Factory de Sedes

// Factory de Grupo de Preguntas

class SedeFactory extends Factory
{
    protected $model = Sede::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nombre' => fake()->numerify('Sede ##'),
            'descripcion' => fake()->sentence(8),
        ];
    }
}
