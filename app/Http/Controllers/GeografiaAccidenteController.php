<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Distrito;
use App\Services\GeografiaAccidente;
use App\Http\Requests\RequestUtils;
use RuntimeException;

class GeografiaAccidenteController extends Controller
{
    public function departamento(GeografiaAccidente $geografia, $departamento)
    {
        return $this->respuesta($geografia, 'departamentos', Departamento::findOrFail($departamento));
    }

    public function distrito(GeografiaAccidente $geografia, $distrito)
    {
        return $this->respuesta($geografia, 'distritos', Distrito::findOrFail($distrito));
    }

    private function respuesta(GeografiaAccidente $geografia, string $tipo, $modelo)
    {
        abort_unless(RequestUtils::havePermission('accidentes', 'create'), 403);
        try {
            return response()->json($tipo === 'distritos'
                ? $geografia->limiteDistrito($modelo, Departamento::find($modelo->departamento_id))
                : $geografia->territorio($tipo, $modelo));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
