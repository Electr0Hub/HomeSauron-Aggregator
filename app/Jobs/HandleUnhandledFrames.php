<?php

namespace App\Jobs;

use App\Models\Camera;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Handle frames which were not handled. That may be in case of connection lost while collecting frames.
 *
 * @example when \App\Console\Commands\RestreamCamera started to collect frames from camera
 * and before dispatching \App\Jobs\CreateVideoAndUploadToGoogleDrive job the connection with camera lost.
 * The RestreamCamera will not dispatch \App\Jobs\CreateVideoAndUploadToGoogleDrive job because not all frames were collected.
 * In that case there will be _frames folder with some frames in it.
 * This class will check for old folders containing frames and pass them to \App\Jobs\CreateVideoAndUploadToGoogleDrive
 */
class HandleUnhandledFrames implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cameras = Camera::all();

        foreach ($cameras as $camera) {
            $cameraDir = config('streaming.localstorage.path') . DIRECTORY_SEPARATOR . str_replace(' ', '_', $camera->name);

            if(! File::exists($cameraDir)) {
                continue;
            }

            try {
                $dirsInCameraDir = File::directories($cameraDir);

                foreach ($dirsInCameraDir as $dir) {
                    if(Str::endsWith($dir, 'videos')) {
                        continue;
                    }

                    $secondsSinceLastModified = now()->unix() - File::lastModified($dir);

                    if($secondsSinceLastModified >= 60 * 20) {  // 20 mins
                        $segments = explode('/', $dir);
                        $last = end($segments);
                        $operationId = Str::after($last, '_frames_');
                        CreateVideoAndUploadToGoogleDrive::dispatch($camera, $operationId, 60, true);
                    }
                }
            }
            catch (\Exception $e) {

            }
        }
    }
}
