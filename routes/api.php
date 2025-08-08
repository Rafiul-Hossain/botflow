<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::post('password/email', [AuthController::class, 'sendResetOTP']);
Route::post('password/verify-otp', [AuthController::class, 'verifyResetOTP'])->name('password.verify-otp');
Route::post('password/forget', [AuthController::class, 'sendPasswordResetLink'])->name('password.forget');
Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');


Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');


Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::put('/update-password', [AuthController::class, 'updatePassword']);
});

Route::middleware(['auth:api', 'role:user'])->group(function () {
    Route::get('/user-only', function () {
        return response()->json(['message' => 'Hello User!']);
    });
});

Route::middleware(['auth:api', 'role:admin,user'])->group(function () {
    Route::get('/both-roles', function () {
        return response()->json(['message' => 'Hello Admin or User!']);
    });
});
