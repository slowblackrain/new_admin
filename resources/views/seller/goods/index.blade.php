@extends('seller.layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">상품 관리</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">상품 목록</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Search Filter -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('seller.goods.index') }}" class="form-inline">
                        <div class="form-group mr-2">
                            <label class="mr-2">검색어</label>
                            <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}" placeholder="상품명/상품코드">
                        </div>
                        <button type="submit" class="btn btn-primary">검색</button>
                        <a href="{{ route('seller.goods.index') }}" class="btn btn-default ml-1">초기화</a>
                    </form>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-12 text-right">
                    {{-- Only Admin (3151) has full create permission usually, but let's check view logic --}}
                    @if(Auth::guard('seller')->user()->provider_seq == 3151)
                        <a href="{{ route('seller.goods.create') }}" class="btn btn-primary">상품등록</a>
                    @endif
                </div>
            </div>

            <!-- Goods List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">상품 리스트 (총 {{ $goods->total() }}건)</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap text-center">
                        <thead>
                        <tr>
                            <th>번호</th>
                            <th>이미지</th>
                            <th>상품명/코드</th>
                            <th>소비자가</th>
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
                                    <div class="text-muted text-sm">{{ $item->goods_code }}</div>
                                </td>
                                <td>{{ number_format($item->consumer_price) }}</td>
                                <td>{{ number_format($item->price) }}</td>
                                <td>{{ number_format($item->supply_price) }}</td>
                                <td>{{ number_format($item->stock) }}</td>
                                <td>
                                    @if($item->goods_status == 'normal')
                                        <span class="badge badge-success">판매중</span>
                                    @elseif($item->goods_status == 'runout')
                                        <span class="badge badge-danger">품절</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $item->goods_status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('seller.goods.edit', $item->goods_seq) }}" class="btn btn-sm btn-info">수정</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">등록된 상품이 없습니다.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $goods->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection
