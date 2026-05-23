<?php
use App\Http\Controllers\OrderController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RequestLoggerAspect;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




Route::middleware([RequestLoggerAspect::class])->group(function () {
    Route::post('/buy/{id}', [OrderController::class, 'buy']);
    Route::post('/buy1/{id}', [OrderController::class, 'buy1']);

    
Route::post('/generate-report', function () {
    return response()->json([
        'message' => 'تم البدء في توليد التقرير الشامل ومعالجة البيانات ضخمة في الخلفية.'
    ]);
});
});
