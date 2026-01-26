@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">재고 수불부 (Stock Ledger)</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">재고 변동 이력 (입고/조정)</h3>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="get" class="mb-3">
                        <div class="row">
                            <div class="col-md-5">
                                <input type="text" name="keyword" class="form-control" placeholder="상품명/코드 검색" value="{{ request('keyword') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">검색</button>
                            </div>
                        </div>
                    </form>

                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th style="width: 100px">구분</th>
                                <th style="width: 180px">일자</th>
                                <th>상품 정보</th>
                                <th style="width: 120px">변동 수량</th>
                                <th>비고 / 관련 코드</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td class="text-center">
                                    @if($log->type == '입고')
                                        <span class="badge badge-success">입고</span>
                                    @elseif($log->type == '조정')
                                        <span class="badge badge-warning">조정</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $log->type }}</span>
                                    @endif
                                </td>
                                <td>{{ $log->date }}</td>
                                <td>
                                    <strong>{{ $log->goods_name }}</strong><br>
                                    <small class="text-muted">{{ $log->goods_code }}</small>
                                </td>
                                <td class="text-right">
                                    @if($log->qty > 0)
                                        <span class="text-success">+{{ number_format($log->qty) }}</span>
                                    @elseif($log->qty < 0)
                                        <span class="text-danger">{{ number_format($log->qty) }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>{{ $log->note }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">조회된 이력이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="mt-3">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
