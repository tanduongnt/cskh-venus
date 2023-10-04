<?php

namespace App\Http\Controllers\Api\Customer;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;

class AuthController extends ApiController
{
    public function __construct() {
        Auth::setDefaultDriver('customer');
    }

    public function login(LoginRequest $request)
    {
        $data = $request->only(['email', 'password']);
        if (Auth::attempt($data)) {
            $customer = Auth::user();
            $token = $customer->createToken('API')->accessToken;
            return $this->success(array_merge($customer->toArray(), [
                'token' => $token,
            ]));
        }
        return $this->failed(__('auth.failed'));
    }

    public function user(Request $request)
    {
        $token = $request->bearerToken();
        $user = $request->user();
        return $this->success(array_merge($user->toArray(), [
            'token' => $token,
        ]));
    }
}
