<?php

return [
    'socket_url' => env('SOCKET_URL'),
    'boundary' => env('FRAME_BOUNDARY', ''),
    'local_path' => env('VIDEOS_LOCAL_PATH', ''),
    'seconds_to_collect_frames' => env('SECONDS_TO_COLLECT_FRAMES', 60),
    'google_drive' => [
        'parent_folder_id' => env('GOOGLE_DRIVE_PARENT_FOLDER_ID', 'root'),
    ],
    'localstorage' => [
        'path' => env('LOCAL_STORAGE_PATH', storage_path('app/cameras')),
    ],
];
