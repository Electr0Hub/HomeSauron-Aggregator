<?php

namespace App\Jobs;

use App\Models\Camera;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Sometimes the process of restreaming may be killed because of many issues.
 * For example the restreamer works with X camera. The camera lost connection with WiFi and could recover it after 5 mins
 * This job will wake up once a min, list the processes and compare with each camera ID from database. If there is a camera
 * in database but the process is missing - it'll run it
*/
class RecreateCameraRestreamingProcesses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cameras = Camera::select('id')->get();

        foreach ($cameras as $camera) {
            $cameraId = $camera->id;

            exec("ps aux | grep 'artisan restream:camera --camera=$cameraId' | grep -v grep | awk '{print $2}'", $output);

            if (count($output) === 0) {
                exec("php " . base_path('artisan') . " restream:camera --camera=$cameraId > /dev/null 2>&1 &");
            }
        }
    }
}
