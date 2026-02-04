<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>Pairing Payment</title>
    <script src="https://pairingpayments.com/js/requestpay_utf.js"></script>
</head>
<body>
    <form name="fm" id="fm" method="POST" accept-charset="euc-kr">
        <input type="hidden" name="code" id="code" value="{{ $pgParams['clientId'] }}"/>
        <input type="hidden" name="order_no" id="order_no" value="{{ $order->order_seq }}"/>
        <input type="hidden" name="amount" id="amount" value="{{ $order->settleprice }}"/>
        <input type="hidden" name="product_code" id="product_code" value="{{ $order->items->first()->goods_seq }}"/>
        <input type="hidden" name="product_name" id="product_name" value="{{ $pgParams['goods_name'] }}"/>
        <input type="hidden" name="buyer" id="buyer" value="{{ $order->order_user_name }}"/>
        <!-- Placeholder info -->
        <input type="hidden" name="recp_name" id="recp_name" value="{{ $order->recipient_user_name }}"/>
        <input type="hidden" name="recp_addr" id="recp_addr" value="{{ $order->recipient_address }}"/>
        <input type="hidden" name="returnURL" id="returnURL" value="{{ route('payment.pairing.receive', ['orderno' => $order->order_seq, 'mode' => 'order', 'type' => 'p']) }}" />
    </form>
    <script type="text/javascript">
        // Pairing function
        ftn_approval();
    </script>
</body>
</html>
