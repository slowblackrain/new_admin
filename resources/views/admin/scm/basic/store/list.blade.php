@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <!-- Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">매장 목록 (Store List)</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.scm_basic.store.form') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> 매장 등록
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <!-- List -->
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>매장명</th>
                                <th>구분</th>
                                <th>URL</th>
                                <th>등록일</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stores as $store)
                                <tr>
                                    <td>{{ $store->store_seq }}</td>
                                    <td>{{ $store->store_name }}</td>
                                    <td>{{ $store->store_type }}</td>
                                    <td>{{ $store->store_url }}</td>
                                    <td>{{ $store->regist_date }}</td>
                                    <td>
                                        <a href="{{ route('admin.scm_basic.store.form', ['seq' => $store->store_seq]) }}" class="btn btn-sm btn-info">수정</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">등록된 매장이 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $stores->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
