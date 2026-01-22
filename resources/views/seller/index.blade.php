@extends('seller.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Welcome Message -->
    <div class="col-12 mb-3">
        <div class="callout callout-info">
            <h5>Welcome, {{ $seller->provider_name ?? $seller->provider_id }}!</h5>
            <p>Today is {{ date('Y-m-d') }}. Check your order status below.</p>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="col-lg-3 col-6">
        <!-- Ready to Ship -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($readyToShipCnt) }}</h3>
                <p>Ready to Ship</p>
            </div>
            <div class="icon">
                <i class="fas fa-box-open"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- Claims -->
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($claimCnt) }}</h3>
                <p>Claims (Ret/Exc)</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- New Orders -->
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($newOrderCnt) }}</h3>
                <p>New Orders</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
        <!-- Settlement (Placeholder) -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>-</h3>
                <p>Settlement</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>

<!-- Main Row -->
<div class="row">
    <!-- Left col -->
    <section class="col-lg-7 connectedSortable">
        <!-- Notices -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bullhorn mr-1"></i>
                    Seller Notices
                </h3>
            </div>
            <div class="card-body">
                <ul class="todo-list" data-widget="todo-list">
                    @forelse($notices as $notice)
                        <li>
                            <span class="text">{{ $notice->subject }}</span>
                            <!-- Assuming date is stored as timestamp or datetime string -->
                            <small class="badge badge-secondary"><i class="far fa-clock"></i> 
                                {{ $notice->m_date ? substr($notice->m_date, 0, 10) : '' }}
                            </small>
                        </li>
                    @empty
                        <li>No notices found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </section>
    
    <!-- Right col -->
    <section class="col-lg-5 connectedSortable">
       <!-- Calendar or other widgets -->
    </section>
</div>
@endsection
