<?php
namespace App\Console\Commands;

use App\Jobs\CreateVideoAndUploadToGoogleDrive;
use Carbon\Carbon;
use Google\Service\Exception;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Models\Camera;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RestreamCamera extends Command
{
    protected $signature = 'restream:camera {--camera=}';
    protected $description = 'Restream camera frames';
    protected string $cameraDir;
    protected Carbon $startTime;
    protected array $config;
    protected int $frameRate = 0;

    public function __construct()
    {
        parent::__construct();
        $this->config = config('streaming');
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {

        try {
            // Retrieve the camera option
            $cameraOption = $this->option('camera');

            // Check if the camera option is provided
            if (!$cameraOption) {
                $this->error('The --camera option is required.');
                return;
            }

            // Sometimes for some unknown reason supervisor creates many times the same process for streaming a camera.
            // Here we check, if there is already a process for this camera - just cancel it.
            exec("ps aux | grep 'artisan restream:camera --camera=$cameraOption' | grep -v grep | awk '{print $2}'", $output);
            if (count($output) !== 0) {
                exit();
            }

            // Fetch the camera(s) based on the option
            $camera = Camera::find($cameraOption);

            if (is_null($camera)) {
                $this->error('No cameras found with the provided option.');
                return;
            }

            $client = new Client();
            $this->cameraDir = $this->config['localstorage']['path'] . DIRECTORY_SEPARATOR . str_replace(' ', '_', $camera->name);
            $this->startTime = Carbon::now();

            $this->restreamCamera($client, $camera);
        } catch (\Exception $exception) {
            Log::channel("streams")->error($exception);
            throw $exception;
        }
    }

    protected function restreamCamera(Client $client, Camera $camera): void
    {
        $url = $camera->url;

        $client->getAsync($url, [
            'stream' => true,
            'sink' => fopen('php://temp', 'r+')
        ])->then(
            function (Response $response) use ($client, $camera) {
                $this->info(now() . ' Streaming frame from ' . $camera->id);
                $body = $response->getBody();
                $frameBuffer = '';
                $frameCount = 0;
                $operationId = Str::random(32);

                while (!$body->eof()) {
                    // Read a chunk of data
                    $frame = $body->read(1024); // Adjust the buffer size as needed

                    $frameBuffer .= $frame;

                    // Check if the buffer contains more than one boundary
                    if (substr_count($frameBuffer, $this->config['boundary']) > 1) {
                        // Extract data between boundaries
                        $jpegData = $this->getDataBetweenBoundary($frameBuffer, $this->config['boundary'], $this->config['boundary']);

                        // Find the JPEG start marker
                        $jpegStart = strpos($jpegData, "\xFF\xD8");
                        if ($jpegStart !== false) {
                            // Extract the JPEG data from the start marker
                            $jpegData = substr($jpegData, $jpegStart);

                            $this->publishToRedis($camera, $jpegData);

                            if($this->shouldHandleVideoFiles($camera)) {
                                $currentFramesDir = $this->cameraDir . DIRECTORY_SEPARATOR . '_frames_' . $operationId;
                                $frameFileName =  $currentFramesDir . DIRECTORY_SEPARATOR . sprintf('frame_%06d.jpg', $frameCount++);

                                if (!file_exists($currentFramesDir)) {
                                    try {
                                        mkdir($currentFramesDir, 0755, true);
                                    }
                                    catch (\Exception $exception) {
                                        exit($exception->getMessage());
                                    }
                                    $this->info(now() . ' Created directory: ' . $currentFramesDir);
                                }
                                file_put_contents($frameFileName, $jpegData);

                                $diffInSeconds = $this->startTime->diffInSeconds(now());
                                if ($diffInSeconds >= $this->config['seconds_to_collect_frames']) {
                                    $this->createVideoFromFrames($camera, $operationId, $frameCount / $diffInSeconds);

                                    $this->startTime = Carbon::now(); // Reset the start time
                                    $frameCount = 0; // Reset frame count
                                    $operationId = Str::random(32);
                                    $this->info(now() . ' Video archive or google upload enabled. Video job dispatched');
                                }
                            }

                            // Reset the buffer to remove processed data
                            $frameBuffer = substr($frameBuffer, strpos($frameBuffer, $this->config['boundary'], strpos($frameBuffer, $this->config['boundary']) + 1));
                        }
                    }
                }
            },
            function ($error) {
                // Handle the error
                $this->error($error);
            }
        )->otherwise(function (\ErrorException $error) {
            $this->error($error);
        });
    }

    /**
     * @throws \Google\Exception
     * @throws Exception
     */
    protected function createVideoFromFrames(Camera $camera, string $operationId, int $frameRate = 60): void
    {
        CreateVideoAndUploadToGoogleDrive::dispatch($camera, $operationId, $frameRate);
    }

    protected function getDataBetweenBoundary($string, $start, $end): string
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    protected function publishToRedis(Camera $camera, string $jpegData): void
    {
        $dataToPublish = [
            'frame' => base64_encode($jpegData),
            'camera' => [
                'id' => $camera->id,
                'name' => $camera->name,
                'url' => $camera->url,
            ],
        ];

        Redis::publish("camera_stream:$camera->id", json_encode($dataToPublish));
    }

    protected function shouldHandleVideoFiles(Camera $camera): bool
    {
        return // todo true to false
            Camera::getSettingCache($camera->id, 'upload_to_google_drive', true) ||
            Camera::getSettingCache($camera->id, 'store_in_localstorage', false);
    }
}
