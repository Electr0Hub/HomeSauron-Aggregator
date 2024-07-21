<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCameraRequest;
use App\Jobs\RecreateCameraRestreamingProcesses;
use App\Models\Camera;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class CameraController extends Controller
{
    public function index()
    {
        return view('cameras.index')->with([
            'cameras' => Camera::all(),
            'pageTitle' => 'Cameras'
        ]);
    }

    public function create()
    {
        return view('cameras.create')->with([
            'pageTitle' => 'Create New Camera',
        ]);
    }

    public function store(StoreCameraRequest $request): RedirectResponse
    {
        $parsedUrl = parse_url($request->input('url'));
        $hostName = gethostbyaddr($parsedUrl['host']);
        $camera = Camera::create([
            'name' => $request->input('name'),
            'hostname' => $hostName,
            'url' => $request->input('url'),
            'settings' => [
                'upload_to_google_drive' => $request->filled('upload_to_google_drive') && $request->input('upload_to_google_drive') === 'on',
                'store_in_localstorage' => $request->filled('store_in_localstorage') && $request->input('store_in_localstorage') === 'on',
                'google_store_for_days' => (int)$request->input('google_store_for_days', 0),
                'localstorage_store_for_days' => (int)$request->input('localstorage_store_for_days', 0),
            ]
        ]);

        RecreateCameraRestreamingProcesses::dispatch();

        Redis::publish("camera_added", $camera);

        return redirect()->route('dashboard.index')->with('success', 'Camera added successfully');
    }

    public function show(Camera $camera): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('cameras.show')->with([
            'pageTitle' => 'Camera ' . $camera->name,
            'camera' => $camera
        ]);
    }

    public function delete(Camera $camera): RedirectResponse
    {
        $camera->delete();
        $cameraId = $camera->id;
        exec("ps aux | grep 'artisan restream:camera --camera=$cameraId' | grep -v grep | awk '{print $2}'", $output);

        foreach ($output as $pid) {
            // Kill each process
            exec("kill -9 $pid");
        }

        Redis::publish("camera_deleted", $camera);

        return redirect()->route('dashboard.index')->with('success', 'Camera deleted successfully');
    }

    public function discover()
    {
        return view('cameras.discover')->with([
            'pageTitle' => 'Discover Cameras In Local Network',
        ]);
    }
}
