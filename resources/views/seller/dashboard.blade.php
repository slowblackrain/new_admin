@extends('seller.layouts.app')

@section('title', 'Seller Dashboard')
@section('body_class', 'sidebar-mini')

@section('content')
<div class="container-fluid">
    <!-- Top Banner (Placeholder) -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-light border" role="alert">
                <strong>Welcome, {{ Auth::guard('seller')->user()->provider_name }}!</strong> 
                (Member Seq: {{ $memberData->member_seq ?? 'N/A' }})
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Seller Summary (Notices) -->
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bullhorn mr-1"></i>
                        입점사공지 (Notices)
                    </h3>
                    <div class="card-tools">
                         <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($notices) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($notices as $notice)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-truncate" style="max-width: 70%;">
                                        {{ $notice->subject }}
                                    </span>
                                    <small class="text-muted">{{ substr($notice->m_date, 0, 10) }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center py-3">등록된 공지사항이 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Order Summary -->
        <div class="col-md-6">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shipping-fast mr-1"></i>
                        주문/배송 현황 (Order Status)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Ready to Ship -->
                        <div class="col-12 col-sm-6">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fas fa-box-open"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ $orderSummary['ready_to_ship']['title'] ?? '출고준비건' }}</span>
                                    <span class="info-box-number">{{ $orderSummary['ready_to_ship']['count'] ?? 0 }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        <a href="{{ $orderSummary['ready_to_ship']['link'] ?? '#' }}" class="text-white">
                                            View Details <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Add other summaries here later -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
