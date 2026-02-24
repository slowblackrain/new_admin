@extends('admin.layouts.admin')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">클레임 통합 리스트</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        <!-- 검색 필터 -->
        <div class="card card-outline card-primary mb-4">
            <div class="card-header">
                <h3 class="card-title">클레임 검색</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.order.claim.list') }}" method="GET">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">검색어</span>
                                </div>
                                <input type="text" name="keyword" class="form-control form-control-sm" placeholder="주문 번호 또는 클레임 번호" value="{{ request('keyword') }}">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">처리 상태</span>
                                </div>
                                <div class="form-control form-control-sm" style="height: auto;">
                                    <div class="icheck-primary d-inline mr-3">
                                        <input type="checkbox" id="status_req" name="status[]" value="request" {{ in_array('request', request('status', [])) ? 'checked' : '' }}>
                                        <label for="status_req">신청</label>
                                    </div>
                                    <div class="icheck-primary d-inline mr-3">
                                        <input type="checkbox" id="status_ing" name="status[]" value="ing" {{ in_array('ing', request('status', [])) ? 'checked' : '' }}>
                                        <label for="status_ing">처리중</label>
                                    </div>
                                    <div class="icheck-primary d-inline mr-3">
                                        <input type="checkbox" id="status_comp" name="status[]" value="complete" {{ in_array('complete', request('status', [])) ? 'checked' : '' }}>
                                        <label for="status_comp">처리완료</label>
                                    </div>
                                    <div class="icheck-primary d-inline mr-3">
                                        <input type="checkbox" id="status_cancel" name="status[]" value="cancel" {{ in_array('cancel', request('status', [])) ? 'checked' : '' }}>
                                        <label for="status_cancel">클레임취소</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-right">
                            <button type="submit" class="btn btn-dark btn-sm px-4">검색</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 탭 네비게이션 -->
        <ul class="nav nav-tabs custom-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'all' ? 'active font-weight-bold' : '' }}" href="{{ route('admin.order.claim.list', array_merge(request()->query(), ['tab' => 'all'])) }}">통합 로드</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'returns' ? 'active font-weight-bold' : '' }}" href="{{ route('admin.order.claim.list', array_merge(request()->query(), ['tab' => 'returns'])) }}">반품/교환 내역</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'refund' ? 'active font-weight-bold' : '' }}" href="{{ route('admin.order.claim.list', array_merge(request()->query(), ['tab' => 'refund'])) }}">환불/결제취소 내역</a>
            </li>
        </ul>

        @if(in_array($tab, ['all', 'returns']))
        <!-- 반품 내역 리스트 -->
        <div class="card mb-5">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">
                    <span class="text-primary"><i class="fas fa-box-open mr-1"></i> 반품/교환 내역</span>
                    <small class="ml-2 text-muted">총 {{ number_format($returns->total()) }} 건</small>
                </h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-outline-info" onclick="processClaims('return', 'ing')">선택건 처리중으로</button>
                    <button class="btn btn-sm btn-outline-success" onclick="processClaims('return', 'complete')">선택건 처리완료로</button>
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-bordered table-hover text-sm table-striped text-center mb-0">
                    <thead class="bg-gray-light">
                        <tr>
                            <th width="40"><input type="checkbox" class="chk-all-returns"></th>
                            <th width="150">접수일시 / 클레임번호</th>
                            <th width="120">주문번호</th>
                            <th>주문자 / 연락처</th>
                            <th width="70">유형</th>
                            <th width="80">신청수량</th>
                            <th width="150">처리상태</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $rt)
                            <tr>
                                <td class="align-middle">
                                    <input type="checkbox" class="chk-return" value="{{ $rt->return_code }}">
                                </td>
                                <td class="align-middle">
                                    <span class="text-muted d-block small">{{ $rt->regist_date }}</span>
                                    <strong class="text-primary">{{ $rt->return_code }}</strong>
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('admin.order.view', $rt->order_seq) }}" class="font-weight-bold" target="_blank">{{ $rt->order_seq }}</a>
                                </td>
                                <td class="align-middle text-left">
                                    @if($rt->order)
                                        {{ $rt->order->order_user_name }} <span class="text-muted">/ {{ $rt->order->order_cellphone }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-secondary">{{ strtoupper($rt->return_type ?? '반품') }}</span>
                                </td>
                                <td class="align-middle font-weight-bold">
                                    {{ number_format($rt->return_ea) }}개
                                </td>
                                <td class="align-middle">
                                    @if($rt->status == 'request')
                                        <span class="badge badge-warning">신청</span>
                                    @elseif($rt->status == 'ing')
                                        <span class="badge badge-info">처리중</span>
                                    @elseif($rt->status == 'complete')
                                        <span class="badge badge-success">처리완료</span>
                                    @elseif($rt->status == 'cancel')
                                        <span class="badge badge-dark">취소</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $rt->status }}</span>
                                    @endif
                                    @if($rt->status_date)
                                      <div class="small text-muted mt-1">{{ substr($rt->status_date, 0, 10) }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-4 text-muted">조회된 반품/교환 내역이 없습니다.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($returns->hasPages())
            <div class="card-footer clearfix">
                {{ $returns->links('pagination::bootstrap-4') }}
            </div>
            @endif
        </div>
        @endif


        @if(in_array($tab, ['all', 'refund']))
        <!-- 환불 내역 리스트 -->
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">
                    <span class="text-danger"><i class="fas fa-undo mr-1"></i> 환불/결제취소 내역</span>
                    <small class="ml-2 text-muted">총 {{ number_format($refunds->total()) }} 건</small>
                </h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-outline-info" onclick="processClaims('refund', 'ing')">선택건 처리중으로</button>
                    <button class="btn btn-sm btn-outline-success" onclick="processClaims('refund', 'complete')">선택건 처리완료로</button>
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-bordered table-hover text-sm table-striped text-center mb-0">
                    <thead class="bg-gray-light">
                        <tr>
                            <th width="40"><input type="checkbox" class="chk-all-refunds"></th>
                            <th width="150">접수일시 / 클레임번호</th>
                            <th width="120">주문번호</th>
                            <th>주문자 / 연락처</th>
                            <th width="100">결제수단</th>
                            <th width="100">환불 금액</th>
                            <th width="150">처리상태</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($refunds as $rf)
                            <tr>
                                <td class="align-middle">
                                    <input type="checkbox" class="chk-refund" value="{{ $rf->refund_code }}">
                                </td>
                                <td class="align-middle">
                                    <span class="text-muted d-block small">{{ $rf->regist_date }}</span>
                                    <strong class="text-danger">{{ $rf->refund_code }}</strong>
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('admin.order.view', $rf->order_seq) }}" class="font-weight-bold" target="_blank">{{ $rf->order_seq }}</a>
                                </td>
                                <td class="align-middle text-left">
                                    @if($rf->order)
                                        {{ $rf->order->order_user_name }} <span class="text-muted">/ {{ $rf->order->order_cellphone }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="align-middle">
                                     @if($rf->order)
                                        <span class="badge badge-light border">{{ strtoupper($rf->order->payment) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="align-middle text-danger font-weight-bold">
                                    {{ number_format($rf->refund_price) }}원
                                </td>
                                <td class="align-middle">
                                    @if($rf->status == 'request')
                                        <span class="badge badge-warning">신청</span>
                                    @elseif($rf->status == 'ing')
                                        <span class="badge badge-info">처리중</span>
                                    @elseif($rf->status == 'complete')
                                        <span class="badge badge-success">처리완료</span>
                                    @elseif($rf->status == 'cancel')
                                        <span class="badge badge-dark">취소</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $rf->status }}</span>
                                    @endif
                                     @if($rf->status_date)
                                      <div class="small text-muted mt-1">{{ substr($rf->status_date, 0, 10) }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-4 text-muted">조회된 환불 내역이 없습니다.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($refunds->hasPages())
            <div class="card-footer clearfix">
                {{ $refunds->links('pagination::bootstrap-4') }}
            </div>
            @endif
        </div>
        @endif

    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Check all returns
    $('.chk-all-returns').change(function() {
        $('.chk-return').prop('checked', $(this).prop('checked'));
    });
    // Check all refunds
    $('.chk-all-refunds').change(function() {
        $('.chk-refund').prop('checked', $(this).prop('checked'));
    });
});

function processClaims(type, status) {
    let checkedValues = [];
    $('.chk-' + type + ':checked').each(function() {
        checkedValues.push($(this).val());
    });

    if (checkedValues.length === 0) {
        alert("상태를 변경할 클레임 건을 선택해주세요.");
        return;
    }

    if (!confirm("선택한 건의 상태를 변경하시겠습니까?")) return;

    $.ajax({
        url: "{{ route('admin.order.claim.process') }}",
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            type: type,
            status: status,
            codes: checkedValues
        },
        success: function(response) {
            if(response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert(response.message || "오류가 발생했습니다.");
            }
        },
        error: function(xhr) {
            alert("통신 오류: " + xhr.status);
        }
    });
}
</script>
<style>
.custom-tabs .nav-link { color: #495057; background-color: #f4f6f9; border: 1px solid #dee2e6; margin-right: 2px; }
.custom-tabs .nav-link.active { color: #007bff; background-color: #fff; border-bottom-color: transparent; }
</style>
@endsection
