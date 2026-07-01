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




Route::post('/buyWithoutTransaction/{id}', [OrderController::class, 'buyWithoutTransaction']);
Route::post('/buyWithTransaction/{id}', [OrderController::class, 'buyWithTransaction']);



//---------------------------------------------------------------------
// الطلب 6 
Route::get('/getProductWithoutCache/{id}', [OrderController::class, 'getProductWithoutCache']);
Route::get('/getProductWithCache/{id}', [OrderController::class, 'getProductWithCache']);
Route::put('/products/{id}/stock', [OrderController::class, 'updateStock']);



Route::get('/getProductWithout/{id}', [OrderController::class, 'getProductWithout']);

Route::get('/getProductWith/{id}', [OrderController::class, 'getProductWith']);
