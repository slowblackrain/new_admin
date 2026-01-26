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
            <!-- Search Form (Legacy Style) -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">회원 검색</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.member.catalog') }}" method="GET">
                        <table class="table table-bordered table-sm search-table">
                            <colgroup>
                                <col width="150" style="background-color: #f4f6f9;">
                                <col>
                                <col width="150" style="background-color: #f4f6f9;">
                                <col>
                            </colgroup>
                            <tbody>
                                <tr>
                                    <th>검색어</th>
                                    <td colspan="3">
                                        <select name="keyword_type" class="form-control form-control-sm d-inline-block" style="width: 100px;">
                                            <option value="">통합검색</option>
                                            <option value="userid">아이디</option>
                                            <option value="user_name">이름</option>
                                            <option value="email">이메일</option>
                                            <option value="phone">전화번호</option>
                                        </select>
                                        <input type="text" name="keyword" class="form-control form-control-sm d-inline-block" style="width: 300px;" value="{{ request('keyword') }}">
                                    </td>
                                </tr>
                                <tr>
                                    <th>가입일</th>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <input type="date" name="start_date" class="form-control form-control-sm" style="width: 150px;" value="{{ request('start_date') }}">
                                            <span class="mx-2">~</span>
                                            <input type="date" name="end_date" class="form-control form-control-sm" style="width: 150px;" value="{{ request('end_date') }}">
                                        </div>
                                    </td>
                                    <th>최종 로그인</th>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <input type="date" name="lastlogin_start" class="form-control form-control-sm" style="width: 150px;" value="{{ request('lastlogin_start') }}">
                                            <span class="mx-2">~</span>
                                            <input type="date" name="lastlogin_end" class="form-control form-control-sm" style="width: 150px;" value="{{ request('lastlogin_end') }}">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>회원등급</th>
                                    <td>
                                        <select name="group_seq" class="form-control form-control-sm" style="width: 200px;">
                                            <option value="">전체</option>
                                            @foreach($groups as $g)
                                                <option value="{{ $g->group_seq }}" {{ request('group_seq') == $g->group_seq ? 'selected' : '' }}>
                                                    {{ $g->group_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <th>가입승인</th>
                                    <td>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" value="" {{ !request('status') ? 'checked' : '' }}>
                                            <label class="form-check-label">전체</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" value="done" {{ request('status') == 'done' ? 'checked' : '' }}>
                                            <label class="form-check-label">승인</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" value="hold" {{ request('status') == 'hold' ? 'checked' : '' }}>
                                            <label class="form-check-label">대기</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" value="dormancy" {{ request('status') == 'dormancy' ? 'checked' : '' }}>
                                            <label class="form-check-label">휴면</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>수신동의</th>
                                    <td colspan="3">
                                        <span class="mr-3 font-weight-bold">SMS:</span>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="sms" value="" {{ !request('sms') ? 'checked' : '' }}>
                                            <label class="form-check-label">전체</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="sms" value="y" {{ request('sms') == 'y' ? 'checked' : '' }}>
                                            <label class="form-check-label">동의</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="sms" value="n" {{ request('sms') == 'n' ? 'checked' : '' }}>
                                            <label class="form-check-label">거부</label>
                                        </div>

                                        <span class="mr-3 ml-4 font-weight-bold">이메일:</span>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mailing" value="" {{ !request('mailing') ? 'checked' : '' }}>
                                            <label class="form-check-label">전체</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mailing" value="y" {{ request('mailing') == 'y' ? 'checked' : '' }}>
                                            <label class="form-check-label">동의</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mailing" value="n" {{ request('mailing') == 'n' ? 'checked' : '' }}>
                                            <label class="form-check-label">거부</label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-dark btn-lg px-5">검색</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List Table (Legacy Style Columns) -->
            <div class="card">
                <div class="card-header">
                     <h3 class="card-title">총 {{ number_format($members->total()) }}명</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-hover text-sm table-striped">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th width="30"><input type="checkbox"></th>
                                <th width="50">번호</th>
                                <th width="60">유입</th>
                                <th width="60">승인</th>
                                <th width="80">등급</th>
                                <th width="60">유형</th>
                                <th width="">아이디</th>
                                <th width="">이름</th>
                                <th width="200">이메일/핸드폰</th>
                                <th width="120">전화번호</th>
                                <th width="100">가입일<br>최종방문</th>
                                <th width="80">적립금</th>
                                <th width="80">포인트</th>
                                <th width="80">발주캐시</th>
                                <th width="80">신상캐시</th>
                                <th width="60">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($members as $index => $member)
                                <tr>
                                    <td class="text-center align-middle"><input type="checkbox" value="{{ $member->member_seq }}"></td>
                                    <td class="text-center align-middle">{{ $members->firstItem() + $index }}</td>
                                    <td class="text-center align-middle">{{ $member->referer ?? '-' }}</td>
                                    <td class="text-center align-middle">
                                        @if($member->status == 'done') 승인
                                        @elseif($member->status == 'hold') <span class="text-danger">대기</span>
                                        @elseif($member->status == 'dormancy') <span class="text-muted">휴면</span>
                                        @else {{ $member->status }} @endif
                                    </td>
                                    <td class="text-center align-middle">{{ $member->group_name }}</td>
                                    <td class="text-center align-middle">
                                        {{ $member->business_seq == 'y' ? '사업자' : '개인' }}
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('admin.member.view', $member->member_seq) }}" class="font-weight-bold text-primary">
                                            {{ $member->userid }}
                                        </a>
                                    </td>
                                    <td class="align-middle">{{ $member->user_name }}</td>
                                    <td class="align-middle small">
                                        {{ $member->email }} <span class="text-muted">({{ strtoupper($member->mailing) }})</span><br>
                                        {{ $member->cellphone }} <span class="text-muted">({{ strtoupper($member->sms) }})</span>
                                    </td>
                                    <td class="text-center align-middle">{{ $member->phone }}</td>
                                    <td class="text-center align-middle small">
                                        {{ substr($member->regist_date, 0, 10) }}<br>
                                        <span class="text-muted">{{ substr($member->lastlogin_date, 0, 10) ?? '-' }}</span>
                                    </td>
                                    <td class="text-right align-middle text-primary font-weight-bold cursor-pointer">
                                        {{ number_format($member->emoney) }}
                                    </td>
                                    <td class="text-right align-middle text-success font-weight-bold cursor-pointer">
                                        {{ number_format($member->point) }}
                                    </td>
                                    <td class="text-right align-middle text-info font-weight-bold cursor-pointer">
                                        {{ number_format($member->cash) }}
                                    </td>
                                    <td class="text-right align-middle text-secondary font-weight-bold cursor-pointer">
                                        {{ number_format($member->order_cash) }}
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="{{ route('admin.member.view', $member->member_seq) }}" class="btn btn-xs btn-secondary">상세</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="16" class="text-center py-4">검색된 회원이 없습니다.</td>
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
