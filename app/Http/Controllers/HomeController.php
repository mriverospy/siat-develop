<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestUtils;
use App\Models\Derivacion;
use Auth;

class HomeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Home Controller
    |--------------------------------------------------------------------------
    |
    | This controller renders your application's "dashboard" for users that
    | are authenticated. Of course, you are free to change or remove the
    | controller as you wish. It is just here to get your app started!
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
        parent::__construct();
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index()
    {
        $derivaciones = [];
        if (RequestUtils::havePermission('accidentes', 'receive')) {
            $derivaciones = Derivacion::where('deriva_a_institucion_id', Auth::user()->sede->institucion_id)->orderBy('created_at', 'desc')->take(5)->get();
        }

        $autorizaciones = [];
        if (RequestUtils::havePermission('accidentes', 'receive-authorization')) {
            $autorizaciones = Auth::user()->autorizaciones_recibidas()->orderBy('created_at', 'desc')->take(5)->get();
        }

        return view('home', compact('derivaciones', 'autorizaciones'));
    }
}
