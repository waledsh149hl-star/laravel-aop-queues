
<?php


$servers = [
    'http://127.0.0.1:8000',
    'http://127.0.0.1:8001',
    'http://127.0.0.1:8002'
];

$baseDir = __DIR__ . DIRECTORY_SEPARATOR;
$targetIndex = 0; 
if (is_dir($baseDir . 'ptr_1')) {
    $targetIndex = 1;
    @rmdir($baseDir . 'ptr_1');
    @mkdir($baseDir . 'ptr_2');
} elseif (is_dir($baseDir . 'ptr_2')) {
    $targetIndex = 2;
    @rmdir($baseDir . 'ptr_2');
    @mkdir($baseDir . 'ptr_0');
} else {
    $targetIndex = 0;
    @rmdir($baseDir . 'ptr_0');
    @mkdir($baseDir . 'ptr_1');
}
$selectedServer = $servers[$targetIndex];
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$targetUrl = $selectedServer . $requestUri;
error_log("Load Balancer Forwarding: {$requestUri} -> {$selectedServer}");

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $targetUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_CUSTOMREQUEST => $_SERVER['REQUEST_METHOD'] ?? 'GET',
    CURLOPT_TIMEOUT => 60,
    CURLOPT_CONNECTTIMEOUT => 5,
]);

$input = file_get_contents('php://input');
if ($input !== false && $input !== '') {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
}
$headers = [];
foreach (getallheaders() as $key => $value) {
    $lowerKey = strtolower($key);
    if (in_array($lowerKey, ['host', 'content-length', 'transfer-encoding', 'connection'], true)) {
        continue;
    }
    $headers[] = "$key: $value";
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    http_response_code(502);
    echo json_encode(['error' => 'Backend server offline', 'details' => $error]);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

$responseHeaders = substr($response, 0, $headerSize);
$responseBody = substr($response, $headerSize);


http_response_code($httpCode ?: 200);

foreach (explode("\r\n", $responseHeaders) as $headerLine) {
    if ($headerLine === '' || str_starts_with($headerLine, 'HTTP/')) {
        continue;
    }
    $headerName = strtolower(trim(strtok($headerLine, ':')));
    if (in_array($headerName, ['transfer-encoding', 'content-length', 'connection', 'host'], true)) {
        continue;
    }
    header($headerLine, true);
}


header('X-Balancer-Port: ' . parse_url($selectedServer, PHP_URL_PORT));
header('X-Balancer-Target-Index: ' . $targetIndex);
header('X-Balancer-Algorithm: round-robin-strict');
header('Connection: close'); 


echo $responseBody;