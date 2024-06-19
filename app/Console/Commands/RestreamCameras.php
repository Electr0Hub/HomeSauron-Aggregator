<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Camera;

class RestreamCameras extends Command
{
    protected $signature = 'restream:cameras';
    protected $description = 'Command to run all cameras restreamers';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info(now() . ' Waking up');
        // Kill all previous instances of the `restream:camera` command
        $this->killPreviousProcesses();

        // Retrieve all cameras
        $cameras = Camera::select('id')->get();

        foreach ($cameras as $camera) {
            $cameraId = $camera->id;
            $this->info(now() . ' Starting restream for camera ID: ' . $cameraId);

            // Execute the restream:camera command in the background
            exec("php artisan restream:camera --camera={$cameraId} > /dev/null 2>&1 &");
        }
    }

    /**
     * Kill all previous instances of the `restream:camera` command.
     */
    protected function killPreviousProcesses(): void
    {
        // Get all the PIDs of `restream:camera` processes
        exec("ps aux | grep 'artisan restream:camera ' | grep -v grep | awk '{print $2}'", $output);

        foreach ($output as $pid) {
            // Kill each process
            exec("kill -9 $pid");
        }
    }
}
