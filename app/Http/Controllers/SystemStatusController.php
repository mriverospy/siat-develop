<?php

namespace App\Http\Controllers;

use App\Models\Accidente;
use Illuminate\Http\Request;

class SystemStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->has('token') && $request->get('token') == 'ucPuBcVXahWgPE8RVM83HCsWgTsX6ZWK') {
            try {
                // hacemos un query a la db, si hay problema, va a generar un error
                $accidente = Accidente::first()->get();

                $db_status = 'OK';
            } catch (\Exception $e) {
                $db_status = 'ERROR';
            }

            return response()->json([
                'status' => 'success',
                'db_status' => $db_status,
                'release' => config('siat.release'),
            ]);
        } else {
            abort(404);
        }
    }
}
