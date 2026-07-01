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
























//---------------------------------------------------------------------



// // كود محاكاة توزيع الأحمال (Load Balancing - Round Robin) للطلب الخامس
// $pointerFile = __DIR__ . '/../balancer_pointer.txt';
// if (!file_exists($pointerFile)) {
//     file_put_contents($pointerFile, '0');
// }
// $currentIndex = (int)file_get_contents($pointerFile);

// $currentPort = $_SERVER['SERVER_PORT'];

// error_log("--- Load Balancer Traffic --- Request received on Port: " . $currentPort . " | Target Instance Index: " . $currentIndex);

// // 🔥 السطرين الجديدين لإثبات التناوب داخل الـ Postman:
// header("X-Balancer-Port: " . $currentPort);
// header("X-Balancer-Target-Index: " . $currentIndex);

// $nextIndex = ($currentIndex + 1) % 2; 
// file_put_contents($pointerFile, (string)$nextIndex);





//-----------------------------------------------------------

// // كود محاكاة توزيع الأحمال المطور للـ Postman والمتصفح معاً
// $pointerFile = __DIR__ . '/balancer_pointer.txt'; // حفظ الملف في مجلد public مباشرة لضمان الصلاحيات
// if (!file_exists($pointerFile)) {
//     file_put_contents($pointerFile, '0');
// }

// // قراءة المؤشر الحالي
// $currentIndex = (int)trim(file_get_contents($pointerFile));

// $currentPort = $_SERVER['SERVER_PORT'];

// // تمرير القيم في الـ Headers لإثباتها بالـ Postman
// header("X-Balancer-Port: " . $currentPort);
// header("X-Balancer-Target-Index: " . $currentIndex);

// // حساب المؤشر القادم بالتناوب الدائري (0 ثم 1)
// $nextIndex = ($currentIndex === 0) ? 1 : 0;

// // قفل الملف أثناء الكتابة لمنع التداخل والتأكد من الحفظ الفوري
// file_put_contents($pointerFile, (string)$nextIndex, LOCK_EX);

// error_log("--- Load Balancer Traffic --- Port: " . $currentPort . " | Next Index: " . $nextIndex);



//---------------------------------------------




// // كود موازن الأحمال النهائي والمستقر للمتصفح والـ Postman معاً
// $pointerFile = __DIR__ . '/balancer_pointer.txt';
// if (!file_exists($pointerFile)) {
//     file_put_contents($pointerFile, '0');
// }

// // قراءة القيمة الحالية بشكل صارم ومسح أي مسافات فارغة
// $currentIndex = (int)trim(file_get_contents($pointerFile));
// $currentPort = $_SERVER['SERVER_PORT'];

// // حساب المؤشر القادم بالتناوب الدائري
// $nextIndex = ($currentIndex === 0) ? 1 : 0;

// // حفظ القيمة فوراً بقفل حصري لمنع تداخل المتصفح
// file_put_contents($pointerFile, (string)$nextIndex, LOCK_EX);

// // تمرير القيم في الـ Headers للـ Postman ومنع الكاش في المتصفح
// header("Cache-Control: no-cache, no-store, must-revalidate");
// header("Pragma: no-cache");
// header("Expires: 0");
// header("X-Balancer-Port: " . $currentPort);
// header("X-Balancer-Target-Index: " . $currentIndex);


// ----------------------------------------------------------------



// // كود موازن الأحمال المطور لإغلاق الاتصالات المتتالية وإجبار الملف على التغير
// $pointerFile = __DIR__ . '/balancer_pointer.txt';
// if (!file_exists($pointerFile)) {
//     file_put_contents($pointerFile, '0');
// }

// // قراءة القيمة الحالية بدقة
// $currentIndex = (int)trim(file_get_contents($pointerFile));
// $currentPort = $_SERVER['SERVER_PORT'];

// // حساب المؤشر القادم
// $nextIndex = ($currentIndex === 0) ? 1 : 0;

// // كتابة القيمة فوراً مع قفل حصري وإجبار النظام على الحفظ على القرص الحقيقي
// file_put_contents($pointerFile, (string)$nextIndex, LOCK_EX);

// // الرؤوس البرمجية لمنع الكاش وإغلاق الاتصال فوراً لإثبات التناوب
// header("Cache-Control: no-cache, no-store, must-revalidate");
// header("Pragma: no-cache");
// header("Expires: 0");
// header("Connection: close"); // 🔥 إجبار المتصفح على فتح اتصال جديد بالكامل في كل نقرة
// header("X-Balancer-Port: " . $currentPort);
// header("X-Balancer-Target-Index: " . $currentIndex);

//--------------------------------------------------




















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
