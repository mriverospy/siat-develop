<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Accidente extends Model
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    /**
     * Campos de Asignacion Masiva
     *
     * @var array
     */
    protected $fillable = [
        'usuario_id',
        'sede_id',
        'institucion_id',
        'localidad_id',
        'estado_id',
        'estado',
        'caratula',
        'fecha_accidente',
        'fecha_apertura',
        'fecha_cierre',
        'expediente_judicial',
        'sumario_policial',
        'otro',
        'dependencia',
        'lugar',
        'codigo_postal',
        'tipo_camino',
        'area',
        'altura',
        'codigo_postal',
        'gps_latitud',
        'gps_longitud',
        'heridos',
        'fallecidos',
        'ilesos',
        'participantes_siniestro',
    ];

    protected $casts = [
        'fecha_accidente' => 'datetime',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function scopeAbierto($query)
    {
        return $query->where('estado', 'abierto');
    }

    public function scopeCerrado($query)
    {
        return $query->where('estado', 'cerrado');
    }

    /**
     * Un Accidente ptiene un estado
     *
     * @return Estado
     */
    public function estado()
    {
        return $this->belongsTo(\App\Models\Estado::class);
    }

    /**
     * Un Accidente pertenece a un Usuario
     *
     * @return Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class);
    }

    /**
     * Un Accidente pertenece a una Sede
     *
     * @return Sede
     */
    public function sede()
    {
        return $this->belongsTo(\App\Models\Sede::class);
    }

    /**
     * Un Accidente pertenece a una Institución
     *
     * @return Institucion
     */
    public function institucion()
    {
        return $this->belongsTo(\App\Models\Institucion::class);
    }

    /**
     * Un Accidente pertenece a una Localidad
     *
     * @return Localidad
     */
    public function localidad()
    {
        return $this->belongsTo(\App\Models\Localidad::class);
    }

    /**
     * Un Accidente tiene varias Respuestas
     *
     * @return Respuesta
     */
    public function respuestas()
    {
        return $this->hasMany(\App\Models\Respuesta::class);
    }

    /**
     * Un Accidente tiene varias Derivaciones
     *
     * @return Derivacion
     */
    public function derivaciones()
    {
        return $this->hasMany(\App\Models\Derivacion::class);
    }

    /**
     * Un Accidente tiene varias Autorizaciones
     *
     * @return Autorizacion
     */
    public function autorizaciones()
    {
        return $this->hasMany(\App\Models\Autorizacion::class);
    }

    /**
     * Lugar Accidente
     *
     * @return string
     */
    public function getLugarAccidenteAttribute($value)
    {
        $first = [];
        if (trim($this->lugar) != '') {
            $first[] = $this->lugar;
        }
        $first[] = $this->localidad->nombre;
        $last[] = $this->localidad->distrito->nombre;
        $last[] = $this->localidad->distrito->departamento->nombre;

        return [implode(', ', $first), implode(', ', $last)];
    }

    /**
     * Realiza el paginado para las listas de los ABM's
     * en caso que se le pase un filtro, filtra por los campos
     * establecidos de la forma 'campo' like '%filtro%'
     */
    public function scopeFilteredPaginate($query, $filter = '', $count = 10)
    {
        if (trim($filter) != '') {
            $txt = str_replace(' ', '%', $filter);
            $txt = '%'.$txt.'%';
            $query->where('id', '=', $filter)
                  ->orWhere('caratula', 'like', $txt)
                  ->where('expediente_judicial', 'like', $txt)
                  ->orWhere('sumario_policial', 'like', $txt)
                  ->orWhere('otro', 'like', $txt)
                  ->orWhere('dependencia', 'like', $txt)
                  ->orWhere('lugar', 'like', $txt)
                  ->orWhere('tipo_camino', 'like', $txt)
                  ->orWhere('altura', 'like', $txt)
                  ->orWhere('codigo_postal', 'like', $txt)
                  ->orWhereHas('usuario', function ($q) use ($txt) {
                      $q->where(function ($query) use ($txt) {
                          return $query
                                ->where('nombres', 'like', $txt)
                                ->orWhere('apellidos', 'like', $txt);
                      });
                  })
                  ->orWhereHas('sede', function ($q) use ($txt) {
                      $q->where('nombre', 'like', $txt);
                  })
                  ->orWhereHas('institucion', function ($q) use ($txt) {
                      $q->where('nombre', 'like', $txt);
                  })
                  ->orWhereHas('localidad', function ($q) use ($txt) {
                      $q->where('nombre', 'like', $txt);
                  })
                  ->orWhereHas('localidad.distrito', function ($q) use ($txt) {
                      $q->where('nombre', 'like', $txt);
                  })
                  ->orWhereHas('localidad.distrito.departamento', function ($q) use ($txt) {
                      $q->where('nombre', 'like', $txt);
                  });
        }

        return $query->with(['sede', 'sede.institucion', 'localidad', 'localidad.distrito', 'estado', 'usuario'])->orderBy('created_at', 'desc')->paginate($count);
    }

    /**
     * Scope para obtener un listado de Accidentes filtrado por los campos recibidos
     * en un array "filters", se utiliza para devolver el listado json de
     * accidentes para el API SOAP
     */
    public function scopeListFiltered($query, $filters = [], $orderBy = null, $limit = 10, $offset = 0)
    {
        if (isset($filters) && is_array($filters)) {
            foreach ($filters as $key => $f) {
                if ($f['operator'] == 'like') {
                    $f['value'] = str_replace(' ', '%', $f['value']);
                    $f['value'] = '%'.$f['value'].'%';
                }
                $query->where($f['field'], $f['operator'], $f['value']);
            }
        }

        if (! isset($orderBy) || ! is_array($orderBy)) {
            $orderBy = ['field' => 'created_at', 'order' => 'desc'];
        }

        return $query->with(['sede', 'sede.institucion', 'localidad', 'localidad.distrito', 'estado', 'usuario', 'respuestas'])
                     ->orderBy($orderBy['field'], $orderBy['order'])->limit($limit)->offset($offset);
    }

    public function scopeUltimosTreintaDias($query)
    {
        $query->where('fecha_accidente', '>=', Carbon::now()->subDays(30));
    }

    public function scopeUltimosTresMeses($query)
    {
        $query->where('fecha_accidente', '>=', Carbon::now()->subDays(30 * 3));
    }
}
