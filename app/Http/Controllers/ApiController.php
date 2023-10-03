<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiController extends Controller
{
    public function success($result = [], $message = null)
    {
        return response([
            'error' => false,
            'message' => $message,
            'result' => $result,
        ], Response::HTTP_OK);
    }

    public function failed($message = null)
    {
        return response([
            'error' => true,
            'message' => $message
        ], Response::HTTP_NOT_FOUND);
    }

    public function unauthorize($message = null)
    {
        return response([
            'error' => true,
            'message' => $message ?? __('auth.token')
        ], Response::HTTP_FORBIDDEN);
    }
}
