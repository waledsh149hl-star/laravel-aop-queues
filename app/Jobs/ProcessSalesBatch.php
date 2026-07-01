<?php
namespace App\Jobs;

use Illuminate\Bus\Batchable; // هذه الإضافة من المحاضرة 4
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSalesBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function handle(): void
    {
        
        if ($this->batch()->cancelled()) {
            return;
        }
            sleep(1);
        // محاكاة معالجة البيانات (توليد  ٍ التقرير)
        foreach ($this->products as $product) {
            // عمليات حسابية نثلا 
        }
        Log::info("Batch Worker: تمت معالجة دفعة تضم " . count($this->products) . " منتج بنجاح.");
    }
}