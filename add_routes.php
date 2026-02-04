<?php
$file = 'c:/dometopia/new_admin/routes/web.php';
$content = file_get_contents($file);

$routes = <<<EOT

// Front Payment Routes
Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/request', [App\Http\Controllers\Front\PaymentController::class, 'request'])->name('request');
    Route::any('/success', [App\Http\Controllers\Front\PaymentController::class, 'success'])->name('success');
    Route::any('/fail', [App\Http\Controllers\Front\PaymentController::class, 'fail'])->name('fail');
    Route::any('/pairing/receive', [App\Http\Controllers\Front\PaymentController::class, 'pairingReceive'])->name('pairing.receive');
});
EOT;

// Insert after admin group end "});" around line 262
// Look for "});" followed by "/*" or similar context
$pattern = '/\}\);\s*\/\*\s*\/\/ Legacy Debug Routes/';
if (preg_match($pattern, $content)) {
    $content = preg_replace($pattern, "});\n" . $routes . "\n\n/*\n// Legacy Debug Routes", $content);
    file_put_contents($file, $content);
    echo "Routes added successfully.";
} else {
    // Fallback: Just look for the closing of admin group.
    // The admin group ends with "});" at line 262
    // And is followed by empty lines and /*
    // Let's just find the last occurrence of "});" before the end of file? No that's risky.
    // Let's find "Route::get('statistic_visitor', ... ->name('statistic_visitor.index');\n});"
    
    $anchor = "Route::get('statistic_visitor', [App\Http\Controllers\Admin\StatisticSummaryController::class, 'visitor'])->name('statistic_visitor.index');\n});";
    if (strpos($content, $anchor) !== false) {
        $content = str_replace($anchor, $anchor . "\n" . $routes, $content);
        file_put_contents($file, $content);
        echo "Routes added successfully via anchor.";
    } else {
        echo "Could not find insertion point.";
    }
}
?>
