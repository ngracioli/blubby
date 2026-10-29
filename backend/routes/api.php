<?php

use App\Http\Controllers\Api\Auth\RegistrationController;
use App\Http\Controllers\Api\Auth\SessionController;
use App\Http\Middleware\Auth\ValidateAuthenticationPayload;
use App\Http\Resources\Api\Auth\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return (new UserResource($request->user()))->response()->header('Cache-Control', 'no-store');
})->middleware(['auth:sanctum', 'abilities:user:read'])->name('auth.user');

Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'abilities:session:revoke'])->name('auth.logout');

Route::middleware(ValidateAuthenticationPayload::class)->group(function (): void {
    Route::post('/register', RegistrationController::class)->middleware('auth.throttle:registration')->name('auth.register');
    Route::post('/login', [SessionController::class, 'store'])->middleware('auth.throttle:login')->name('auth.login');
});
