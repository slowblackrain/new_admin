<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Front\MemberController;

// Create a dummy image file for upload testing
$tempFile = tempnam(sys_get_temp_dir(), 'bno');
file_put_contents($tempFile, 'dummy license content');
$uploadedFile = new UploadedFile($tempFile, 'license.jpg', 'image/jpeg', null, true);

$request = Request::create('/member/register_process', 'POST', [
    'type' => 'business',
    'userid' => 'test_b2b_' . time(),
    'password' => 'password123!',
    'username' => 'Tester',
    'email' => 'test_' . time() . '@b2b.com',
    'cellphone' => '010-1234-5678',
    'bname' => 'Test Company',
    'bno' => '123-45-67890',
    'bceo' => 'CEO Kim'
], [], ['bno_file' => $uploadedFile]);

$controller = new MemberController();
try {
    $response = $controller->register_process($request);
    echo "SUCCESS: Registration process completed.\n";
    echo "Response status: " . $response->getStatusCode() . "\n";
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "VALIDATION ERROR:\n";
    print_r($e->errors());
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}

