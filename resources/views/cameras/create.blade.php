@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <form method="post" action="{{ route('cameras.store') }}">
            @csrf
            <div class="input-group">
                <div class="col-md-3">
                    <label for="name">Name</label>
                    <input class="form-control" type="text" name="name" id="name" value="{{ old('name') }}">
                    @error('name')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="input-group">
                <div class="col-md-3">
                    <label for="url">URL</label>

                    <input class="form-control" type="text" name="url" id="url" value="{{ old('url', request()->query('camera_host', '')) }}">
                    @error('url')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>


            <div class="input-group">
                <label for="store_in_localstorage">Store in local storage</label>
                <div class="form-check form-switch ps-0">
                    <input class="form-check-input ms-auto" type="checkbox" name="store_in_localstorage" id="store_in_localstorage" {{ old('store_in_localstorage') ? 'checked' : '' }}>
                </div>
                @error('store_in_localstorage')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="input-group">
                <div class="col-md-3">
                    <label for="localstorage_store_for_days">How many days the video should be stored in local storage?</label>
                    <input class="form-control" type="number" min="1" max="30" name="localstorage_store_for_days" id="localstorage_store_for_days" value="{{ old('localstorage_store_for_days', 14) }}">
                    @error('localstorage_store_for_days')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>


            <div class="input-group">
                <label for="upload_to_google_drive">Upload to Google Drive</label>
                <div class="form-check form-switch ps-0">
                    <input class="form-check-input ms-auto" type="checkbox" name="upload_to_google_drive" id="upload_to_google_drive" {{ old('upload_to_google_drive') ? 'checked' : '' }}>
                </div>
                @error('upload_to_google_drive')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="input-group">
                <div class="col-md-3">
                    <label for="google_store_for_days">How many days the video should be stored in google drive?</label>
                    <input class="form-control" type="number" min="1" max="30" name="google_store_for_days" id="google_store_for_days" value="{{ old('google_store_for_days', 14) }}">
                    @error('google_store_for_days')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="input-group">
                <input class="btn btn-outline-primary btn-sm mb-0 me-3" type="submit" value="Submit">
            </div>
        </form>
    </div>
@endsection
