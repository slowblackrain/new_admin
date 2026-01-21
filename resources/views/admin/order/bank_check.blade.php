@extends('admin.layouts.admin')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">무통장 입금확인</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>주문일시</th>
                                <th>주문번호</th>
                                <th>주문자/입금자</th>
                                <th>은행/계좌</th>
                                <th>입금액</th>
                                <th>상태</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>{{ $order->regist_date }}</td>
                                    <td>{{ $order->order_seq }}</td>
                                    <td>
                                        {{ $order->order_user_name }}<br>
                                        <small>{{ $order->depositor }}</small>
                                    </td>
                                    <td>{{ $order->bank_account }}</td>
                                    <td>{{ number_format($order->settleprice) }}원</td>
                                    <td>
                                        <span class="badge badge-warning">{{ $order->step }} (입금대기)</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-success" 
                                                onclick="updateStatus('{{ $order->order_seq }}', '25')">
                                            입금확인
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">입금 대기 중인 주문이 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $orders->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <script>
    function updateStatus(orderSeq, nextStep) {
        if (!confirm('입금 확인 처리를 진행하시겠습니까?')) return;
        
        fetch('{{ route("admin.order.update_status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                order_seq: orderSeq,
                step: nextStep
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('처리되었습니다.');
                location.reload();
            } else {
                alert('오류가 발생했습니다: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('통신 오류가 발생했습니다.');
        });
    }
    </script>
@endsection
