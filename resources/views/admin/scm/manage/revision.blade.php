@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">재고 조정 관리 (Stock Revision)</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">조정 이력</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.scm_manage.revision.regist') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> 재고 조정 등록
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <form method="get" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" name="keyword" class="form-control" placeholder="조정코드 검색" value="{{ request('keyword') }}">
                                    <span class="input-group-append">
                                        <button class="btn btn-secondary" type="submit">검색</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Excel Upload Section -->
                    <div class="card card-secondary collapsed-card mb-3">
                        <div class="card-header">
                            <h3 class="card-title">엑셀 일괄 조정 (Excel Bulk Revision)</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.scm_manage.revision.excel') }}" method="post" enctype="multipart/form-data" class="form-inline">
                                @csrf
                                <div class="form-group mr-2">
                                    <a href="{{ route('admin.scm_manage.revision.sample') }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-download"></i> 샘플 양식 다운로드
                                    </a>
                                </div>
                                <div class="form-group mr-2">
                                    <label for="excelFile" class="mr-2">파일 선택:</label>
                                    <input type="file" name="revision_excel_file" id="excelFile" class="form-control-file" required>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('일괄 조정을 진행하시겠습니까?');">
                                    <i class="fas fa-file-upload"></i> 일괄 업로드
                                </button>
                                <small class="text-muted ml-3">
                                    * A열: 시스템코드, B열: 상품코드(필수), C열: 목표수량(필수)<br>
                                    * 목표수량과 현재수량의 차이만큼 자동 조정됩니다.
                                </small>
                            </form>
                        </div>
                    </div>

                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>조정일시</th>
                                <th>조정코드</th>
                                <th>창고</th>
                                <th>유형</th>
                                <th>총 수량</th>
                                <th>비고</th>
                                <th>상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($revisions as $rev)
                            <tr>
                                <td>{{ $rev->regist_date->format('Y-m-d H:i') }}</td>
                                <td>{{ $rev->revision_code }}</td>
                                <td>{{ $warehouses[$rev->wh_seq]->wh_name ?? $rev->wh_seq }}</td>
                                <td>
                                    @if($rev->revision_type == 1) <span class="badge badge-success">증가</span>
                                    @elseif($rev->revision_type == 2) <span class="badge badge-danger">감소</span>
                                    @elseif($rev->revision_type == 3) <span class="badge badge-warning">설정(Set)</span>
                                    @elseif($rev->revision_type == 4) <span class="badge badge-dark">폐기</span>
                                    @else {{ $rev->revision_type }}
                                    @endif
                                </td>
                                <td>{{ number_format($rev->total_ea) }}</td>
                                <td>{{ $rev->admin_memo }}</td>
                                <td>
                                    @if($rev->revision_status == 1) 완료
                                    @else 임시
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">조정 이력이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $revisions->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
