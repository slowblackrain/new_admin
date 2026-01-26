@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">재고 조정 (Stock Revision)</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">상품 목록</h3>
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

                    <!-- Revision Form -->
                    <form action="{{ route('admin.scm_manage.save_revision') }}" method="POST">
                        @csrf
                        <div class="mb-2 text-right">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('입력한 재고로 일괄 수정하시겠습니까?');">
                                <i class="fas fa-save"></i> 변경사항 저장
                            </button>
                        </div>

                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>상품코드</th>
                                    <th>상품명</th>
                                    <th>현재 전산재고</th>
                                    <th>실사 재고 (수정)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($goods as $item)
                                <tr>
                                    <td>{{ $item->goods_code }}</td>
                                    <td>{{ $item->goods_name }}</td>
                                    <td>{{ number_format($item->current_stock) }}</td>
                                    <td>
                                        <input type="number" 
                                               name="stock[{{ $item->goods_seq }}]" 
                                               class="form-control form-control-sm" 
                                               style="width: 100px" 
                                               value="{{ $item->current_stock }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <div class="mt-3">
                            {{ $goods->links() }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
