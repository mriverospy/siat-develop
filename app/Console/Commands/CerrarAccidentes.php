<?php

namespace App\Console\Commands;

use App\Models\Accidente;
use DB;
use Illuminate\Console\Command;

class CerrarAccidentes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accidentes:cerrar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cerrar accidentes abiertos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fechaCierreTope = date('Y-m-d H:i:s');

        $this->info('Inicio del Proceso de Cierre de Accidentes');

        $this->line('Verificando accidentes abiertos cuya fecha de cierre es menor a '.$fechaCierreTope);

        $accidentesCount = Accidente::abierto()->where('fecha_cierre', '<=', $fechaCierreTope)->count();

        $this->line($accidentesCount.' accidentes abierto(s) que deben ser cerrados');

        DB::transaction(function () use ($fechaCierreTope) {
            Accidente::abierto()
                ->where('fecha_cierre', '<=', $fechaCierreTope)
                ->update(['estado' => 'cerrado']);
        });

        $this->line($accidentesCount.' accidentes marcados como cerrado');

        $this->info('Fin del Proceso de Cierre de Accidentes');
    }
}
