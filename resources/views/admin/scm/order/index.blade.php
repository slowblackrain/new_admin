@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">SCM 발주 목록 (SCM Order List)</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Trader</th>
                            <th>Total EA</th>
                            <th>Total Price (KRW)</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->sorder_seq }}</td>
                            <td>{{ $order->sorder_code }}</td>
                            <td>{{ $order->trader_seq }}</td>
                            <td>{{ number_format($order->total_ea) }}</td>
                            <td>{{ number_format($order->krw_total_price) }}</td>
                            <td>
                                @if($order->sorder_status == 0) <span class="badge badge-warning">Draft</span>
                                @elseif($order->sorder_status == 1) <span class="badge badge-primary">Ordered</span>
                                @elseif($order->sorder_status == 2) <span class="badge badge-success">Warehoused</span>
                                @else <span class="badge badge-secondary">{{ $order->sorder_status }}</span>
                                @endif
                            </td>
                            <td>{{ $order->regist_date }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
