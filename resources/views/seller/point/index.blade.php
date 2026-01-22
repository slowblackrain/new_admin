@extends('seller.layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="point-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $type == 'emoney' ? 'active' : '' }}" href="{{ route('seller.point.emoney') }}" role="tab">적립금</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $type == 'cash' ? 'active' : '' }}" href="{{ route('seller.point.cash') }}" role="tab">이머니(예치금)</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                    <div class="input-group-append"><span class="input-group-text">~</span></div>
                                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">검색</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- List -->
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>번호</th>
                                    <th>날짜</th>
                                    <th>{{ $type == 'emoney' ? '적립금' : '이머니' }}</th>
                                    <th>내용</th>
                                    <th>관련주문</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $log->{$type.'_seq'} }}</td>
                                        <td>{{ $log->regist_date }}</td>
                                        <td>
                                            <span class="{{ $log->{$type} > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($log->{$type}) }}
                                            </span>
                                        </td>
                                        <td>{{ $log->memo }}</td>
                                        <td>{{ $log->ordno ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">내역이 없습니다.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    {{ $logs->withQueryString()->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
@endsection
