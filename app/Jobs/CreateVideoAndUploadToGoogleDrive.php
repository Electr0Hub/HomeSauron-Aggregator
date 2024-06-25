<?php
namespace App\Jobs;

use App\Models\Camera;
use App\Models\Setting;
use App\Models\Video;
use Exception;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Google\Service\Exception as GoogleServiceException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateVideoAndUploadToGoogleDrive implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Camera $camera,
        protected string $operationId,
        protected int $frameRate,
        protected bool $isUnhandled = false)
    {
    }

    public function uniqueId(): string
    {
        return $this->camera->id . '-' . $this->operationId;
    }

    public function handle(): void
    {
        $cameraDir = config('streaming.localstorage.path') . DIRECTORY_SEPARATOR . str_replace(' ', '_', $this->camera->name);
        $cameraFramesDir = $cameraDir . DIRECTORY_SEPARATOR . '_frames_' . $this->operationId;
        $cameraFramesToMakeVideoDir = $cameraDir . DIRECTORY_SEPARATOR . '_processing_frames_' . $this->operationId;
        $videosArchiveDir = $cameraDir . DIRECTORY_SEPARATOR . 'videos';
        $nowAsPath = date('Y') . DIRECTORY_SEPARATOR . date('M') . DIRECTORY_SEPARATOR . date('d');

        if (!file_exists($videosArchiveDir . DIRECTORY_SEPARATOR . $nowAsPath)) {
            mkdir($videosArchiveDir . DIRECTORY_SEPARATOR . $nowAsPath, 0755, true);
        }

        $newVideoPath = $videosArchiveDir . DIRECTORY_SEPARATOR . $nowAsPath . DIRECTORY_SEPARATOR . date('H:i:s');

        if ($this->isUnhandled) {
            $newVideoPath .= '_unhandled_' . Str::random(6);
        }

        $newVideoPath .= '.mp4';

        $framePattern = $cameraFramesToMakeVideoDir . '/frame_%06d.jpg';

        if (!file_exists($cameraFramesDir)) {
            return;
        }

        File::move($cameraFramesDir, $cameraFramesToMakeVideoDir);

        $ffmpegCmd = "ffmpeg -framerate $this->frameRate -i $framePattern -c:v libx264 -pix_fmt yuv420p $newVideoPath > /dev/null 2>&1";
        exec($ffmpegCmd);

        File::deleteDirectory($cameraFramesToMakeVideoDir);

        if (Camera::getSettingCache($this->camera->id, 'upload_to_google_drive', false)) {
            $this->uploadToGoogleDrive($newVideoPath);
        }

        if (!Camera::getSettingCache($this->camera->id, 'store_in_localstorage', false)) {
            File::delete($newVideoPath);
        } else {
            $name = explode(DIRECTORY_SEPARATOR, $newVideoPath);
            Video::create([
                'camera_id' => $this->camera->id,
                'name' => end($name),
                'absolute_path' => $newVideoPath,
            ]);
        }
    }

    protected function uploadToGoogleDrive(string $filePath): void
    {
        $client = $this->getGoogleClient();

        $service = new Drive($client);

        $relativePath = str_replace('videos/', '', str_replace(config('streaming.localstorage.path') . DIRECTORY_SEPARATOR, '', $filePath));
        $pathParts = explode('/', dirname($relativePath));
        $fileName = basename($filePath);

        $parentId = config('streaming.google_drive.parent_folder_id');
        foreach ($pathParts as $part) {
            $parentId = $this->getOrCreateFolder($part, $parentId, $service);
        }

        $fileMetadata = new DriveFile([
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

        if (!Camera::getSettingCache($this->camera->id, 'store_in_localstorage', false)) {
            File::delete($filePath);
        }
    }

    protected function getOrCreateFolder(string $name, string $parentId, Drive $service): string
    {
        $query = sprintf("name = '%s' and '%s' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false", $name, $parentId);
        $response = $service->files->listFiles(['q' => $query]);

        if (count($response->files) > 0) {
            return $response->files[0]->id;
        }

        $folderMetadata = new DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId]
        ]);

        $folder = $service->files->create($folderMetadata, ['fields' => 'id']);

        return $folder->id;
    }

    /**
     * @throws Exception
     */
    protected function getGoogleClient(): Client
    {
        $client = new Client();
        $client->setAccessToken($this->getAccessToken());

        if ($client->isAccessTokenExpired()) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($this->getRefreshToken());
            Setting::updateOrCreate(['key' => Setting::GOOGLE_DRIVE_ACCESS_TOKEN], ['value' => json_encode($newToken)]);
            $client->setAccessToken($newToken);
        }

        $client->addScope(Drive::DRIVE);
        return $client;
    }

    /**
     * @throws Exception
     */
    protected function getAccessToken(): array
    {
        $token = Setting::getValue(Setting::GOOGLE_DRIVE_ACCESS_TOKEN);

        if(is_null($token)) {
            throw new Exception('Access token is missing. Run: php artisan auth:google-drive');
        }

        return json_decode($token, true);
    }

    protected function getRefreshToken(): string
    {
        return Setting::getValue(Setting::GOOGLE_DRIVE_ACCESS_TOKEN);
    }
}
