<?php

namespace App\Models;

use Auth;
use Shanmuga\LaravelEntrust\Models\EntrustRole;
use Venturecraft\Revisionable\RevisionableTrait;

class Rol extends EntrustRole
{
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;

    protected $fillable = ['name', 'display_name', 'description'];

    /**
     * Relación usuarios
     *
     * @return [type] [description]
     */
    public function usuarios()
    {
        return $this->belongsToMany(\App\Models\Usuario::class, 'rol_usuario', 'rol_id', 'usuario_id');
    }

    /**
     * Relación permisos
     *
     * @return [type] [description]
     */
    public function permisos()
    {
        return $this->belongsToMany(\App\Models\Permiso::class, 'permiso_rol', 'rol_id', 'permiso_id');
    }

    /**
     * Realiza el paginado para las listas de los ABM's
     * en caso que se le pase un filtro, filtra por los campos
     * establecidos de la forma 'campo' like '%filtro%'
     */
    public function scopeFilteredPaginate($query, $filter = '', $count = 10)
    {
        if (trim($filter) !== '') {
            $query->where('name', 'like', '%'.$filter.'%')
                    ->orWhere('display_name', 'like', '%'.$filter.'%')
                    ->orWhere('description', 'like', '%'.$filter.'%')
                    ->orWhere('id', 'like', '%'.$filter.'%');
        }

        return $query->paginate($count);
    }

    public function scopeRolList($query)
    {
        //CHECK USER INSTITUTION
        if (! Auth::user()->hasRole('sysadmin')) {
            $query->where('name', '<>', 'sysadmin');
        }
    }
}
