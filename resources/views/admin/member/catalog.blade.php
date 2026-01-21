@extends('admin.layouts.admin')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">회원리스트</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">회원 검색</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.member.catalog') }}" method="GET" class="form-inline">
                        <input type="text" name="keyword" class="form-control mr-2" placeholder="아이디/이름" value="{{ request('keyword') }}">
                        <button type="submit" class="btn btn-primary">검색</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>가입일</th>
                                <th>아이디</th>
                                <th>이름</th>
                                <th>이메일</th>
                                <th>등급</th>
                                <th>상태</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($members as $member)
                                <tr>
                                    <td>{{ $member->regist_date }}</td>
                                    <td>
                                        <a href="{{ route('admin.member.view', $member->member_seq) }}">
                                            {{ $member->userid }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.member.view', $member->member_seq) }}">
                                            {{ $member->user_name }}
                                        </a>
                                    </td>
                                    <td>{{ $member->email }}</td>
                                    <td>{{ $member->group_seq }}</td>
                                    <td>
                                        <span class="badge badge-success">{{ $member->status }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary">상세</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">회원이 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $members->withQueryString()->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection
