<?php

namespace App\Jobs;

use App\Models\Camera;
use App\Models\Video;
use Google\Service\Exception;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateVideoAndUploadToGoogleDrive implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Camera $camera,
        protected string $operationId,
        protected int $frameRate,
        protected bool $isUnhandled = false
    )
    {}

    public function uniqueId(): string
    {
        return $this->camera->id . '-' . $this->operationId;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cameraDir = config('streaming.localstorage.path') . DIRECTORY_SEPARATOR . str_replace(' ', '_', $this->camera->name);
        $cameraFramesDir = $cameraDir . DIRECTORY_SEPARATOR . '_frames_' . $this->operationId;
        $cameraFramesToMakeVideoDir = $cameraDir . DIRECTORY_SEPARATOR . '_processing_frames_' . $this->operationId;
        $videosArchiveDir = $cameraDir . DIRECTORY_SEPARATOR . 'videos';
        $nowAsPath = date('Y') . DIRECTORY_SEPARATOR .
            date('M') . DIRECTORY_SEPARATOR .
            date('d');

        if(!file_exists($videosArchiveDir . DIRECTORY_SEPARATOR . $nowAsPath)) {
            mkdir($videosArchiveDir . DIRECTORY_SEPARATOR . $nowAsPath, 0755, true);
        }

        // This is the full path of new video
        $newVideoPath = $videosArchiveDir . DIRECTORY_SEPARATOR . $nowAsPath . DIRECTORY_SEPARATOR . date('H:i:s');

        if($this->isUnhandled) {
            $newVideoPath .= '_unhandled_' . Str::random(6);
        }

        $newVideoPath .= '.mp4';

        $framePattern = $cameraFramesToMakeVideoDir . '/frame_%06d.jpg';

        // To be sure queue is not killed
        if(!file_exists($cameraFramesDir)) {
            return;
        }

        File::move($cameraFramesDir, $cameraFramesToMakeVideoDir);

        // Use FFmpeg to create a video from the JPEG frames
        $ffmpegCmd = "ffmpeg -framerate $this->frameRate -i $framePattern -c:v libx264 -pix_fmt yuv420p $newVideoPath > /dev/null 2>&1";

        exec($ffmpegCmd);

        File::deleteDirectory($cameraFramesToMakeVideoDir);

        if (Camera::getSettingCache($this->camera->id, 'upload_to_google_drive', false)) {
            $this->uploadToGoogleDrive($newVideoPath);
        }

        if(!Camera::getSettingCache($this->camera->id, 'store_in_localstorage', false)) {
            File::delete($newVideoPath);
        }
        else {
            $name = explode(DIRECTORY_SEPARATOR, $newVideoPath);
            Video::create([
                'camera_id' => $this->camera->id,
                'name' => end($name),
                'absolute_path' => $newVideoPath,
            ]);
        }
    }

    /**
     * @throws Exception
     * @throws \Google\Exception
     */
    protected function uploadToGoogleDrive(string $filePath): void
    {
        $client = new Google_Client();
        $client->setAuthConfig(base_path('service-account.json'));
        $client->addScope(Google_Service_Drive::DRIVE);
        $service = new Google_Service_Drive($client);

        // Parse the directory structure from the file path
        $relativePath = str_replace('videos/', '', str_replace(config('streaming.localstorage.path') . DIRECTORY_SEPARATOR, '', $filePath));
        $pathParts = explode('/', dirname($relativePath));
        $fileName = basename($filePath);

        // Ensure the directory structure exists on Google Drive
        $parentId = config('streaming.google_drive.parent_folder_id');
        foreach ($pathParts as $part) {
            $parentId = self::getOrCreateFolder($part, $parentId, $service);
        }

        // Create the file metadata
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $fileName,
            'parents' => [$parentId]
        ]);

        $content = file_get_contents($filePath);

        $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => 'application/octet-stream',
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);

        if(!Camera::getSettingCache($this->camera->id, 'store_in_localstorage', false)) {
            File::delete($filePath);
        }
    }

    /**
     * @throws Exception
     */
    protected function getOrCreateFolder(string $name, string $parentId, Google_Service_Drive $service): string
    {
        // Search for the folder
        $query = sprintf("name = '%s' and '%s' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false", $name, $parentId);
        $response = $service->files->listFiles(['q' => $query]);

        // Return the folder ID if it exists
        if (count($response->files) > 0) {
            return $response->files[0]->id;
        }

        // Create the folder if it does not exist
        $folderMetadata = new Google_Service_Drive_DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId]
        ]);

        $folder = $service->files->create($folderMetadata, ['fields' => 'id']);
        return $folder->id;
    }
}
