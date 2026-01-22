<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$member = \Illuminate\Support\Facades\DB::table('fm_member')->where('userid', 'newjjang3')->first();
if (!$member) die("Member not found");

$configs = \Illuminate\Support\Facades\DB::table('fm_config')->get();
$tried = [];

foreach ($configs as $conf) {
    $vals = [$conf->value, $conf->codecd];
    
    // Split serials
    if (strpos($conf->value, '||') !== false) {
        $parts = explode('||', $conf->value);
        foreach($parts as $p) $vals[] = $p;
    }

    foreach ($vals as $v) {
        if (!$v) continue;
        
        $candidates = [
            $v, 
            md5($v), 
            substr($v, 0, 16), 
            substr(md5($v), 0, 16),
            strtoupper(md5($v))
        ];

        foreach ($candidates as $k) {
            if (isset($tried[$k])) continue;
            $tried[$k] = true;

            try {
                $res = \Illuminate\Support\Facades\DB::select("SELECT AES_DECRYPT(UNHEX(?), ?) as d", [$member->email, $k]);
                $decrypted = $res[0]->d;
                if ($decrypted && preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $decrypted)) {
                    echo "SUCCESS! Key found: " . $k . " (Source: {$conf->codecd})\n";
                    echo "Decrypted: " . $decrypted . "\n";
                    exit;
                }
            } catch (\Exception $e) {}
        }
    }
}
echo "Failed to find key in fm_config values.\n";
