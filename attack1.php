<?php
$url = "http://localhost/api/buy1/2";
$mh = curl_multi_init();
$handles = [];
for ($i = 0; $i < 20; $i++) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    $handles[] = $ch;
    curl_multi_add_handle($mh, $ch);
}
$running = null;
do {
    curl_multi_exec($mh, $running);
} while ($running > 0);
echo "تم إرسال  الطلبات \n";