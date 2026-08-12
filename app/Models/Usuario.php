<?php

namespace App\Models;

use Auth;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Shanmuga\LaravelEntrust\Traits\LaravelEntrustUserTrait;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Notifications\Notifiable;

class Usuario extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, RevisionableTrait, HasFactory, Notifiable;
    use LaravelEntrustUserTrait {
        roles as rolesNotUsed;
    }

    protected $revisionCreationsEnabled = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sede_id',
        'documento',
        'genero',
        'nombres',
        'apellidos',
        'telefonos',
        'direccion',
        'username',
        'email',
        'password',
        'activo',
        'cambiar_password',
        'api_key',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'activo' => 'boolean',
        'cambiar_password' => 'boolean',
    ];

    /**
     * Campos de tipo fecha
     *
     * @var array
     */
    public static function boot()
    {
        parent::boot();
    }

    /**
     * Relación roles
     *
     * @return [type] [description]
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('entrust.models.role'), Config::get('entrust.tables.role_user'), 'usuario_id', 'rol_id');
    }

    /**
     * Un Usuario pertenece a una Sede
     *
     * @return Sede
     */
    public function sede()
    {
        return $this->belongsTo(\App\Models\Sede::class);
    }

    /**
     * Un usuario tiene muchas derivaciones
     *
     * @return Derivacion
     */
    public function derivaciones_enviadas()
    {
        return $this->hasMany(\App\Models\Derivacion::class, 'usuario_id');
    }

    /**
     * Un usuario reciba muchas derivaciones
     *
     * @return Derivacion
     */
    public function derivaciones_recibidas()
    {
        return $this->hasMany(\App\Models\Derivacion::class, 'deriva_a_usuario_id');
    }

    /**
     * Un usuario tiene muchas autorizaciones
     *
     * @return Autorizacion
     */
    public function autorizaciones_enviadas()
    {
        return $this->hasMany(\App\Models\Autorizacion::class, 'otorga_usuario_id');
    }

    /**
     * Un usuario recibe muchas autorizaciones
     *
     * @return Autorizacion
     */
    public function autorizaciones_recibidas()
    {
        return $this->hasMany(\App\Models\Autorizacion::class, 'recibe_usuario_id');
    }

    /**
     * Un usuario tiene muchos reportes
     *
     * @return Reporte
     */
    public function reportes()
    {
        return $this->hasMany(\App\Models\Reporte::class);
    }

    /**
     * Un usuario tiene muchos reportes de datos
     *
     * @return Reporte
     */
    public function reporte_datos()
    {
        return $this->hasMany(\App\Models\ReporteDatos::class);
    }

    /**
     * Un usuario tiene muchos logins
     *
     * @return Derivacion
     */
    public function logins()
    {
        return $this->hasMany(\App\Models\Login::class);
    }

    /**
     * Un usuario tiene muchas notificaciones
     *
     * @return Notificacion
     */
    public function notificaciones()
    {
        return $this->hasMany(\App\Models\Notificacion::class);
    }

    /**
     * Retorna Número de Documento formateado
     *
     * @return string
     */
    public function numeroDocumento()
    {
        return (string) $this->documento == '' ? '&mdash;' : number_format($this->documento, 0, ',', '.');
    }

    /**
     * Retorna el Nombre y Apellido
     *
     * @return  string
     */
    public function nombreCompleto()
    {
        return $this->nombres.' '.$this->apellidos;
    }

    /**
     * Retorna la bienvenida para mostrar en la interface
     *
     * @return string
     */
    public function bienvenida()
    {
        $letra = '@';
        if ($this->genero === 'Masculino') {
            $letra = 'o';
        } elseif ($this->genero === 'Femenino') {
            $letra = 'a';
        }

        return 'Bienvenid'.$letra;
    }

    /**
     * Concatena el nombre de la insitucion - sede para mostrar en combo
     *
     * @return string
     */
    public function nombreInstitucionSede()
    {
        return $this->sede->institucion->nombre.' - '.$this->sede->nombre;
    }

    /**
     * Realiza el paginado para las listas de los ABM's
     * en caso que se le pase un filtro, filtra por los campos
     * establecidos de la forma 'campo' like '%filtro%'
     */
    public function scopeFilteredPaginate($query, $filter = '', $count = 10)
    {
        //CHECK USER INSTITUTION
        if (! Auth::user()->hasRole('sysadmin')) {
            $query->whereHas('sede.institucion', function ($q) {
                $q->where('id', '=', Auth::user()->sede->institucion->id);
            });
        }

        if (trim($filter) !== '') {
            $query->where(function ($q) use ($filter) {
                $q->where('nombres', 'like', '%'.$filter.'%')
                    ->orWhere('apellidos', 'like', '%'.$filter.'%')
                    ->orWhere('id', 'like', '%'.$filter.'%')
                    ->orWhere('documento', 'like', '%'.$filter.'%')
                    ->orWhere('username', 'like', '%'.$filter.'%')
                    ->orWhere('email', 'like', '%'.$filter.'%')
                    ->orWhere('telefonos', 'like', '%'.$filter.'%')
                    ->orWhereHas('sede', function ($q) use ($filter) {
                        $q->where('nombre', 'like', '%'.$filter.'%');
                    })
                    ->orWhereHas('sede.institucion', function ($q) use ($filter) {
                        $q->where('nombre', 'like', '%'.$filter.'%');
                    });
            });
        }

        return $query->with('sede')->with('sede.institucion')->paginate($count);
    }

    public function attachRole($role) {
        $this->roles()->attach($role);
    }
}
