<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Device;
use App\Devices\Contracts\DeviceDriverContract;
use App\Devices\Exceptions\DeviceNotConnectedException;

class PingDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:devices:ping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Query and store deviceses state';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    
    protected $headers = ["Id","Name","Port","Status","Software"];

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
        $devices = Device::all();

        $outputRows = [];

        foreach ($devices as $device) {
            try {
                $status = $device->getStatus();
            } catch(DeviceNotConnectedException $e) {
                $status = DeviceDriverContract::STATUS_OFFLINE;
            }

            $outputRows [] = [
                $device->id,
                $device->type->name,
                $device->port,
                $status,
                $device->currentSoftwareName()
            ];
        }

        $this->table($this->headers, $outputRows);
    }
}
