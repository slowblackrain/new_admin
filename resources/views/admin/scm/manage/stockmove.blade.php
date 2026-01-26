@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>재고이동 관리</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin">Home</a></li>
                        <li class="breadcrumb-item active">재고이동</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Search -->
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.scm_manage.stockmove') }}" method="GET" class="form-inline">
                        <div class="form-group mr-2">
                            <input type="text" name="keyword" class="form-control" placeholder="이동코드 검색" value="{{ request('keyword') }}">
                        </div>
                        <button type="submit" class="btn btn-primary">검색</button>
                    </form>
                </div>
            </div>

            <!-- List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">이동 내역 (총 {{ $moves->total() }}건)</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.scm_manage.stockmove.regist') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus"></i> 재고이동 등록
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>번호</th>
                                <th>이동코드</th>
                                <th>출고창고</th>
                                <th>입고창고</th>
                                <th>총 수량</th>
                                <th>상태</th>
                                <th>처리일시</th>
                                <th>관리자 메모</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($moves as $move)
                            <tr>
                                <td>{{ $move->move_seq }}</td>
                                <td>{{ $move->move_code }}</td>
                                <td>
                                    {{ $warehouses[$move->out_wh_seq]->wh_name ?? $move->out_wh_seq }}
                                </td>
                                <td>
                                    {{ $warehouses[$move->in_wh_seq]->wh_name ?? $move->in_wh_seq }}
                                </td>
                                <td>{{ number_format($move->total_ea) }}</td>
                                <td>
                                    @if($move->move_status == 1)
                                        <span class="badge badge-warning">요청</span>
                                    @elseif($move->move_status == 2)
                                        <span class="badge badge-success">완료</span>
                                    @else
                                        {{ $move->move_status }}
                                    @endif
                                </td>
                                <td>{{ $move->complete_date }}</td>
                                <td>{{ $move->admin_memo }}</td>
                            </tr>
                            @endforeach
                            
                            @if($moves->isEmpty())
                            <tr>
                                <td colspan="8" class="text-center">이동 내역이 없습니다.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $moves->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
