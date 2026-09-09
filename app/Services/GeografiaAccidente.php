<?php

namespace App\Services;

use App\Models\Departamento;
use App\Models\Distrito;
use RuntimeException;

class GeografiaAccidente
{
    private string $directory;

    public function __construct(?string $directory = null)
    {
        $this->directory = $directory ?? storage_path('app/geografia');
    }

    public function territorio(string $tipo, $modelo): array
    {
        if (!in_array($tipo, ['departamentos', 'distritos'], true) || !$modelo) {
            throw new RuntimeException('El territorio seleccionado no existe.');
        }
        $catalogo = $this->leer($this->directory.'/catalogo.json');
        $registro = $catalogo[$tipo][$modelo->id] ?? null;
        // Los IDs pertenecen al catálogo auditado; un ID reutilizado no hereda sus límites.
        if (!$registro || $registro['nombre'] !== $modelo->nombre ||
            ($tipo === 'distritos' && (int) $registro['departamento_id'] !== (int) $modelo->departamento_id)) {
            throw new RuntimeException('No hay límites geográficos verificados para '.$modelo->nombre.'. No se puede confirmar la ubicación.');
        }
        $feature = $this->leer($this->directory.'/'.$tipo.'/'.(int) $modelo->id.'.geojson.gz', true);
        if (($feature['type'] ?? '') !== 'Feature' ||
            !in_array($feature['geometry']['type'] ?? '', ['Polygon', 'MultiPolygon'], true) ||
            empty($feature['geometry']['coordinates']) || empty($feature['properties']['referencia'])) {
            throw new RuntimeException('La cartografía de la zona no es válida.');
        }
        return $feature;
    }

    public function limiteDistrito($distrito, $departamento): array
    {
        if (!$distrito || !$departamento || (int) $distrito->departamento_id !== (int) $departamento->id) {
            throw new RuntimeException('El distrito no corresponde al departamento seleccionado.');
        }
        $catalogo = $this->leer($this->directory.'/catalogo.json');
        $registro = $catalogo['distritos'][$distrito->id] ?? null;
        $configurado = $registro && $registro['nombre'] === $distrito->nombre &&
            (int) $registro['departamento_id'] === (int) $departamento->id &&
            is_file($this->directory.'/distritos/'.(int) $distrito->id.'.geojson.gz');
        $feature = $configurado
            ? $this->territorio('distritos', $distrito)
            : $this->territorio('departamentos', $departamento);
        $feature['properties']['nivel_limite'] = $configurado ? 'distrito' : 'departamento';
        $feature['properties']['nombre_limite'] = $feature['properties']['nombre'];
        // La respuesta conserva la identidad de la selección, aunque use el límite departamental.
        $feature['properties']['id'] = $distrito->id;
        $feature['properties']['nombre'] = $distrito->nombre;
        $feature['properties']['departamento_id'] = $departamento->id;
        return $feature;
    }

    private function leer(string $path, bool $compressed = false): array
    {
        if (!is_readable($path)) {
            throw new RuntimeException('No está disponible la cartografía de la zona seleccionada.');
        }
        $content = file_get_contents($path);
        if ($compressed) $content = @gzdecode($content);
        $data = $content === false ? null : json_decode($content, true);
        if (!is_array($data)) throw new RuntimeException('No se pudo leer la cartografía de la zona seleccionada.');
        return $data;
    }

    public function validar(array $data): ?string
    {
        if (!is_numeric($data['gps_latitud'] ?? null) || !is_numeric($data['gps_longitud'] ?? null)) {
            return 'Ingrese coordenadas válidas.';
        }
        $lat = (float) $data['gps_latitud'];
        $lng = (float) $data['gps_longitud'];
        if (!is_finite($lat) || !is_finite($lng) || abs($lat) > 90 || abs($lng) > 180) {
            return 'Ingrese coordenadas válidas.';
        }
        $departamento = Departamento::find($data['departamento_id'] ?? null);
        $distrito = Distrito::find($data['distrito_id'] ?? null);
        if (!$departamento || !$distrito || (int) $distrito->departamento_id !== (int) $departamento->id) {
            return 'El distrito no corresponde al departamento seleccionado.';
        }
        try {
            $dep = $this->territorio('departamentos', $departamento);
            $dist = $this->limiteDistrito($distrito, $departamento);
            if (!self::contiene($dep['geometry'], $lng, $lat) || !self::contiene($dist['geometry'], $lng, $lat)) {
                return $dist['properties']['nivel_limite'] === 'departamento'
                    ? 'La ubicación debe estar dentro del departamento seleccionado.'
                    : 'La ubicación debe estar dentro del distrito y departamento seleccionados.';
            }
        } catch (RuntimeException $e) {
            return $e->getMessage();
        }
        return null;
    }

    public static function contiene(array $geometry, float $x, float $y): bool
    {
        if (!is_finite($x) || !is_finite($y)) return false;
        if (($geometry['type'] ?? '') === 'Polygon') $polygons = [$geometry['coordinates']];
        elseif (($geometry['type'] ?? '') === 'MultiPolygon') $polygons = $geometry['coordinates'];
        else return false;
        foreach ($polygons as $rings) {
            if (empty($rings)) continue;
            $outer = self::anillo($rings[0], $x, $y);
            if ($outer < 0) continue;
            $inside = true;
            foreach (array_slice($rings, 1) as $hole) {
                // Se acepta el borde exacto, pero no el interior de un hueco.
                if (self::anillo($hole, $x, $y) > 0) { $inside = false; break; }
            }
            if ($inside) return true;
        }
        return false;
    }

    // -1 exterior, 0 borde, 1 interior. Misma tolerancia numérica en PHP y JS.
    private static function anillo(array $ring, float $x, float $y): int
    {
        if (count($ring) < 4) return -1;
        $inside = false;
        for ($i = 0, $j = count($ring) - 1; $i < count($ring); $j = $i++) {
            [$ax, $ay] = $ring[$j];
            [$bx, $by] = $ring[$i];
            $dx = $bx - $ax; $dy = $by - $ay;
            $length2 = $dx * $dx + $dy * $dy;
            if ($length2 > 0) {
                $t = (($x - $ax) * $dx + ($y - $ay) * $dy) / $length2;
                if ($t >= 0 && $t <= 1 && hypot($x - ($ax + $t * $dx), $y - ($ay + $t * $dy)) <= 1e-10) return 0;
            } elseif (hypot($x - $ax, $y - $ay) <= 1e-10) return 0;
            if (($ay > $y) !== ($by > $y) && $x < ($bx - $ax) * ($y - $ay) / ($by - $ay) + $ax) $inside = !$inside;
        }
        return $inside ? 1 : -1;
    }
}
