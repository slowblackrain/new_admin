@extends('admin.layouts.admin')

@section('content')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark">무통장 입금확인</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">무통장 입금확인</li>
        </ol>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">검색 조건</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.order.bank_check') }}" method="GET">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>검색어 (입금자/메모)</label>
                        <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}" placeholder="입금자 또는 메모">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>조회 기간</label>
                        <div class="input-group">
                            <input type="date" name="sdate" class="form-control" value="{{ request('sdate') }}">
                            <div class="input-group-append"><span class="input-group-text">~</span></div>
                            <input type="date" name="edate" class="form-control" value="{{ request('edate') }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>은행 선택</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="bank[]" value="농협" {{ in_array('농협', request('bank', [])) ? 'checked' : '' }}>
                            <label class="form-check-label">농협</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="bank[]" value="국민" {{ in_array('국민', request('bank', [])) ? 'checked' : '' }}>
                            <label class="form-check-label">국민</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="bank[]" value="신한" {{ in_array('신한', request('bank', [])) ? 'checked' : '' }}>
                            <label class="form-check-label">신한</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="deli_ck" value="1" {{ request('deli_ck') ? 'checked' : '' }}>
                            <label class="form-check-label">미매칭만 보기</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-right">
                    <button type="submit" class="btn btn-primary">검색</button>
                    <a href="{{ route('admin.order.bank_check') }}" class="btn btn-secondary">초기화</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">입금 내역 (총 {{ number_format($data->total()) }}건)</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>번호</th>
                    <th>은행</th>
                    <th>입금자</th>
                    <th>입금금액</th>
                    <th>입금일시</th>
                    <th>상태</th>
                    <th>매칭/주문번호</th>
                    <th>메모</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    <tr>
                        <td>{{ $row->idx }}</td>
                        <td>{{ $row->in_bank }}</td>
                        <td>{{ $row->in_name }}</td>
                        <td>{{ number_format($row->in_price) }}</td>
                        <td>{{ $row->update_time }}</td>
                        <td>
                            @if($row->pg_status == 'R') <span class="badge badge-warning">대기</span>
                            @elseif($row->pg_status == 'M') <span class="badge badge-success">매칭됨</span>
                            @elseif($row->pg_status == 'D') <span class="badge badge-secondary">제외</span>
                            @endif
                        </td>
                        <td>
                            @if($row->pg_status == 'R')
                                <button class="btn btn-sm btn-danger" onclick="openMatchPopup({{ $row->idx }})">수동매칭</button>
                            @else
                                <a href="#">{{ $row->order_seq }}</a>
                            @endif
                        </td>
                        <td>{{ $row->memo }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">데이터가 없습니다.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $data->withQueryString()->links('pagination::bootstrap-4') }}
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="matchModal" tabindex="-1" role="dialog" aria-labelledby="matchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="matchModalLabel">입금내역 매칭</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="matchModalBody">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
    function openMatchPopup(idx) {
        $('#matchModalBody').html('<div class="text-center p-5"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>');
        $('#matchModal').modal('show');

        // Load candidates via AJAX
        $.get("{{ route('admin.order.bank_check.match') }}", { idx: idx }, function(html) {
            $('#matchModalBody').html(html);
        });
    }

    function processMatch(mosms_idx, order_seq) {
        if(!confirm('선택한 주문과 매칭하시겠습니까?')) return;

        $.post("{{ route('admin.order.bank_check.process') }}", {
            _token: '{{ csrf_token() }}',
            mosms_idx: mosms_idx,
            order_seq: order_seq
        }, function(response) {
            if(response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert(response.message);
            }
        });
    }
</script>
@endsection
