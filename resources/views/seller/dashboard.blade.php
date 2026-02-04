@extends('seller.layouts.app')

@section('title', 'Seller Dashboard')
@section('body_class', 'sidebar-mini')



@section('content')
<div class="container-fluid">
    <!-- Top: Asset Summary (Emoney & Cash) -->
    
    @if(isset($failureAlerts) && count($failureAlerts) > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> 자동발주 실패 알림!</h5>
                <ul class="mb-0">
                    @foreach($failureAlerts as $alert)
                        <li>
                            [{{ substr($alert->regist_date, 5, 11) }}]
                            <strong>{{ $alert->goods_name }}</strong> : {{ $alert->fail_reason }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif
    
    <div class="row mb-3">
        <!-- Asset Summary -->
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-coins"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">보유 예치금 (Emoney)</span>
                    <span class="info-box-number">{{ number_format($assetSummary['emoney']) }} 원</span>
                     <span class="text-xs text-muted">발주 가능 금액</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-wallet"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">보유 캐시 (Cash)</span>
                    <span class="info-box-number">{{ number_format($assetSummary['cash']) }} 원</span>
                </div>
            </div>
        </div>

        <!-- Settlement Summary (New) -->
        <div class="col-12 col-sm-6 col-md-3">
             <div class="info-box mb-3">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">이번 달 예상 마진</span>
                    <span class="info-box-number">{{ number_format($settlementSummary['margin']) }} 원</span>
                    <span class="text-xs text-muted">{{ $settlementSummary['month'] }}월 확정 건 기준</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
             <div class="info-box mb-3">
                <span class="info-box-icon bg-purple elevation-1"><i class="fas fa-won-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">이번 달 정산 매출</span>
                    <span class="info-box-number">{{ number_format($settlementSummary['sales_volume']) }} 원</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Fulfillment & Product Status -->
        <div class="col-md-6">
            <!-- Fulfillment Status -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shipping-fast mr-1"></i>
                        발주/배송 현황 (Fulfillment)
                    </h3>
                </div>
                <div class="card-body p-0">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                입금대기 (Deposit Pending)
                                <span class="float-right badge bg-secondary">{{ number_format($fulfillmentSummary['deposit_pending']) }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link font-weight-bold">
                                결제확인 (Payment Confirmed)
                                <span class="float-right badge bg-danger">{{ number_format($fulfillmentSummary['payment_confirmed']) }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                상품준비중 (Preparing)
                                <span class="float-right badge bg-warning">{{ number_format($fulfillmentSummary['preparing']) }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                배송중 (Shipping)
                                <span class="float-right badge bg-primary">{{ number_format($fulfillmentSummary['shipping']) }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                배송완료 (Completed)
                                <span class="float-right badge bg-success">{{ number_format($fulfillmentSummary['completed']) }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                반품/환불 (Return/Refund)
                                <span class="float-right badge bg-secondary">{{ number_format($fulfillmentSummary['return_refund']) }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Product Status -->
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-boxes mr-1"></i>
                        ATS 상품 현황
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4 border-right">
                             <h5 class="text-success">{{ number_format($productSummary['normal']) }}</h5>
                             <span class="text-muted">판매중</span>
                        </div>
                        <div class="col-4 border-right">
                             <h5 class="text-danger">{{ number_format($productSummary['runout']) }}</h5>
                             <span class="text-muted">품절</span>
                        </div>
                        <div class="col-4">
                             <h5 class="text-secondary">{{ number_format($productSummary['stop']) }}</h5>
                             <span class="text-muted">판매중지</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Chart & Notices -->
        <div class="col-md-6">
             <!-- Weekly Purchase Chart -->
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-1"></i>
                        일별 매입(발주) 추이 (최근 7일)
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="purchaseChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>

            <!-- Notices -->
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bullhorn mr-1"></i>
                        입점사 공지 (Notices)
                    </h3>
                </div>
                 <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($notices as $notice)
                            <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                                <span class="text-truncate" style="max-width: 70%;">
                                    {{ $notice->subject }}
                                </span>
                                <small class="text-muted">{{ substr($notice->m_date, 0, 10) }}</small>
                            </li>
                        @empty
                            <li class="list-group-item text-center">등록된 공지사항이 없습니다.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById('purchaseChart').getContext('2d');
        var chartData = {
            labels: {!! json_encode($purchaseStats['dates'] ?? []) !!},
            datasets: [{
                label: '매입 금액 (원)',
                data: {!! json_encode($purchaseStats['amounts'] ?? []) !!},
                backgroundColor: 'rgba(60, 141, 188, 0.9)',
                borderColor: 'rgba(60, 141, 188, 0.8)',
                pointRadius: 3,
                pointColor: '#3b8bba',
                pointStrokeColor: 'rgba(60,141,188,1)',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(60,141,188,1)',
                fill: true
            }]
        };

        var purchaseChart = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                maintainAspectRatio: false,
                responsive: true,
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false,
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false,
                        },
                        ticks: {
                            callback: function(value, index, values) {
                                return value.toLocaleString() + '원';
                            }
                        }
                    }]
                }
            }
        });
    });
</script>
@endsection
