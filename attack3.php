<?php



$productId = 3; 
$totalRequests = 20; 
$mh = curl_multi_init();
$curl_handles = [];


echo " يزاوتم بلط 20 لاسرإ ءدب\n\n";

for ($i = 1; $i <= $totalRequests; $i++) {
    
    $port = ($i % 2 === 0) ? 8001 : 8000;
    $url = "http://127.0.0.1:{$port}/api/buy/{$productId}";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query([]),
        CURLOPT_TIMEOUT => 20,
    ]);
    curl_multi_add_handle($mh, $ch);
    $curl_handles[$i] = ['handle' => $ch, 'port' => $port];
}
$running = null;
do {
    curl_multi_exec($mh, $running);
    if ($running) {
        curl_multi_select($mh, 1.0); 
    }
} while ($running > 0);

echo "\n📊 جئاتنلا   :\n";
echo str_repeat("-", 90) . "\n";

foreach ($curl_handles as $index => $item) {
    $ch = $item['handle'];
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);       
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);     
    $responseTimeMs = round($totalTime * 1000, 2);          
    
    $response = curl_multi_getcontent($ch);
    
    $status = ($httpCode >= 200 && $httpCode < 300) ? "🟢 SUCCESS" : "🔴 FAILED";

    echo "Req #{$index} | Port: {$item['port']} | HTTP: {$httpCode} | Time: {$responseTimeMs} ms | Status: {$status} -> {$response}\n";
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}

curl_multi_close($mh);

echo str_repeat("-", 90) . "\n";
echo "✅ -----------------------------------------------------------------\n";
?>