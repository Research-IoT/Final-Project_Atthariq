<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\WelcomeController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\ConsumenController;
use App\Http\Controllers\API\DeviceController;


Route::get('/', [WelcomeController::class, 'index']);

Route::prefix('210cf7aa5e2682c9c9d4511f88fe2789')->group(function () {
    Route::post('/register', [AdminController::class, 'register']);
    Route::post('/login', [AdminController::class, 'login']);

    Route::middleware('auth.admin')->group(function () {
        Route::get('/profile', [AdminController::class, 'profile']);
        Route::delete('/logout', [AdminController::class, 'logout']);

        Route::prefix('device')->group(function () {
            Route::post('/register', [AdminController::class, 'deviceRegister']);
            Route::get('/all', [AdminController::class, 'deviceAll']);
            Route::delete('/remove', [AdminController::class, 'deviceRemove']);
        });

        Route::prefix('control')->group(function () {
            Route::put('/update', [AdminController::class, 'controlUpdate']);
        });
    });
});

Route::prefix('6c63041c1e5899003eec1e9b83802740')->group(function () {
    Route::post('/register', [ConsumenController::class, 'register']);
    Route::post('/login', [ConsumenController::class, 'login']);

    Route::middleware('auth.consumen')->group(function () {
        Route::get('/profile', [ConsumenController::class, 'profile']);
        Route::delete('/logout', [ConsumenController::class, 'logout']);

        Route::prefix('device')->group(function () {
            Route::get('/list', [ConsumenController::class, 'deviceList']);
            Route::post('/add-serial', [ConsumenController::class, 'deviceAddBySerial']);
            Route::post('/add-token', [ConsumenController::class, 'deviceAddByToken']);
            Route::delete('/remove', [ConsumenController::class, 'deviceRemove']);

            Route::prefix('data')->group(function () {});
        });

        Route::prefix('control')->group(function () {
            Route::get('/info', [ConsumenController::class, 'controlInfo']);
            Route::put('/change', [ConsumenController::class, 'controlChange']);
        });
    });
});

Route::prefix('e0212e54ec3a2a120ca0d321b3a60c78')->group(function () {
    Route::middleware('auth.device')->group(function () {
        Route::get('/info', [DeviceController::class, 'info']);
        Route::post('/send', [DeviceController::class, 'sendData']);
    });
});

Route::prefix('8d777f385d3dfec8815d20f7496026dc')->group(function () {
    Route::prefix('admin')->middleware(['auth.admin'])->group(function () {
        Route::get('/all', [DeviceController::class, 'allData']);
        Route::get('/latest', [DeviceController::class, 'latestData']);
        Route::get('/day', [DeviceController::class, 'dataDay']);
        Route::get('/week', [DeviceController::class, 'dataWeek']);
        Route::get('/month', [DeviceController::class, 'dataMonth']);
        Route::get('/year', [DeviceController::class, 'dataYear']);
    });

    Route::prefix('consumen')->middleware(['auth.consumen'])->group(function () {
        Route::get('/all', [DeviceController::class, 'allData']);
        Route::get('/latest', [DeviceController::class, 'latestData']);
        Route::get('/day', [DeviceController::class, 'dataDay']);
        Route::get('/week', [DeviceController::class, 'dataWeek']);
        Route::get('/month', [DeviceController::class, 'dataMonth']);
        Route::get('/year', [DeviceController::class, 'dataYear']);
    });

    Route::prefix('device')->middleware(['auth.device'])->group(function () {
        Route::get('/all', [DeviceController::class, 'allData']);
        Route::get('/latest', [DeviceController::class, 'latestData']);
        Route::get('/day', [DeviceController::class, 'dataDay']);
        Route::get('/week', [DeviceController::class, 'dataWeek']);
        Route::get('/month', [DeviceController::class, 'dataMonth']);
        Route::get('/year', [DeviceController::class, 'dataYear']);
    });
});
