<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class CheckPasswordChange
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->check() and $this->auth->user()->cambiar_password) {
            return redirect()
                ->route('user.profile')
                ->withMessages([
                    'type' => 'warning',
                    'text' => 'Debe actualizar sus datos y cambiar su contraseña para poder continuar',
                ]);
        }

        return $next($request);
    }
}
