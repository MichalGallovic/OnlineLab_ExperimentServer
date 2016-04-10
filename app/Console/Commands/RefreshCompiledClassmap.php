<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/*
* Add this to $commands array
* in App\Console\Kernel
*
* http://stackoverflow.com/questions/30819934/laravel-migrations-class-not-found
*/
class RefreshCompiledClassmap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compiled:refresh';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh compiled classmap';
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
        // Clears all compiled files
        $this->call('clear-compiled');
        // Updates autoload_psr4.php, Almost empties autoload_classmap.php
        shell_exec("composer dump-autoload");
        // Updates autoload_classmap.php
        $this->call('optimize');
    }
}