@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="h3 mb-4 page-title">재고 이동 목록 (Stock Move List)</h2>
        
        <div class="card shadow mb-4">
            <div class="card-header">
                <a href="{{ route('admin.scm_manage.stockmove.regist') }}" class="btn btn-primary float-right">재고 이동 등록</a>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>이동 코드</th>
                            <th>상태</th>
                            <th>보내는 창고</th>
                            <th>받는 창고</th>
                            <th>등록일</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($moves as $move)
                            <tr>
                                <td>{{ $move->move_code }}</td>
                                <td>
                                    @if($move->move_status == 1 || $move->move_status == 2)
                                        <span class="badge badge-success">완료</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $move->move_status }}</span>
                                    @endif
                                </td>
                                <td>{{ $move->out_wh_name }}</td>
                                <td>{{ $move->in_wh_name }}</td>
                                <td>{{ $move->regist_date }}</td>
                                <td>
                                    <a href="{{ route('admin.scm_manage.stockmove.regist', ['smno' => $move->move_seq]) }}" class="btn btn-sm btn-info">상세</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">데이터가 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $moves->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
