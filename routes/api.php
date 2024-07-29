<?php


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\UserController;

use App\Http\Controllers\NotificationController;

// Routes for login and other public endpoints
Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/update-fcm-token', [UserController::class, 'updateFcmToken']);

Route::post('/send-notification', [NotificationController::class, 'send']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [UserController::class, 'logout']);

    Route::get('/complaint/get', [ComplaintController::class, 'complaint_index']); //get all complaint by customer by customer id
    Route::post('/submit-complaint', [ComplaintController::class, 'complaint_submit']); //submit by customer
    Route::get('/complaint/customer/ongoing', [ComplaintController::class, 'get_customer_complaint']); //by customer
    Route::post('/update-complaint/{id}', [ComplaintController::class, 'complaint_edit']); //edit by petugas
    Route::get('/complaint/{id}', [ComplaintController::class, 'get_complaint_by_id']);
    Route::post('/accept-complaint/{id}', [ComplaintController::class, 'accept_complaint_by_officer']);
    
    Route::get('/complaint/officer/receive', [ComplaintController::class, 'complaint_officer_index_by_received']);

    
    
    
});

Route::post('/sendPushNotification', [ComplaintController::class, 'sendPushNotification']);