<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\Contracts\View\View;

class HeaderComposer
{
    /**
     * Create a new sidebar composer.
     *
     * @param  AuthManager  $auth
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     **/
    public function compose(View $view)
    {
        $view->with('notificaciones_no_leidas', Auth::user()->notificaciones()->noleidos()->count());
        $view->with('notificaciones', Auth::user()->notificaciones()->noleidos()->orderBy('fecha', 'desc')->orderBy('id', 'desc')->get());
    }
}
