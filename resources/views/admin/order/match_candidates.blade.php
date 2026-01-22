<div class="alert alert-info">
    선택한 입금내역: <strong>{{ $sms->in_name }}</strong> ({{ number_format($sms->in_price) }}원) / {{ $sms->in_bank }} / {{ $sms->update_time }}
</div>

@if($candidates->isEmpty())
    <div class="alert alert-warning text-center">
        매칭할 수 있는 주문이 없습니다. (조건: 72시간 이내, 주문접수 단계, 무통장 결제)
    </div>
@else
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>주문번호</th>
                <th>주문자</th>
                <th>주문금액</th>
                <th>상태</th>
                <th>주문일시</th>
                <th>매칭</th>
            </tr>
        </thead>
        <tbody>
            @foreach($candidates as $order)
                <tr>
                    <td>{{ $order->order_seq }}</td>
                    <td>{{ $order->order_user_name }} ({{ $order->depositor }})</td>
                    <td>
                        {{ number_format($order->settleprice) }}원
                        @if($order->settleprice == $sms->in_price)
                            <span class="badge badge-success">금액일치</span>
                        @else
                            <span class="badge badge-danger">불일치</span>
                        @endif
                    </td>
                    <td>{{ $order->step }} (주문접수)</td>
                    <td>{{ $order->regist_date }}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="processMatch({{ $sms->idx }}, {{ $order->order_seq }})">매칭</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
