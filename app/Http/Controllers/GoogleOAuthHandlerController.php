<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GoogleOAuthHandlerController extends Controller
{
    public function handleCallback(Request $request)
    {
        $incomingData = $request->input();

        return view('google.callback-handler')->with([
            'data' => $incomingData
        ]);
    }
}
