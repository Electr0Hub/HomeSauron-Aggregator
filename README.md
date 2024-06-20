# HomeSauron Aggregator
### The missing control panel and aggregator for ESP32 cameras 😎

This project is an aggregator, control panel, and restreamer for ESP32 or other mjpeg streaming cameras. It allows you to manage and stream videos from multiple cameras, providing features for local and cloud storage, camera control, and more.

To use it easily use modified ESP32 CameraWebServer project - an [EYE](https://github.com/Electr0Hub/HomeSauron-Eye) of HomeSauron

<p align="center">
  <img src="https://github.com/Electr0Hub/HomeSauron-Aggregator/assets/22774727/fd44a637-4f80-45d3-b1eb-cbff3139db9f" width="300" style="max-width:100%;" alt="logo">
</p>

## Philosophy
Here is why this project created, which problem it solves and where and how can be used.

##### The Problem
I decided to make DIY surveillance system with ESP32 cameras by just installing them at my house, but I got a serious problem.
We all (or some of us) know that when you stream mjpeg from your ESP32, only one HTTP connection can read that stream. For example if your camera stream ran at http://192.168.1.55:81/stream and you open that page, the other user cannot do that, because mjpeg stream is endless and the response is not sent to you, the other user will wait endless time to get a response from a camera. If you have installed ESP32 at your house, only one person can watch the camera and this is a real problem.

##### The Idea
Decided to create a system, which will read the stream once, i.e. open the streaming connection and forward incoming frame data to all connected users. Since there can be only one active connection reading frames from ESP32 mjpeg stream, decided to make it that way, so there is one linux process for one camera. Laravel fits best for running such processes in background, control them, provide ability for fast coding a simple dashboard, creating CRUD (Create, Read, Update, Delete) for cameras. In addition, decided to create uploading functionality, so we can store the videos into localstorage and Google Drive.

##### The Result
In the end I got the whole aggregator for cameras, which provides cameras CRUD, restreamer, uploader and scanner. My DIY surveillance system is ready, build your own now 😎

![image](https://github.com/Electr0Hub/HomeSauron-Aggregator/assets/22774727/2573b652-9398-42b4-b2b6-5c43767342b7)

## Features

- **Add, Edit, and Delete Cameras**: Manage your cameras easily through the control panel.
- **Camera Channels**: Each camera has its own channel, allowing you to request frames from specific cameras.
- **Local Storage**: Store videos locally on your server.
- **Google Drive Storage**: Store videos in Google Drive.
- **Auto-Remove Old Videos**: Automatically remove old videos from local storage based on camera settings.
- **Camera Discovery**: Scan for cameras in your network.
- **Continuous Video Processing**: Each camera runs a process that continuously captures frames and creates videos.
- **Process Monitoring**: Automatically restart processes for cameras that lose and regain connection.
- **Handle Unhandled Frames**: Process frames from disconnected cameras and save the resulting video as "unhandled".
- **Intercom Functionality**: Use a device like a pad or laptop as an intercom by navigating to the door camera page.
- **Configurable Video Length**: Set video length via a configuration file.
- **Camera Settings**: Configure camera settings such as upload to Google Drive, store in local storage, and days to keep videos.
- **ESP32 Camera Control**: Adjust camera settings like brightness, contrast, and quality and all stuff EPS32 provides.

## Tech Stack

- **Laravel**: Used for developing dashboard, HTTP handlers, restreamer, scheduled and queueable jobs, brodacaster
- **Redis**: Used for caching, queue management, and broadcasting JPEG frames to Socket.IO.
- **Node.js Socket.IO**: Emits JPEG frames to socket clients (browsers, mobile phones, intercom pads).
- **Docker**: Dockerizes the entire project for easy deployment.
- **FFmpeg**: Converts collected frames into video.
- **PostgreSQL**: Database management.
- **Linux**: Operating system. Recommended to run this on a machine which going to be run forever. Raspberry PI is the best choose for this. I didn't test on windows and not even going to do that.
- **Supervisor**: Manages various processes including Laravel app, queue, scheduler, restreamer processes, and PM2 with Socket.IO.
- **Nginx**: Forwards traffic to the Laravel application.

## Setup Instructions
All you need to have on your machine is Docker, assuming you already have it.
1. **Clone the Repository**:
    ```shell
    git clone git@github.com:Electr0Hub/HomeSauron-Aggregator.git
    cd HomeSauron-Aggregator
    ```

2. **Build and start the application**
    ```shell
    sudo make start
    ```
3. **Set env vars**
   
    Copy the example environment file and configure it with your own values.
    ```sh
    cp .env.example .env
    ```
   
    The following env must be set:
   ```shell
   SOCKET_PORT=
   SOCKET_URL= #Do not set the local IP, since the browser must to be able to connect
   GOOGLE_DRIVE_PARENT_FOLDER_ID= # If you plan to upload to gdrive, get the ID of the folder where you want to have all folders and videos. You can hind it in the URL of the folder (the latest segment)
   LOCAL_STORAGE_PATH= # If you plan to upload to your local storage, set the absolute path. Remember, this path is mounted as separated volume in docker-compose, so each time you change it you need to rebuild your app
   ```
4. **Install app**

    Connect to app container by:
    
    ```shell
    sudo make connect_app
    ```
   
    Then run the following commands one-by-one

    ```shell
   php artisan key:generate
   php artisan migrate
   ```

    Everything is ready. Your app, queue, scheduler, restreamer and socket.io are working. Just add some cameras from dashboard.

## Enabling google drive api
To be able to upload videos into google drive you need to:
1. Create a service account
2. Create JSON key file
3. Copy and paste it in app root service-account.json file
4. Share your google drive cameras folder with your service account. Get the email from JSON file (client email)

For more info google or chatgpt(just copy paste these and ask what to do)

## Folders Hierarchy
This is the hierarchy of local storage folders. Google drive hierarchy has the same view, except temp folder and video folder (YEAR folders are in CAMERA_NAME folders)
- cameras root folder (LOCAL_STORAGE_PATH)
    - CAMERA_NAME
        - _frames_OPERATION_ID (temp folder, currently collecting frames)
        - _processing_frames_OPERATION_ID (temp folder, already collected frames which are now going to be converted to viedo)
        - videos
          - YEAR
              - MONTH
                  - DAY
                      - VIDEO_FILE_NAME(hour, minutes, seconds)
                      - VIDEO_FILE_NAME(hour, minutes, seconds)
                      - VIDEO_FILE_NAME(hour, minutes, seconds)
                  - DAY
                      - VIDEO_FILE_NAME(hour, minutes, seconds)
                      - VIDEO_FILE_NAME(hour, minutes, seconds)
                      - VIDEO_FILE_NAME(hour, minutes, seconds)



## Interacting with ESP32 Cameras

Here you can understand how this application interacts with ESP32 cameras from the same network and how the things done.

For first, you add a camera from a dashboard. Before creating the camera resource, the backend checks if the host is available AND streams frames. 
Then the controller creates the camera, fires an event to start the camera restreaming and informs socket.io about new added camera by **camera_added** event, so the socket.io can emit to its clients the new camera content.

### Camera restreaming
This is how restreamer works:
1. The restreamer gets all cameras and creates a process for each one
2. The restreaming process (each camera has its own) creates a connection with ESP32 - a GET request
3. Reads incoming streaming response (which is endless) chunk by chunk (1024
4. Founds a JPEG data between boundary (by default its 123456789000000000000987654321 and you have not change it in ESP32 there is nothing to do, otherwise update .env file)
5. Publishes the frame to redis pubsub (each camera has its own topic there and socketio subscribed to all topics)
6. If upload to google drive or local storage is enabled, starts to collect frames X seconds (see config/streaming.php) and puts JPEG files into _frames_OPERATION_ID folder
7. If collected X seconds - runs a job to create a mp4 video from that frames and puts the resulting file into **videos** folder. The collected frames folder becomes from _frames_OPERATION_ID into _processing_frames_OPERATION_ID
8. If google drive upload enabled - uploads the file there and if local storage is DISABLED - removes the video file
9. Repeat

    
![Untitled Diagram drawio](https://github.com/Electr0Hub/HomeSauron-Aggregator/assets/22774727/ec166ca8-c3c2-44fb-bed4-3843359c4700)


### Handle unhandled frames
Each frame is JPEG image which going to be a part of video once the restreamer collected X(see config/streaming.php) seconds of frames. 
But there may be cases when the images created, but restream loop took less than X seconds. 
For example if X = 60seconds, the loop started and collected 30 seconds of frames and the camera lost a connection with wifi (bad man broke it or just connection issue),
the collected frames will not be handled, because the loop didnt take 60 seconds. In that case the app/Jobs/HandleUnhandledFrames.php job will do its job.

Here is how it works:
1. Runs every five minutes (see routes/console.php)
2. Gets all cameras
3. Goes to camera root folder
4. Check the folders (except videos) last modified date. See the [folders hierarchy here](https://github.com/Electr0Hub/HomeSauron-Aggregator?tab=readme-ov-file#folders-hierarchy)
5. If there is a folder which was not modified (adding/deleting files is modification too) for 20 mins, dispatches the job which creates the videos app/Jobs/CreateVideoAndUploadToGoogleDrive.php

![image](https://github.com/Electr0Hub/HomeSauron-Aggregator/assets/22774727/dae35aa1-d154-4e72-9b2c-9152328f435b)

### Recreate camera restreaming
As mentioned many times, the camera may lose the connection with aggregator. To keep the restreamer process continuously up, this job doing the following:
1. Gets all cameras IDs
2. Using ps aux command check if all cameras have running processes
3. If some camera doesn't - runs restreamer process in background for that camera

![image](https://github.com/Electr0Hub/HomeSauron-Aggregator/assets/22774727/23c0c7ce-17f2-475f-9806-98c1e725affe)

## The schema of project
This is the resulting schema how the things work

![Untitled Diagram drawio (1)](https://github.com/Electr0Hub/HomeSauron-Aggregator/assets/22774727/52f14179-8a78-4789-a5dd-2ae1d788161e)

## Versioning
It's a semantic versioning like X.Y.Z where X is vendor, Y major and Z minor changes. The vendor change is relatied with an [EYE](https://github.com/Electr0Hub/HomeSauron-Eye), while major and minor changes are for feature and bugfix changes

## Todos

- **Tests**: Implement unit and integration tests.
- **Google Drive Auto-Delete**: Write functionality to auto-delete old videos from Google Drive. Currently only localstorage has autodeletion feature.
- **Auto-Updater**: Implement an auto-update feature that checks for new tags on GitHub, pulls updates, runs migrations, and restarts necessary processes.

## Notes
1. You need to remember, that a video creation process is no that light-weight and there is a need in some CPU power
2. Each camera restreaming process is an **endless** process. Each process should be done in parallel (because we want realtime frames capturing). Remember, that adding a lot of cameras may failure the system - this one and your OS. It's recomended NOT to have more cameras then your CPU core. But you can do experiments and inform about that here by creating an issue.
3. You can set high quality of frames in camera page. Remember, that high quality videos may require more disk space. 1 min VGA (640x840) video takes about 10MB of disk space. And if you want to keep videos for camera for 7 days, it'll cost you about 98GB of data. Multiple it by 4 (avg num of cameras in the house) you'll be required to provide at least 400GB of space. And this is for a poor VGA format 🙂
4. DO not judge me for modified ugly interface, I'm backend developer only and the half of my time I spent to understand how to put the button where I want 🙂

## Kudos to
- [Creative Team](https://www.creative-tim.com) for making this beatiful UI and providing it for absolutely free
- [espressif](https://github.com/espressif) for making such powerful soft for ESP32 chips


## License

This software is under the CC BY-NC-ND 4.0 licence. For more info see: 
https://github.com/Electr0Hub/HomeSauron-Aggregator/blob/master/LICENCE
