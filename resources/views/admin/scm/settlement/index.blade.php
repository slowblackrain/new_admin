@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">입점사 정산 관리 (ATS)</h3>
                <div class="card-tools">
                    <form action="{{ route('admin.scm_settlement.index') }}" method="GET" class="form-inline">
                        <select name="year" class="form-control mr-1">
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}년</option>
                            @endfor
                        </select>
                        <select name="month" class="form-control mr-2">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>{{ $m }}월</option>
                            @endfor
                        </select>
                        <input type="text" name="keyword" class="form-control mr-2" placeholder="입점사명/ID" value="{{ request('keyword') }}">
                        <button type="submit" class="btn btn-primary">검색</button>
                    </form>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>정산월</th>
                            <th>상태</th>
                            <th>입점사</th>
                            <th class="text-right">총 결제금액</th>
                            <th class="text-right">총 공급가</th>
                            <th class="text-right">예상 마진</th>
                            <th class="text-center">판매건수</th>
                            <th class="text-center">관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settlements as $item)
                        <tr>
                            <td>{{ $item->acc_date }}</td>
                            <td>
                                @if($item->acc_status == 'complete')
                                    <span class="badge badge-success">정산완료</span>
                                @else
                                    <span class="badge badge-secondary">미정산</span>
                                @endif
                            </td>
                            <td>{{ $item->user_name }}<br><small>({{ $item->userid }})</small></td>
                            <td class="text-right">{{ number_format($item->sell_price) }}원</td>
                            <td class="text-right">{{ number_format($item->offer_price) }}원</td>
                            <td class="text-right text-primary font-weight-bold">{{ number_format($item->margin) }}원</td>
                            <td class="text-center">{{ number_format($item->sell_ea) }}건</td>
                            <td class="text-center">
                                <button class="btn btn-xs btn-primary btn-edit" 
                                        data-seq="{{ $item->seq }}"
                                        data-offer="{{ $item->offer_price }}"
                                        data-margin="{{ $item->margin }}"
                                        @if($item->acc_status == 'complete') disabled @endif>
                                    관리
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">해당 월의 정산 데이터가 없습니다.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $settlements->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">정산 데이터 조정</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editSeq">
                    <div class="form-group">
                        <label for="editOfferPrice">총 공급가</label>
                        <input type="number" class="form-control" id="editOfferPrice" required>
                    </div>
                    <div class="form-group">
                        <label for="editMargin">예상 마진</label>
                        <input type="number" class="form-control" id="editMargin" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" id="btnSave">저장</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = $('#editModal');
    
    // Open Modal
    $('.btn-edit').on('click', function() {
        const seq = $(this).data('seq');
        const offer = $(this).data('offer');
        const margin = $(this).data('margin');
        
        $('#editSeq').val(seq);
        $('#editOfferPrice').val(offer);
        $('#editMargin').val(margin);
        
        editModal.modal('show');
    });
    
    // Save Changes
    $('#btnSave').on('click', function() {
        const seq = $('#editSeq').val();
        const offerPrice = $('#editOfferPrice').val();
        const margin = $('#editMargin').val();
        
        if(!seq) return;
        
        $.ajax({
            url: "{{ url('admin/scm_settlement') }}/" + seq,
            method: 'PUT',
            data: {
                _token: "{{ csrf_token() }}",
                offer_price: offerPrice,
                margin: margin
            },
            success: function(response) {
                alert('수정되었습니다.');
                window.location.reload();
            },
            error: function(xhr) {
                alert('오류가 발생했습니다: ' + (xhr.responseJSON.message || 'Unknown Error'));
            }
        });
    });
});
</script>
@endsection
