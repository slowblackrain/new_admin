<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;

try {
    echo "=== Login Verification ===\n";
    $req = new Illuminate\Http\Request();
    $req->replace(['userid' => 'dometopia', 'password' => '1234']);
    
    // $controller = new App\Http\Controllers\Front\MemberController();
    
    $userid = 'dometopia';
    $password = '1234';
    $str_sha = hash('sha256', $password);
    echo "Target Hash (SHA256 of 1234): " . $str_sha . "\n";
    
    $member = App\Models\Member::where('userid', $userid)
        ->where(function ($query) use ($str_sha) {
            $query->where('password', $str_sha);
        })
        ->first();
    
    if ($member) {
        echo "Login Logic Success: Found member " . $member->userid . "\n";
        Auth::login($member);
        echo "Auth::check() = " . (Auth::check() ? 'True' : 'False') . "\n";
        if (Auth::check()) {
             echo "Auth::user()->userid = " . Auth::user()->userid . "\n";
        }
    } else {
        echo "Login Logic Failed: Member not found with hash.\n";
        // Check if user exists at all
        $userExists = App\Models\Member::where('userid', $userid)->first();
        if ($userExists) {
             echo "User '$userid' exists but password hash mismatch.\n";
             echo "Stored Password: " . $userExists->password . "\n";
        } else {
             echo "User '$userid' does not exist.\n";
        }
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}