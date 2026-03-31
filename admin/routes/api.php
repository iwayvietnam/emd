<?php declare(strict_types=1);

use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

Route::domain(config("emd.api.domain"))
    ->middleware("auth:api")
    ->controller(EmailController::class)
    ->group(static function () {
        Route::get("/email", "index");
        Route::get("/email/{id}", "show");
        Route::get("/email/{id}/devices", "devices");
        Route::post("/send", "send");
    });

Route::domain(config("emd.api.domain"))
    ->post("/upload", UploadController::class)
    ->middleware("auth:api");
