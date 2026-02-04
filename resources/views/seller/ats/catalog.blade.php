@extends('seller.layouts.app')

@section('title', '상품투자 관리 (ATS)')

@section('content')
<div class="container-fluid">
    <!-- ATS Statistics -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info">
                <strong>ATS 투자금액:</strong> {{ number_format($stats['goods_ea_price'] ?? 0) }}원 / 
                <strong>ATS 발주금액:</strong> {{ number_format($stats['goods_sorder_price'] ?? 0) }}원
            </div>
        </div>
    </div>

    <!-- Search Form -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('seller.ats.catalog') }}" method="GET" class="form-inline">
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                @if(request('ATS_status_plus'))
                    <input type="hidden" name="ATS_status_plus" value="{{ request('ATS_status_plus') }}">
                @endif
                
                <div class="form-group mr-2">
                    <label class="mr-2">기간</label>
                    <input type="date" name="sdate" class="form-control" value="{{ $startDate }}">
                    <span class="mx-1">~</span>
                    <input type="date" name="edate" class="form-control" value="{{ $endDate }}">
                </div>

                <div class="form-group mr-2">
                    <label class="mr-2">검색어</label>
                    <input type="text" name="keyword" class="form-control" placeholder="상품명/코드" value="{{ $keyword }}">
                </div>

                <button type="submit" class="btn btn-primary">검색</button>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ (!$statusPlus && $type != 'social') ? 'active' : '' }}" href="{{ route('seller.ats.catalog') }}">전체상품</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $statusPlus == 'ATS_agency' ? 'active' : '' }}" href="{{ route('seller.ats.catalog', ['ATS_status_plus' => 'ATS_agency']) }}">대행상품</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $statusPlus == 'ATS_only' ? 'active' : '' }}" href="{{ route('seller.ats.catalog', ['ATS_status_plus' => 'ATS_only']) }}">단독상품</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $type == 'social' ? 'active' : '' }}" href="{{ route('seller.ats.social_catalog') }}">티켓/쿠폰</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover text-center text-nowrap" style="font-size: 13px;">
                            <thead>
                                <tr>
                                    <th>번호</th>
                                    <th>이미지</th>
                                    <th>상품명/상품코드</th>
                                    <th>정가</th>
                                    <th>판매가</th>
                                    <th>공급가</th>
                                    <th>재고</th>
                                    <th>상태</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($goods as $item)
                                    <tr>
                                        <td>{{ $item->goods_seq }}</td>
                                        <td>
                                            @if($item->file_key_w)
                                                <img src="/data/goods/{{ $item->file_key_w }}" width="50" height="50" alt="img" onerror="this.src='/images/no_image.gif'">
                                            @else
                                                <span class="text-muted">No Image</span>
                                            @endif
                                        </td>
                                        <td class="text-left">
                                            <div><strong>{{ $item->goods_name }}</strong></div>
                                            <div class="text-muted">{{ $item->goods_code }}</div>
                                        </td>
                                        <td>{{ number_format($item->consumer_price ?? 0) }}</td>
                                        <td>{{ number_format($item->price ?? 0) }}</td>
                                        <td>{{ number_format($item->supply_price ?? 0) }}</td>
                                        <td>{{ number_format($item->stock ?? 0) }}</td>
                                        <td>
                                            @if(strpos($item->goods_status_info ?? '', 'runout_order') !== false)
                                                <span class="badge badge-warning">단종요청중</span>
                                            @elseif($item->goods_status == 'normal')
                                                <span class="badge badge-success">판매중</span>
                                            @elseif($item->goods_status == 'runout')
                                                <span class="badge badge-danger">품절</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $item->goods_status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical">
                                                @if(strpos($item->goods_status_info ?? '', 'runout_order') === false)
                                                    <button type="button" class="btn btn-sm btn-danger btn-runout mb-1" data-seq="{{ $item->goods_seq }}">
                                                        단종요청
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-secondary mb-1" disabled>
                                                        요청완료
                                                    </button>
                                                @endif

                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">등록된 상품이 없습니다.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    {{ $goods->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Runout Request Logic
        document.querySelectorAll('.btn-runout').forEach(btn => {
            btn.addEventListener('click', function() {
                if(!confirm('정말 단종 요청을 하시겠습니까?')) return;
                
                const goodsSeq = this.dataset.seq;

                fetch("{{ route('seller.ats.runout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ goods_seq: goodsSeq })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success') {
                        alert('단종 요청이 완료되었습니다.');
                        location.reload();
                    } else {
                        alert('요청 중 오류가 발생했습니다: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('서버 통신 중 오류가 발생했습니다.');
                });
            });
        });

            });
        });
    });
</script>
@endsection
