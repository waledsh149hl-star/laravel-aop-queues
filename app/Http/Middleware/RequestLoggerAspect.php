<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendInvoiceJob; 

class RequestLoggerAspect
{
    public function handle($request, Closure $next)
{
    DB::beginTransaction();
    try {
        $response = $next($request);

        if ($response->getStatusCode() == 200) {
            DB::commit();

            
            // if ($request->is('api/buy*')) {
            //     \App\Jobs\SendInvoiceJob::dispatch();
            //     Log::info("Aspect: تم إرسال فاتورة فردية للخلفية.");
            // }

//      الطلب الثالث المعالجة غير المتزامنة 
if ($request->is('api/buy*')) {
    if ($request->query('mode') == 'sync') {
        
        \App\Jobs\SendInvoiceJob::dispatchSync(); 
        Log::info("Aspect: تم تنفيذ الفاتورة بشكل متزامن (Sync).");
    } else {
        
        \App\Jobs\SendInvoiceJob::dispatch();
        Log::info("Aspect: تم إرسال فاتورة فردية للخلفية (Async).");
    }
}
//   انتهى الطلب الرابع 



            // الطلب الرابع: معالجة دفعات متوازية 
            if ($request->is('api/generate-report')) {
                $this->handleBatchProcessing();
            }
        }
        return $response;
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

//           الطلب الرابع 
protected function handleBatchProcessing()
{
    $products = \App\Models\Product::all();
    $jobs = [];
    // تقسيم البيانات إلى  (Chunks) 
    foreach ($products->chunk(5) as $chunk) {
        $jobs[] = new \App\Jobs\ProcessSalesBatch($chunk);
    }
    // (Callbacks)
    \Illuminate\Support\Facades\Bus::batch($jobs)
        ->then(function ($batch) { // Job Chaining
            Log::info("Success: تم الانتهاء من معالجة كافة الدفعات بنجاح!");
        })
        ->catch(function ($batch, $e) {
            Log::error("Error: فشلت إحدى الدفعات في المعالجة المتوازية.");
        })
        ->dispatch();
    Log::info("Aspect: تم تقسيم العمل وتوزيعه كـ Parallel Batch.");
}
}