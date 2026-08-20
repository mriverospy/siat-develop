<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.formato_formulario_unico', 'expandido');
        $this->migrator->add('general.dias_edicion', 5);
        $this->migrator->add('general.logo_membrete', 'img/accidente_cabecera_pdf.png');
        $this->migrator->add('general.logo_login', 'img/login-logo.png');
        $this->migrator->add('general.logo_principal', 'img/login-logo.png');
        $this->migrator->add('general.logo_cabecera', 'img/avalon-logo.png');
    }
}
