<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetAppServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh migrations, Seed and delete all logs';

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
        $this->call("experiment:logs:clear");
        $this->info("Logs cleared");
        $this->call("migrate:refresh");
        $this->call("db:seed");
    }
}
