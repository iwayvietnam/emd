<?php declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::middleware('auth:api')->controller(
    EmailController::class
)->group(function () {
    Route::get('/email', 'index');
    Route::get('/email/{id}', 'show');
    Route::get('/email/{id}/devices', 'devices');
});

Route::middleware(['auth:api', 'scope:send-emails'])->post(
    '/send',
    [EmailController::class, 'send']
);

Route::middleware(['auth:api', 'scope:upload-files'])->post(
    '/upload',
    [UploadController::class, 'upload']
);
