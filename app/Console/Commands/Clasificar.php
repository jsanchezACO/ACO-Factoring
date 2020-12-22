<?php

namespace App\Console\Commands;

use App\Http\Controllers\SiesaWSController;
use Illuminate\Console\Command;

class Clasificar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'WSSiesa:Clasificar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Plano para reclasificar terceros';

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
        $Procesar->WS_SIESA(3);
    }
}
