@extends('admin.layouts.admin')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">입점사 리스트</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">입점사 검색</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.provider.catalog') }}" method="GET" class="form-inline">
                        <input type="text" name="keyword" class="form-control mr-2" placeholder="아이디/입점사명" value="{{ request('keyword') }}">
                        <button type="submit" class="btn btn-primary">검색</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>등록일</th>
                                <th>아이디</th>
                                <th>입점사명</th>
                                <th>대표자</th>
                                <th>연락처</th>
                                <th>상태</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($providers as $provider)
                                <tr>
                                    <td>{{ $provider->regdate }}</td>
                                    <td>{{ $provider->provider_id }}</td>
                                    <td>{{ $provider->provider_name }}</td>
                                    <td>{{ $provider->ceo_name }}</td>
                                    <td>{{ $provider->phone }}</td>
                                    <td>
                                        <span class="badge badge-{{ $provider->provider_status == '1' ? 'success' : 'secondary' }}">
                                            {{ $provider->provider_status == '1' ? '승인' : '대기/정지' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary">수정</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">입점사가 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $providers->withQueryString()->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection
