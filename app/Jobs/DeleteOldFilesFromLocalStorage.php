<?php

namespace App\Jobs;

use App\Models\Camera;
use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class DeleteOldFilesFromLocalStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Get all cameras
        $cameras = Camera::select('id', 'settings')->get();

        foreach ($cameras as $camera) {
            // Get the local storage days setting for the camera
            $daysToKeep = $camera->settings['localstorage_store_for_days'];

            // Calculate the cutoff date
            $cutoffDate = now()->subDays($daysToKeep);

            // Query videos that are older than the cutoff date
            $videos = Video::select('id', 'absolute_path')
                ->whereCameraId($camera->id)
                ->where('created_at', '<', $cutoffDate)
                ->get();

            foreach ($videos as $video) {
                // Get the absolute path of the video
                $videoPath = $video->absolute_path;

                // Delete the video file
                if (File::exists($videoPath)) {
                    File::delete($videoPath);
                }

                // Delete the video record from the database
                $video->delete();

                // Delete the parent folder if it's empty (day level)
                $dayFolder = dirname($videoPath);
                $this->deleteIfEmpty($dayFolder);

                // Delete the parent folder if it's empty (month level)
                $monthFolder = dirname($dayFolder);
                $this->deleteIfEmpty($monthFolder);

                // Delete the parent folder if it's empty (year level)
                $yearFolder = dirname($monthFolder);
                $this->deleteIfEmpty($yearFolder);
            }
        }
    }

    /**
     * Delete the folder if it's empty.
     *
     * @param string $folder
     * @return void
     */
    private function deleteIfEmpty($folder): void
    {
        if (is_dir($folder) && !(new \FilesystemIterator($folder))->valid()) {
            File::deleteDirectory($folder);
        }
    }
}
