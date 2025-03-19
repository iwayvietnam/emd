<?php declare(strict_types=1);

use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::controller(TrackingController::class)
    ->prefix("tracking")
    ->group(static function () {
        Route::get("/open/{idHash}.gif", "openImage")->name("tracking_open");
        Route::get("/click/{idHash}", "clickUrl")->name("tracking_click");
    });

Route::get("/", static fn () => redirect(config("emd.panel_path")));
