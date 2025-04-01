<?php declare(strict_types=1);

use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get(
    "/user",
    static fn (Request $request) => $request->user()
)->middleware("auth:api");

Route::middleware("auth:api")->controller(
    EmailController::class
)->group(static function () {
    Route::get("/email", "index");
    Route::get("/email/{id}", "show");
    Route::get("/email/{id}/devices", "devices");
    Route::post("/send", "send");
});

Route::post("/upload", [
    UploadController::class,
    "upload",
])->middleware("auth:api");
