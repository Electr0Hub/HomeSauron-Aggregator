@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <a class="btn btn-outline-primary btn-sm mb-0 me-3" href="{{ route('cameras.create') }}">Add New Camera</a>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <div class="container-fluid" id="cameraContainer">

    </div>
@endsection

@section('scripts')
    <script src="https://cdn.socket.io/4.4.0/socket.io.min.js"></script>
    <script>
        const cameraMonitorTemplate = `{!! addslashes(view('components.camera_monitor')) !!}`;
        const socket = io('{{ config('streaming.socket_url') }}?camera_id=all');
        const cameraContainer = document.getElementById('cameraContainer');

        socket.on('frame', (data) => {
            const imgElement = document.getElementById(`camera-${data.camera.id}`);
            if (imgElement) {
                imgElement.src = `data:image/jpeg;base64,${data.frame}`;
            }
            else {
                createImgElement(data.camera)
            }
        });

        socket.on('connect', () => {
            console.log(`Connected to socket.io server with id: ${socket.id}`);
        });

        socket.on('disconnect', () => {
            console.log('Disconnected from socket.io server');
        });

        function createImgElement(camera) {
            const cameraDiv = document.createElement('div');
            cameraDiv.classList.add('col-md-3');
            cameraDiv.innerHTML = cameraMonitorTemplate.replaceAll('%cameraId%', camera.id)
            const cameraDataDiv = cameraDiv.querySelector('.camera-data');

            Object.keys(camera).forEach(key => {
                if (key !== 'id') {
                    const dataItem = document.createElement('div');
                    dataItem.textContent = `${key}: ${camera[key]}`;
                    cameraDataDiv.appendChild(dataItem);
                }
            });

            cameraContainer.appendChild(cameraDiv);
        }
    </script>
@endsection
