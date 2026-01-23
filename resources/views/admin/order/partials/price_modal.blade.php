
{{-- Price Modification Modal --}}
<div class="modal fade" id="priceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">주문 금액 수정</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="priceForm">
                    <input type="hidden" name="order_seq" value="{{ $order->order_seq }}">
                    <input type="hidden" name="change_type" value="price_info">

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">최종결제금액</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" name="settleprice" value="{{ $order->settleprice }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">배송비</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" name="shipping_cost" value="{{ $order->shipping_cost }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">사용 적립금</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" name="emoney" value="{{ $order->emoney }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">쿠폰 할인</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" name="coupon_sale" value="{{ $order->coupon_sale }}">
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label>변경 사유 (관리자 메모)</label>
                        <textarea class="form-control" name="admin_memo" rows="2" placeholder="변경 사유를 입력하세요"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                <button type="button" class="btn btn-primary" onclick="submitPriceUpdate()">저장하기</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openPriceModal() {
        $('#priceModal').modal('show');
    }

    function submitPriceUpdate() {
        if(!confirm('주문 금액 정보를 수정하시겠습니까?')) return;

        const formData = $('#priceForm').serialize();

        $.ajax({
            url: "{{ route('admin.order.update_price') }}",
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    alert('수정되었습니다.');
                    location.reload();
                } else {
                    alert('오류: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('서버 오류가 발생했습니다.');
            }
        });
    }
</script>
