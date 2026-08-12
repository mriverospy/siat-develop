<?php

namespace App\Listeners;

use App\Models\Login;
use Auth;

class UserEventLogoutListener
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
     * @param  auth.logout  $event
     * @return void
     */
    public function handle($event)
    {
        $usuario = Auth::user();
        if ($usuario) {
            $login = new Login([
                'accion' => 'logout',
                'direccion_ip' => \Request::getClientIp(),
            ]);
            $usuario->logins()->save($login);
        }
    }
}
