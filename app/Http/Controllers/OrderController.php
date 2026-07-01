<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
class OrderController extends Controller
{

//  الطلب الأول
public function buy($id)
{
    $product = \App\Models\Product::find($id);
    if ($product && $product->stock > 0) {
        for ($i = 0; $i < 30; $i++) {
            \App\Models\Product::orderByRaw('RAND()')->take(20)->get();}
        $currentStock = $product->stock;
        $product->stock = $currentStock - 1;
        $product->save();
        return response()->json(['message' => 'Purchased']);
    }
    return response()->json(['error' => 'Out of stock'],400);
}

public function buy1($id)
{
    $response = DB::transaction(function () use ($id) {
    $product = Product::where('id', $id)
            ->lockForUpdate() 
            ->first();
    if (!$product) {
        return response()->json(['error' => 'Product not found'], 404);
    }
    if ($product->stock > 0) {
        $product->stock -= 1;
        $product->save();
        return response()->json(['message' => 'Purchased']);
    }
    
    return response()->json(['error' => 'Out of stock'], 400);
    });
    return $response;
}

// انتهى الطلب الأول

//          الطلب الثامن 

public function buyWithoutTransaction(Request $request, $id)
{
    try {
        $product = Product::find($id);
        if (!$product || $product->stock <= 0) {
            return response()->json(['message' => 'المنتج غير متوفر بالمخزون'], 400);
        }
        $product->stock -= 1;
        $product->save();
        throw new \Exception('فشل الاتصال بسيرفر الدفع الخارجي!');
        return response()->json(['message' => 'تمت عملية الشراء بنجاح']);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'note' => 'المخزون تم تعديله في قاعدة البيانات '
        ], 500);
    }
}


public function buyWithTransaction(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $product = Product::find($id);
        if (!$product || $product->stock <= 0) {
            return response()->json(['message' => 'المنتج غير متوفر بالمخزون'], 400);
        }
        $product->stock -= 1;
        $product->save();
        throw new \Exception('فشل الاتصال بسيرفر الدفع الخارجي!');
        DB::commit();
        return response()->json(['message' => 'تمت العملية بنجاح وتثبيت البيانات']);
    } catch (\Exception $e) {
        // التراجع السحري لحماية البيانات
        DB::rollBack();
        return response()->json([
            'status' => 'rollback',
            'message' => $e->getMessage(),
            'note' => 'تم إلغاء التعديلات بنجاح '
        ], 500);
    }
}

//        انتهى  الطلب الثامن  



public function getProductWithoutCache(Request $request, $id)
{
    $startTime = microtime(true);
    DB::enableQueryLog();
    for ($i = 0; $i < 50; $i++) {
        Product::orderByRaw('RAND()')->take(100)->get();
    }
    $product = Product::find($id);
    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }
    $executedQueries = DB::getQueryLog();
    $queriesCount = count($executedQueries);
    $executionTime = round((microtime(true) - $startTime) * 1000, 2); 
    return response()->json([
        'status' => 'Success',
        'real_source' => 'قاعدة البيانات مباشرة',
        'queries_count' => $queriesCount, 
        'execution_time_ms' => $executionTime . ' ms', 
        'product' => $product
    ]);
}


public function getProductWithCache($id)
{
    $startTime = microtime(true);
    $cacheKey = "product_detail_" . $id;
    $product = Cache::remember($cacheKey, 60, function () use ($id) {
        DB::enableQueryLog(); // بدأ العداد هنا داخل الـ Miss فقط!
        for ($i = 0; $i < 50; $i++) {
            Product::orderByRaw('RAND()')->take(100)->get();
        }
        return Product::find($id);
    });
    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }
    $executedQueries = DB::getQueryLog();
    $queriesCount = count($executedQueries);
    $executionTime = round((microtime(true) - $startTime) * 1000, 2);
    return response()->json([
        'status' => 'Success',
        'queries_count' => $queriesCount, 
        'execution_time_ms' => $executionTime . ' ms',
        'real_source' => $queriesCount > 0 ? 'قاعدة البيانات (أول طلب - Cache Miss)' : 'الذاكرة المؤقتة (Cache Hit)',
        'product' => $product
    ]);
}


//   -----------------------------------------------------


public function updateStock(Request $request, $id)
{
    $request->validate([
        'stock' => 'required|integer|min:0'
    ]);
    $product = Product::find($id);
    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }
    $product->stock = $request->input('stock');
    $product->save();
    $cacheKey = "product_detail_" . $id;
    Cache::forget($cacheKey);
    return response()->json([
        'status' => 'Success',
        'message' => 'Product stock updated successfully and cache cleared!',
        'product' => $product
    ]);
}


//         انتهى الطلب السادس 

public function getProductWithout($id)
{
    
    $startTime = microtime(true);
    DB::enableQueryLog();
    for ($i = 0; $i < 50; $i++) {
        $products = Product::orderByRaw('RAND()')->take(100)->get();
    }
    $product = Product::find($id);
    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }
    $executedQueries = DB::getQueryLog();
    $queriesCount = count($executedQueries);
    $executionTimeMs = (microtime(true) - $startTime) * 1000 ;
    return response()->json([
        'status' => 'Success',
        'execution_time' => round($executionTimeMs, 2) . ' ms', 
        'database_queries_executed' => $queriesCount, 
        'real_source' => 'قاعدة البيانات ',
        'product' => $product
    ]);
}


public function getProductWith($id)
{
    
    $startTime = microtime(true);
    $cacheKey = "product_detail_" . $id;
    DB::enableQueryLog();
    $product = Cache::remember($cacheKey, 15, function () use ($id) {
        for ($i = 0; $i < 50; $i++) {
            Product::orderByRaw('RAND()')->take(100)->get();
        }
        return Product::find($id);
    });
    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }
    $executedQueries = DB::getQueryLog();
    $queriesCount = count($executedQueries);
    $executionTimeMs = (microtime(true) - $startTime) * 1000;
    return response()->json([
        'status' => 'Success',
        'execution_time' => round($executionTimeMs, 2) . ' ms', 
        'database_queries_executed' => $queriesCount, 
        'real_source' => $queriesCount > 0 ? 'قاعدة البيانات (أول طلب - Cache Miss)' : 'الذاكرة المؤقتة (Cache / Memory Hit)',
        'product' => $product
    ]);
}



}










