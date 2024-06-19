<?php

use App\Jobs\DeleteOldFilesFromLocalStorage;
use App\Jobs\HandleUnhandledFrames;
use App\Jobs\RecreateCameraRestreamingProcesses;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new HandleUnhandledFrames())->everyFiveMinutes();
Schedule::job(new RecreateCameraRestreamingProcesses())->everyMinute();

// We call this job at the end of the day to check if the parent folders are empty or not and delete
// If we call this at 00:00 of next day, the video creation
// job may not be created some data and that folder will be removed too
//Schedule::job(new DeleteOldFilesFromLocalStorage())->dailyAt('23:59');


