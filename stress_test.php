<?php

// php stress_test.php http://127.0.0.1:8080/api/getProductWith/1 100 GET
// php stress_test.php http://127.0.0.1:8080/api/buy1/4 100 POST
$url = $argv[1] ?? 'http://127.0.0.1:8080/api/getProductWithCache/1';
//$url = $argv[1] ?? 'http://127.0.0.1:8080/api/getProductWith/1';
$totalRequests = isset($argv[2]) ? max(1, (int) $argv[2]) : 100;
$method = strtoupper($argv[3] ?? 'GET');
$mh = curl_multi_init();
$handles = [];
$startedAt = microtime(true);
echo " Starting concurrent stress test\n";
echo "URL: {$url}\n";
echo "Method: {$method}\n";
echo "Total Requests: {$totalRequests}\n\n";
for ($i = 1; $i <= $totalRequests; $i++) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_CUSTOMREQUEST => $method,
    ]);
    if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([]));
    }
    curl_multi_add_handle($mh, $ch);
    $handles[$i] = $ch;
}
$running = null;
do {
    $status = curl_multi_exec($mh, $running);
    if ($running) {
        curl_multi_select($mh, 1.0);
    }
} while ($running > 0 && $status === CURLM_OK);
$totalTime = microtime(true) - $startedAt;
$success = 0;
$failed = 0;
$crashed = false;
$responseTimes = [];
$statusCodes = [];
$sampleFailures = [];

foreach ($handles as $index => $ch) {
    $response = curl_multi_getcontent($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);

    $httpCode = (int) ($info['http_code'] ?? 0);
    $time = (float) ($info['total_time'] ?? 0);
    $responseTimes[] = $time;
    $statusCodes[$httpCode] = ($statusCodes[$httpCode] ?? 0) + 1;

    if ($httpCode >= 200 && $httpCode < 400 && $error === '') {
        $success++;
    } else {
        $failed++;
        if ($httpCode === 0 || $httpCode >= 500) {
            $crashed = true;
        }

        if (count($sampleFailures) < 5) {
            $body = substr($response, -250);
            $sampleFailures[] = "Req #{$index} | HTTP {$httpCode} | Error: " . ($error ?: 'none') . " | Body: " . str_replace(["\r", "\n"], ' ', $body);
        }
    }

    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}

curl_multi_close($mh);

$avgMs = count($responseTimes) ? (array_sum($responseTimes) / count($responseTimes)) * 1000 : 0;
$minMs = count($responseTimes) ? min($responseTimes) * 1000 : 0;
$maxMs = count($responseTimes) ? max($responseTimes) * 1000 : 0;
$throughput = $totalTime > 0 ? $totalRequests / $totalTime : 0;

ksort($statusCodes);

echo "=============================================\n";
echo " Stress Test Results\n";
echo "=============================================\n";
echo "Total Requests: {$totalRequests}\n";
echo "Success Requests: {$success}\n";
echo "Failed Requests: {$failed}\n";
echo "Average Response Time: " . round($avgMs, 2) . " ms\n";
echo "Min Response Time: " . round($minMs, 2) . " ms\n";
echo "Max Response Time: " . round($maxMs, 2) . " ms\n";
echo "Total Stress Time: " . round($totalTime, 2) . " seconds\n";
echo "Throughput: " . round($throughput, 2) . " requests/sec\n";
echo "HTTP Codes: " . json_encode($statusCodes, JSON_UNESCAPED_UNICODE) . "\n";

echo "System Crashed: " . ($crashed ? 'YES' : 'NO') . "\n";

if (!empty($sampleFailures)) {
    echo "\nSample Failures:\n";
    foreach ($sampleFailures as $failure) {
        echo "- {$failure}\n";
    }
}

echo "=============================================\n";










