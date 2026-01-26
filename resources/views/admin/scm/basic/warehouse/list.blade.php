@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <!-- Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">창고 목록 (Warehouse List)</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.scm_basic.warehouse.form') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> 창고 등록
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Search Form -->
            <div class="card collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">검색 (Search)</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <form action="{{ route('admin.scm_basic.warehouse') }}" method="GET">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">창고명</label>
                            <div class="col-sm-4">
                                <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}">
                            </div>
                            <label class="col-sm-2 col-form-label">그룹</label>
                            <div class="col-sm-4">
                                <select name="wh_group" class="form-control">
                                    <option value="">전체</option>
                                    @foreach($whGroup as $group)
                                        <option value="{{ $group }}" {{ request('wh_group') == $group ? 'selected' : '' }}>{{ $group }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">검색</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List -->
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>그룹</th>
                                <th>창고명</th>
                                <th>주소</th>
                                <th>기본창고</th>
                                <th>등록일</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warehouses as $wh)
                                <tr>
                                    <td>{{ $wh->wh_seq }}</td>
                                    <td>{{ $wh->wh_group }}</td>
                                    <td>{{ $wh->wh_name }}</td>
                                    <td>{{ $wh->wh_address }} {{ $wh->wh_address_detail }}</td>
                                    <td>{{ $wh->wh_default == 1 ? '예' : '아니오' }}</td>
                                    <td>{{ $wh->wh_regist_date }}</td>
                                    <td>
                                        <a href="{{ route('admin.scm_basic.warehouse.form', ['seq' => $wh->wh_seq]) }}" class="btn btn-sm btn-info">수정</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">등록된 창고가 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $warehouses->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
