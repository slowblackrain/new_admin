<div class="modal-header bg-light">
    <h5 class="modal-title font-weight-bold">발주 상세 정보 (NO. {{ $offer->sno }})</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <!-- Product Info -->
    <div class="card mb-3">
        <div class="card-body p-2 d-flex align-items-center">
            <img src="{{ $offer->image_url }}" width="60" height="60" class="border mr-3">
            <div>
                <h6 class="mb-1 font-weight-bold">{{ $offer->goods_name }}</h6>
                <div class="text-muted small">
                    코드: {{ $offer->goods_code }} | 
                    거래처: {{ $offer->trader_name }} ({{ $offer->trader_phone }}) |
                    상태: <span class="badge badge-info">{{ $offer->step_text }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Info Grid -->
    <h6 class="font-weight-bold mb-2">주문/입고 내역</h6>
    <table class="table table-bordered table-sm text-center" style="font-size:12px;">
        <thead class="bg-light">
            <tr>
                <th>구분</th>
                <th>주문(발주)</th>
                <th>입고예정</th>
                <th>입고완료</th>
                <th>선적</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="bg-light font-weight-bold">날짜</td>
                <td>{{ $offer->regist_date ? substr($offer->regist_date, 0, 10) : '-' }}</td>
                <td>{{ $offer->ordering_date ? substr($offer->ordering_date, 0, 10) : '-' }}</td>
                <td>{{ $offer->cn_date ? substr($offer->cn_date, 0, 10) : '-' }}</td>
                <td>{{ $offer->shipment_date ? substr($offer->shipment_date, 0, 10) : '-' }}</td>
            </tr>
            <tr>
                <td class="bg-light font-weight-bold">수량/금액</td>
                <td colspan="4" class="text-left pl-3">
                    <div>수량: {{ number_format($offer->ship_in_total) }} 개</div>
                    <div>공급가: {{ number_format($offer->supply_price) }} 원</div>
                    <div>배송비: {{ number_format($offer->ord_shipping) }} 원</div>
                    <div class="font-weight-bold text-primary">합계: {{ number_format($offer->tot_price) }} 원</div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Memo Area -->
    <form id="detailForm">
        <h6 class="font-weight-bold mb-2">SCM 관리 메모</h6>
        <textarea class="form-control" name="scm_memo" rows="5" placeholder="관리자 메모를 입력하세요 (자동저장되지 않습니다. 저장을 눌러주세요)">{{ $offer->scm_memo }}</textarea>
        <input type="hidden" name="sno" value="{{ $offer->sno }}">
    </form>
</div>
<div class="modal-footer bg-light">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
    <button type="button" class="btn btn-primary" onclick="saveDetail({{ $offer->sno }})">내용 저장</button>
</div>

<script>
function saveDetail(sno) {
    const memo = document.querySelector('textarea[name="scm_memo"]').value;
    
    // Using updateField API for Memo (need to support 'scm_memo' in Controller)
    fetch('{{ route("admin.scm_order.update_field") }}', {
         method: 'POST',
         headers: {
             'Content-Type': 'application/json',
             'X-CSRF-TOKEN': '{{ csrf_token() }}'
         },
         body: JSON.stringify({ sno: sno, field: 'scm_memo', value: memo })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert('저장되었습니다.');
        } else {
            alert('저장 실패: ' + data.error);
        }
    })
    .catch(err => alert('오류: ' + err));
}
</script>
