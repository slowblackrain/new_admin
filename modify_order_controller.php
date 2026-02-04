<?php
$file = 'c:/dometopia/new_admin/app/Http/Controllers/Front/OrderController.php';
$content = file_get_contents($file);

// 1. Change Order Status Logic
$searchStatus = <<<'EOT'
            } else {
                $order->step = \App\Models\Order::STEP_PAYMENT_CONFIRMED;
                $order->deposit_yn = 'y'; 
                $order->bundle_yn = 'n';
            }
EOT;

$replaceStatus = <<<'EOT'
            } else {
                // PG Payment - Wait for Confirmation
                $order->step = \App\Models\Order::STEP_ORDER_RECEIVED;
                $order->deposit_yn = 'n'; 
                $order->bundle_yn = 'n';
            }
EOT;

$content = str_replace($searchStatus, $replaceStatus, $content);

// 2. Change Redirect Logic
$searchRedirect = "return redirect()->route('order.complete', ['id' => \$order->order_seq]);";

$replaceRedirect = <<<'EOT'
            if ($request->payment == 'bank') {
                return redirect()->route('order.complete', ['id' => $order->order_seq]);
            } else {
                return redirect()->route('payment.request', ['order_seq' => $order->order_seq]);
            }
EOT;

// Only replace strictly if found. Note $Variable matching might be tricky with quotes.
// Let's use looser matching or manual verify.
if (strpos($content, $searchRedirect) !== false) {
    $content = str_replace($searchRedirect, $replaceRedirect, $content);
    file_put_contents($file, $content);
    echo "OrderController modified successfully.";
} else {
    echo "Could not find redirect line.";
    // Debug
    // echo "\nSearching for:\n$searchRedirect\n";
}
?>
