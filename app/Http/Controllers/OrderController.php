<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Models\Product;

class OrderController extends Controller
{


public function buy($id)
{
    $product = \App\Models\Product::find($id);

    if ($product && $product->stock > 0) {

        $currentStock = $product->stock;

        //sleep(2); 

        $product->stock = $currentStock - 1;
        $product->save();

        return response()->json(['message' => 'Purchased']);
    }

    return response()->json(['error' => 'Out of stock']);
}









// public function buy1($id)
// {
//     try {
//         DB::transaction(function () use ($id) {

//             $product = Product::where('id', $id)
//                 ->lockForUpdate()
//                 ->first();

//             if (!$product) {
//                 throw new \Exception("Product not found");
//             }

//             if ($product->stock > 0) {
//                 $product->stock -= 1;
//                 $product->save();
//             } else {
//                 throw new \Exception("Out of stock");
//             }

//         });

//         return response()->json([
//             'message' => 'Purchased'
//         ]);

//     } catch (\Exception $e) {
//         return response()->json([
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }

public function buy1($id)
{
    
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
    
}


}
