<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SiesaWSController;

class TareaPlano extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'WSSiesa:Importar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Plano retener facturas';

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
     * @return int
     */
    public function handle()
    {
        $Procesar = new SiesaWSController;
        $Procesar->WS_SIESA(1);
    }
}
