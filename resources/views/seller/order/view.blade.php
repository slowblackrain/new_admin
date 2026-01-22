@extends('seller.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Order Detail</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('seller.index') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('seller.order.catalog') }}">Order List</a></li>
                    <li class="breadcrumb-item active">{{ $order->order_seq }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- Order Summary -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-invoice"></i> Order Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <p><strong>Order No:</strong> {{ $order->order_seq }}</p>
                                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->regist_date)->format('Y-m-d H:i:s') }}</p>
                            </div>
                            <div class="col-12 col-md-4">
                                <p><strong>Status:</strong> <span class="badge badge-info">{{ config('step')[$order->step] ?? $order->step }}</span></p>
                                <p><strong>Payment:</strong> {{ number_format($order->settleprice) }} KRW</p>
                            </div>
                            <div class="col-12 col-md-4">
                                <p><strong>Purchaser:</strong> {{ $order->order_user_name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ordered Items</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 10%">Image</th>
                                    <th>Product</th>
                                    <th>Option</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                             @php
                                                $imgSrc = '/images/no_image.gif';
                                                if($item->goods && $item->goods->image) { 
                                                     $imgSrc = asset($item->goods->image); 
                                                } elseif($item->image) {
                                                     $imgSrc = asset($item->image);
                                                }
                                            @endphp
                                            <img src="{{ $imgSrc }}" alt="Product" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                        </td>
                                        <td>{{ $item->goods_name }}</td>
                                        <td>
                                            @foreach($item->options as $opt)
                                                <div style="margin-bottom: 5px;">
                                                    @if($opt->option1)
                                                        <small>{{ $opt->title1 }}: {{ $opt->option1 }}</small>
                                                    @endif
                                                    @if($opt->option2)
                                                         / <small>{{ $opt->title2 }}: {{ $opt->option2 }}</small>
                                                    @endif
                                                    @if(!$opt->option1)
                                                        <small>기본옵션</small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($item->options as $opt)
                                                <div style="margin-bottom: 5px;">{{ number_format($opt->ea) }}</div>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($item->options as $opt)
                                                <div style="margin-bottom: 5px;">{{ number_format($opt->price) }}</div>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($item->options as $opt)
                                                <div style="margin-bottom: 5px;">
                                                    <span class="badge badge-{{ $opt->step >= 75 ? 'success' : ($opt->step == 15 ? 'danger' : 'secondary') }}">
                                                        {{ config('step')[$opt->step] ?? $opt->step }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recipient Info -->
         <div class="row">
            <div class="col-md-6">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Recipient Information</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $order->recipient_user_name }}</p>
                        <p><strong>Phone:</strong> {{ $order->recipient_phone }}</p>
                        <p><strong>Cell:</strong> {{ $order->recipient_cellphone }}</p>
                        <p><strong>Zip:</strong> {{ $order->recipient_zipcode }}</p>
                        <p><strong>Address:</strong> {{ $order->recipient_address }} {{ $order->recipient_address_detail }}</p>
                        <p><strong>Memo:</strong> {{ $order->memo }}</p>
                    </div>
                </div>
            </div>
             <div class="col-md-6">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Shipping Information</h3>
                    </div>
                    <div class="card-body">
                         <p><strong>Method:</strong> {{ $order->shipping_method_name ?? 'Standard Delivery' }}</p>
                         <p><strong>Tracking No:</strong> {{ $order->delivery_number ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row no-print">
            <div class="col-12">
                <a href="{{ route('seller.order.catalog') }}" class="btn btn-default float-right">Back to List</a>
            </div>
        </div>
    </div>
</div>
@endsection
