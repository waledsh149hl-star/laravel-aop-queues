<?php



$productId = isset($argv[1]) ? (int) $argv[1] : 1;
$iterations = isset($argv[2]) ? max(1, (int) $argv[2]) : 20;
$base = 'http://127.0.0.1:8080/api';

function request_once(string $url): array
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
    ]);
    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'http_code' => (int) ($info['http_code'] ?? 0),
        'time_ms' => round(((float) ($info['total_time'] ?? 0)) * 1000, 2),
        'error' => $error,
        'body' => $body,
    ];
}

function run_benchmark(string $label, string $url, int $iterations): array
{
    $times = [];
    $success = 0;
    $lastBody = '';

    for ($i = 1; $i <= $iterations; $i++) {
        $result = request_once($url);
        $times[] = $result['time_ms'];
        $lastBody = $result['body'];
        if ($result['http_code'] >= 200 && $result['http_code'] < 400 && $result['error'] === '') {
            $success++;
        }
    }

    return [
        'label' => $label,
        'url' => $url,
        'iterations' => $iterations,
        'success' => $success,
        'failed' => $iterations - $success,
        'avg_ms' => round(array_sum($times) / count($times), 2),
        'min_ms' => round(min($times), 2),
        'max_ms' => round(max($times), 2),
        'last_response' => $lastBody,
    ];
}
$nonCachedProductId = $productId + 1;
echo "📏 Benchmark before/after optimization\n";
echo "Product ID: {$productId}\n";
echo "Iterations: {$iterations}\n\n";

$before = run_benchmark('Before - without cache', "{$base}/getProductWithout/{$nonCachedProductId}", $iterations);


//request_once("{$base}/getProductWith/{$productId}");
$after = run_benchmark('After - cache hits', "{$base}/getProductWith/{$productId}", $iterations);

foreach ([$before, $after] as $result) {
    echo "=============================================\n";
    echo $result['label'] . "\n";
    echo "URL: " . $result['url'] . "\n";
    echo "Success: {$result['success']} | Failed: {$result['failed']}\n";
    echo "Average: {$result['avg_ms']} ms | Min: {$result['min_ms']} ms | Max: {$result['max_ms']} ms\n";
}

$improvement = $before['avg_ms'] > 0 ? (($before['avg_ms'] - $after['avg_ms']) / $before['avg_ms']) * 100 : 0;

echo "=============================================\n";
echo "Improvement: " . round($improvement, 2) . "%\n";
echo "Note: first cached request is a MISS. The after benchmark is measured after warm-up.\n";
echo "=============================================\n";
