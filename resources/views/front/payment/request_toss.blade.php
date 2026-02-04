<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>Toss Payment</title>
    <script src="https://js.tosspayments.com/v1/payment"></script>
</head>
<body>
    <script>
        var clientKey = '{{ $pgParams["clientKey"] }}';
        var tossPayments = TossPayments(clientKey);
        
        tossPayments.requestPayment('카드', {
            amount: {{ $order->settleprice }},
            orderId: '{{ $order->order_seq }}',
            orderName: '{{ $pgParams["goods_name"] }}',
            customerName: '{{ $pgParams["customerName"] }}',
            successUrl: '{{ route("payment.success") }}',
            failUrl: '{{ route("payment.fail") }}',
        })
        .catch(function (error) {
            if (error.code === 'USER_CANCEL') {
                // User canceled
                window.location.href = '{{ route("cart.index") }}';
            } else {
                alert(error.message);
                window.location.href = '{{ route("cart.index") }}';
            }
        });
    </script>
</body>
</html>
