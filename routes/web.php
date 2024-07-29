<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PushNotificationController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/send-notification', [PushNotificationController::class, 'sendPushNotification']);
Route::post('/send-notification', [NotificationController::class, 'send']);
