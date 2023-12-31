<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Customer\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['auth:api'])->group(function () {
    Route::prefix('user')->group(function () {
        Route::post('/', [AuthController::class, 'user']);
    });
});

Route::middleware(['guest', 'api'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::fallback(function () {
    return response([
        'message' => __('Not Found.'),
    ], Response::HTTP_NOT_FOUND);
});
