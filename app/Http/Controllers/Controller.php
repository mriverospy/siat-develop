<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        // Variables to be available across all views
//        view()->share('isLoggedIn', Auth::check());
//        if (Auth::check()) {
//            view()->share('currentUser', Auth::user());
//        } else {
//            view()->share('currentUser', null);
//        }
    }
}
