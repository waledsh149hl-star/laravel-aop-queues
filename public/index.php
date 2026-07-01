<?php

$pointerFile = __DIR__ . '/../balancer_pointer.txt';
if (!file_exists($pointerFile)) {
    file_put_contents($pointerFile, '0');
}
$currentIndex = (int)file_get_contents($pointerFile);


$currentPort = $_SERVER['SERVER_PORT'];


error_log("--- Load Balancer Traffic --- Request received on Port: " . $currentPort . " | Target Instance Index: " . $currentIndex);


$nextIndex = ($currentIndex + 1) % 2; 
file_put_contents($pointerFile, (string)$nextIndex);

header("X-Balancer-Port: " . $currentPort);
header("X-Balancer-Target-Index: " . $currentIndex);





//----------------------------------------------------------------
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
