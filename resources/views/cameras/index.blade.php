@extends('layouts.app')

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="row">
        <div class="col-12">
            <div id="camerasTableWrapper" class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Cameras table</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">

                        <div id="loader" style="display: none">
                            <span class="loader"></span>
                        </div>

                        <table id="camera-discovery-table" class="table align-items-center mb-0">
                            <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Preview</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Host</th>
                                <th class="text-secondary opacity-7">Action</th>
                            </tr>
                            </thead>
                            <tbody id="camera-discovery-table-body">
                            @foreach($cameras as $camera)
                                <tr id="cameraId-{{$camera->id}}">
                                    <td>
                                        <img src="">
                                    </td>
                                    <td>
                                        <span>{{ $camera->url }}</span>
                                    </td>
                                    <td>
                                        <form method="post" action="{{ route('cameras.delete', ['camera' => $camera->id]) }}">
                                            @csrf
                                            {{ method_field('DELETE') }}
                                            <input type="submit" class="btn btn-danger" value="Delete Camera">
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
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
        const socket = io('{{ config('streaming.socket_url') }}?camera_id=all');

        socket.on('connect', () => {
            console.log(`Connected to socket.io server with id: ${socket.id}`);
        });

        socket.on('disconnect', () => {
            console.log('Disconnected from socket.io server');
        });

        socket.on('frame', (data) => {
            const cameraId = data.camera.id;
            const imgElement = document.querySelector(`#cameraId-${cameraId} img`);

            if (imgElement) {
                imgElement.src = `data:image/jpeg;base64,${data.frame}`;
            }
        });
    </script>
@endsection
