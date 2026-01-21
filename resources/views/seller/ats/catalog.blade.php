@extends('seller.layouts.app')

@section('title', '상품투자 관리 (ATS)')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-primary card-outline card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ !$statusPlus ? 'active' : '' }}" href="{{ route('seller.ats.catalog') }}">전체상품</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $statusPlus == 'ATS_agency' ? 'active' : '' }}" href="{{ route('seller.ats.catalog', ['ATS_status_plus' => 'ATS_agency']) }}">대행상품</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $statusPlus == 'ATS_only' ? 'active' : '' }}" href="{{ route('seller.ats.catalog', ['ATS_status_plus' => 'ATS_only']) }}">단독상품</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('seller.ats.social_catalog') ? 'active' : '' }}" href="{{ route('seller.ats.social_catalog') }}">티켓/쿠폰</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>번호</th>
                                    <th>이미지</th>
                                    <th>상품명/상품코드</th>
                                    <th>판매가</th>
                                    <th>상태</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($goods as $item)
                                    <tr>
                                        <td>{{ $item->goods_seq }}</td>
                                        <td>
                                            @if($item->image)
                                                <img src="/data/goods/{{ $item->image }}" width="50" height="50" alt="img">
                                            @else
                                                <span class="text-muted">No Image</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $item->goods_name }}</strong><br>
                                            <small class="text-muted">{{ $item->goods_code }}</small>
                                        </td>
                                        <td>{{ number_format($item->price ?? 0) }}원</td>
                                        <td>
                                            @if(strpos($item->goods_status_info ?? '', 'runout_order') !== false)
                                                <span class="badge badge-warning">단종요청중</span>
                                            @else
                                                <span class="badge badge-success">판매중</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(strpos($item->goods_status_info ?? '', 'runout_order') === false)
                                                <button type="button" class="btn btn-sm btn-danger btn-runout" data-seq="{{ $item->goods_seq }}">
                                                    단종요청
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-secondary" disabled>
                                                    요청완료
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">등록된 상품이 없습니다.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    {{ $goods->withQueryString()->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const runoutButtons = document.querySelectorAll('.btn-runout');
        runoutButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                if(!confirm('정말 단종 요청을 하시겠습니까?')) return;
                
                const goodsSeq = this.dataset.seq;
                const btn = this;

                fetch('{{ route('seller.ats.runout') }}', {
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
</script>
@endsection
