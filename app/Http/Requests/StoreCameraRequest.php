<?php

namespace App\Http\Requests;

use App\Rules\CameraIsCurrentlyStreaming;
use Illuminate\Foundation\Http\FormRequest;

class StoreCameraRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:cameras'],
            'url' => ['required', 'string', 'max:255', 'unique:cameras', new CameraIsCurrentlyStreaming()],
            'upload_to_google_drive' => ['nullable'],
            'store_in_localstorage' => ['nullable'],
            'google_store_for_days' => ['required_if:upload_to_google_drive,true', 'int', 'min:1', 'max:30'],
            'localstorage_store_for_days' => ['required_if:store_in_localstorage,true', 'int', 'min:1', 'max:90'],
        ];
    }
}
