@extends('admin.layouts.admin')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">할인 이벤트 관리</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.event.regist') }}" class="btn btn-primary">이벤트 등록</a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Search Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">이벤트 검색</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.event.catalog') }}" method="GET">
                        <table class="table table-bordered table-sm search-table">
                            <colgroup>
                                <col width="150" style="background-color: #f4f6f9;">
                                <col>
                            </colgroup>
                            <tbody>
                                <tr>
                                    <th>상태/기간</th>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <input type="date" name="sdate" class="form-control form-control-sm" style="width: 150px;" value="{{ request('sdate') }}">
                                            <span class="mx-2">~</span>
                                            <input type="date" name="edate" class="form-control form-control-sm" style="width: 150px;" value="{{ request('edate') }}">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>이벤트명</th>
                                    <td>
                                        <input type="text" name="keyword" class="form-control form-control-sm d-inline-block" style="width: 300px;" placeholder="이벤트명 검색" value="{{ request('keyword') }}">
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

            <!-- List Table -->
            <div class="card">
                <div class="card-header">
                     <h3 class="card-title">총 {{ number_format($events->total()) }}건</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-hover text-sm table-striped">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th width="50">번호</th>
                                <th width="100">유형</th>
                                <th>이벤트명(템플릿명)</th>
                                <th width="200">진행기간</th>
                                <th width="100">조회수</th>
                                <th width="100">노출여부</th>
                                <th width="80">수정</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $index => $event)
                                <tr>
                                    <td class="text-center align-middle">{{ $events->firstItem() + $index }}</td>
                                    <td class="text-center align-middle">
                                        @if($event->event_type == 'solo') 단독 @else 일반 @endif
                                    </td>
                                    <td class="align-middle font-weight-bold">{{ $event->title }}</td>
                                    <td class="text-center align-middle">
                                        {{ substr($event->start_date, 0, 10) }} ~ {{ substr($event->end_date, 0, 10) }}
                                    </td>
                                    <td class="text-center align-middle">{{ number_format($event->hit ?? 0) }}</td>
                                    <td class="text-center align-middle">
                                        @if($event->display == 'y')
                                            <span class="text-success">노출</span>
                                        @else
                                            <span class="text-danger">미노출</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <a href="{{ route('admin.event.regist', ['no' => $event->event_seq]) }}" class="btn btn-xs btn-secondary">수정</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">검색된 이벤트가 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $events->withQueryString()->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection
