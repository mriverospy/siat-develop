<?php

namespace App\Listeners;

use App\Models\Login;
use Auth;

class UserEventLoginListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  auth.login  $event
     * @return void
     */
    public function handle($event)
    {
        $usuario = Auth::user();
        $login = new Login([
            'accion' => 'login',
            'direccion_ip' => \Request::getClientIp(),
        ]);
        $usuario->logins()->save($login);
    }
}
