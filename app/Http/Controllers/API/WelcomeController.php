<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\Response;

class WelcomeController extends Controller
{
    /**
     *  Info
     */
    public function index()
    {
        $data = [
            'name' => 'Application ' . env('APP_NAME'),
            'documantion' => env('APP_URL') . '/docs/api'
        ];

        return Response::success($data);
    }
}
