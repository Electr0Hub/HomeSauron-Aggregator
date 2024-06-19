@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <button class="btn btn-outline-success btn-sm mb-0 me-3" id="startCamerasDiscovery">Start Cameras Discovery</button>
            <button class="btn btn-outline-danger btn-sm mb-0 me-3" style="display: none" id="stopCamerasDiscovery">Stop Cameras Discovery</button>
            <div style="display: none" id="camerasTableWrapper" class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Found Cameras</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">

                        <div id="loader" style="display: none">
                            <span class="loader"></span>
                        </div>

                        <table id="camera-discovery-table" class="table align-items-center mb-0">
                            <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Host</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-secondary opacity-7">Action</th>
                            </tr>
                            </thead>
                            <tbody id="camera-discovery-table-body">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.socket.io/4.4.0/socket.io.min.js"></script>

    <script>
        const tableRow = `<tr class="camera-discovery-result-row">
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">__HOST__</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <span class="badge badge-sm bg-gradient-success">Online</span>
                                </td>
                                <td class="align-middle">
                                    <a href="{!! route('cameras.create', ['camera_host' => '__HOST__/stream']) !!}" target='_blank' class="btn btn-outline-primary btn-sm mb-0 me-3">
                                        Add This Camera
                                    </a>
                                </td>
                            </tr>`;

        const socket = io('{{ config('streaming.socket_url') }}');

        socket.on('connect', () => {
            console.log(`Connected to socket.io server with id: ${socket.id}`);
        });

        socket.on('disconnect', () => {
            console.log('Disconnected from socket.io server');
        });

        socket.on('cameras-discovery:result', (data) => {
            const htmlToPaste = tableRow.replaceAll('__HOST__', data);
            const newRow = document.createElement('tr');
            newRow.innerHTML = htmlToPaste;

            const tableBody = document.getElementById('camera-discovery-table-body');
            tableBody.appendChild(newRow);
        })

        socket.on('cameras-discovery:finished', () => {
            console.log('FINISHED')
            document.getElementById('startCamerasDiscovery').style.display = 'block';
            document.getElementById('stopCamerasDiscovery').style.display = 'none';
            document.getElementById('loader').style.display = 'none';
        })

        document.getElementById('startCamerasDiscovery').addEventListener('click', function() {
            document.getElementById('camerasTableWrapper').style.display = 'block';
            document.getElementById('startCamerasDiscovery').style.display = 'none';
            document.getElementById('stopCamerasDiscovery').style.display = 'block';
            document.getElementById('loader').style.display = 'block';
            const rows = document.querySelectorAll('#camerasTableWrapper .camera-discovery-result-row');

            // Iterate over the selected rows and remove each from the table
            rows.forEach(row => row.remove());
            socket.emit('cameras-discovery:start');
        });

        document.getElementById('stopCamerasDiscovery').addEventListener('click', function() {
            document.getElementById('startCamerasDiscovery').style.display = 'block';
            document.getElementById('stopCamerasDiscovery').style.display = 'none';
            document.getElementById('loader').style.display = 'none';
            socket.emit('cameras-discovery:stop');
        });

    </script>
@endsection
