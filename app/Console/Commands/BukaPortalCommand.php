<?php


namespace App\Console\Commands;

use App\Http\Controllers\PoliklinikController;
use \Illuminate\Console\Command;

class BukaPortalCommand extends Command
{
    /**
     * @var string
     * The console command name.
     * */
    protected $name = 'portal:buka';

    /**
     * @var string
     * The console command description.
     */
    protected $description = 'Buka Portal Poliklinik';

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        $poliklinik = new PoliklinikController();
        $poliklinik->bukaPortal();
    }
}
