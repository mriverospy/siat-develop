<?php

namespace Database\Factories;

use App\Models\Sede;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 */
class UsuarioFactory extends Factory
{

    protected $model = Usuario::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $sedeIds = Sede::pluck('id')->toArray();
        return [
            'sede_id' => fake()->randomElement($sedeIds),
            'documento' => fake()->numberBetween(300000, 6000000),
            'genero' => fake()->randomElement(['Sin Definir', 'Masculino', 'Femenino']),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'telefonos' => fake()->phoneNumber(),
            'direccion' => fake()->streetAddress(),
            'email' => fake()->email(),
            'username' => strtolower(fake()->userName()),
            'password' => Hash::make('password'),
            'activo' => fake()->randomElement([0, 1]),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
