@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="page-title">재고이동 관리</h1>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">재고이동 내역</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.scm_manage.stockmove_regist') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> 재고이동 등록
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <form action="{{ route('admin.scm_manage.stockmove') }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="keyword" class="form-control" placeholder="이동코드 검색" value="{{ request('keyword') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-secondary">검색</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>번호</th>
                                    <th>이동코드</th>
                                    <th>출고창고</th>
                                    <th>입고창고</th>
                                    <th>총 수량</th>
                                    <th>상태</th>
                                    <th>등록일</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($moves as $move)
                                <tr>
                                    <td>{{ $move->move_seq }}</td>
                                    <td>{{ $move->move_code }}</td>
                                    <td>{{ $warehouses[$move->out_wh_seq]->wh_name ?? 'Unknown' }}</td>
                                    <td>{{ $warehouses[$move->in_wh_seq]->wh_name ?? 'Unknown' }}</td>
                                    <td>{{ number_format($move->total_ea) }}</td>
                                    <td>
                                        @if($move->move_status == 2)
                                            <span class="badge bg-success">완료</span>
                                        @else
                                            <span class="badge bg-warning">대기</span>
                                        @endif
                                    </td>
                                    <td>{{ $move->regist_date }}</td>
                                    <td>
                                        <a href="{{ route('admin.scm_manage.stockmove_regist', ['seq' => $move->move_seq]) }}" class="btn btn-sm btn-info">
                                            상세
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">재고 이동 내역이 없습니다.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $moves->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
