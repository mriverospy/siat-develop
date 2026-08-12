<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{

    public string $formato_formulario_unico;
    public int $dias_edicion;
    public string $logo_membrete;
    public string $logo_login;
    public string $logo_principal;
    public string $logo_cabecera;

    public static function group(): string
    {
        return 'general';
    }

    public function get($propName): string
    {
        return $this->$propName;
    }

    public function logo_membrete(): string
    {
        return $this->logo_membrete;
    }
}
