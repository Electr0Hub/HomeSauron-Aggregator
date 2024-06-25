@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h1>Incoming Google OAuth Data</h1>
        <div class="row">
            <div class="col-md-12">
                @foreach($data as $key => $value)
                    <p><b>{{ $key }}: </b> {{ $value }}</p>
                @endforeach
            </div>
        </div>
    </div>
@endsection
