<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>PortOne V2 Payment</title>
    <script src="https://cdn.portone.io/v2/api.js"></script>
</head>
<body>
    @php
        $paymentType = $order->payment ?? 'card';
        $payMethod = 'CARD';
        $channelKey = $pgParams['channelKey'];

        if ($paymentType === 'virtual') {
            $payMethod = 'VIRTUAL_ACCOUNT';
            $channelKey = $pgParams['channelKeyVbank'];
        } elseif ($paymentType === 'bank') {
            $payMethod = 'TRANSFER';
            $channelKey = $pgParams['channelKeyTransfer'];
        } elseif ($paymentType === 'cellphone') {
            $payMethod = 'MOBILE';
        }
    @endphp
    <script>
        PortOne.requestPayment({
            storeId: '{{ $pgParams["storeId"] }}',
            channelKey: '{{ $channelKey }}',
            paymentId: '{{ $order->order_seq }}',
            orderName: '{{ $pgParams["goods_name"] }}',
            totalAmount: {{ (int) $order->settleprice }},
            currency: 'CURRENCY_KRW',
            payMethod: '{{ $payMethod }}',
            customer: {
                fullName: '{{ $pgParams["customerName"] }}',
                phoneNumber: '{{ $order->order_cellphone ?? "" }}',
                email: '{{ $order->order_email ?? "" }}'
            },
            redirectUrl: '{{ route("payment.portone.success") }}'
        }).then(function (response) {
            if (response.code != null) {
                // Payment Failure or Canceled
                alert(response.message || '결제가 실패했거나 취소되었습니다.');
                window.location.href = '{{ route("cart.index") }}';
            } else {
                // Payment Success (PC environment resolve fallback)
                window.location.href = '{{ route("payment.portone.success") }}?paymentId=' + encodeURIComponent(response.paymentId);
            }
        }).catch(function (error) {
            alert('결제창 호출 중 에러가 발생했습니다: ' + error.message);
            window.location.href = '{{ route("cart.index") }}';
        });
    </script>
</body>
</html>
