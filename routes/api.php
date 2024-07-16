<?php

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');




use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\UserController;

// Routes for login and other public endpoints
Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [UserController::class, 'logout']);

    Route::get('/complaint/get', [ComplaintController::class, 'complaint/get']); //get all complaint by customer by customer id
    Route::get('/complaint/{complaint_id}', [ComplaintController::class, 'complaint_by_id']);
    Route::get('/complaint/officer/receive', [ComplaintController::class, 'complaint/officer/receive']);
    Route::get('/complaint/customer/ongoing', [ComplaintController::class, 'complaint/customer/ongoing']); //by customer
    Route::post('/submit-complaint', [ComplaintController::class, 'submit-complaint']); //submit by customer
    Route::post('/update-complaint/{id}', [ComplaintController::class, 'update-complaint']); //edit by petugas
});



// Route::group(['middleware' => 'auth:sanctum'], function(){
//     Route::get('/complaint/get', [ComplaintController::class, 'complaint/get']); //get all complaint by customer by customer id
//     Route::get('/complaint/{complaint_id}', [ComplaintController::class, 'complaint_by_id']);
//     Route::get('/complaint/officer/receive', [ComplaintController::class, 'complaint/officer/receive']);
//     Route::get('/complaint/customer/ongoing', [ComplaintController::class, 'complaint/customer/ongoing']); //by customer
//     Route::post('/submit-complaint', [ComplaintController::class, 'submit-complaint']); //submit by customer
//     Route::post('/update-complaint/{id}', [ComplaintController::class, 'update-complaint']); //edit by petugas
//     // Route::delete('/complaint_destroy', [ComplaintController::class, 'complaint_destroy']);
// }); 