<div class="modal fade" id="replaceModal" tabindex="-1" role="dialog" aria-labelledby="replaceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="replaceModalLabel">상품 교환</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="replace_original_item_seq">
                
                {{-- Product Search --}}
                <div class="form-group">
                    <label>교환할 상품 검색</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="replace_search_keyword" placeholder="상품명 또는 상품코드를 입력하세요">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="searchReplaceGoods()">검색</button>
                        </div>
                    </div>
                </div>

                {{-- Search Results --}}
                <div id="replace_search_results" class="mb-3" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; display: none;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>선택</th>
                                <th>이미지</th>
                                <th>상품명</th>
                                <th>가격</th>
                            </tr>
                        </thead>
                        <tbody id="replace_search_tbody">
                            {{-- AJAX Results --}}
                        </tbody>
                    </table>
                </div>

                {{-- Selected Logic --}}
                <div id="replace_selected_area" style="display: none;">
                    <div class="alert alert-info">
                        <strong>선택된 상품:</strong> <span id="selected_goods_name"></span>
                        <input type="hidden" id="selected_new_goods_seq">
                    </div>

                    <div class="form-group">
                        <label>옵션 선택</label>
                        <select class="form-control" id="replace_new_option_seq">
                            <option value="">옵션을 선택하세요</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>교환 사유</label>
                        <select class="form-control" id="replace_change_code">
                            <option value="단순변심">단순변심</option>
                            <option value="오주문">오주문</option>
                            <option value="상품불량">상품불량</option>
                            <option value="오배송">오배송</option>
                            <option value="기타">기타</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                <button type="button" class="btn btn-primary" onclick="submitReplace()">교환 처리</button>
            </div>
        </div>
    </div>
</div>

<script>
    function searchReplaceGoods() {
        let keyword = $('#replace_search_keyword').val();
        if(keyword.length < 2) {
            alert('검색어를 2글자 이상 입력해주세요.');
            return;
        }

        // Mock Search using direct DB query route or create a temporary route?
        // Actually, let's use a simple direct query from OrderProcessController? No, better Admin/GoodsController.
        // For MVP, I'll add a simple search route relative to this feature or just use OrderProcessController.
        // Let's assume we use 'admin/order/search_goods' or similar. 
        // Wait, I haven't implemented search route yet. I'll need to do that. 
        // For now, I'll assume we add `searchGoods` to OrderProcessController.

        $.ajax({
            url: "{{ route('admin.order.search_goods') }}",
            data: { keyword: keyword },
            success: function(data) {
                let html = '';
                if(data.length === 0) {
                    html = '<tr><td colspan="4" class="text-center">검색 결과가 없습니다.</td></tr>';
                } else {
                    data.forEach(item => {
                        html += `
                            <tr>
                                <td><button class="btn btn-xs btn-primary" onclick="selectReplaceGoods('${item.goods_seq}', '${item.goods_name}', '${item.price}')">선택</button></td>
                                <td><img src="${item.image}" width="30"></td>
                                <td>${item.goods_name}</td>
                                <td>${item.price}</td>
                            </tr>
                        `;
                    });
                }
                $('#replace_search_tbody').html(html);
                $('#replace_search_results').show();
            }
        });
    }

    function selectReplaceGoods(goodsSeq, goodsName, price) {
        $('#selected_new_goods_seq').val(goodsSeq);
        $('#selected_goods_name').text(goodsName);
        $('#replace_selected_area').show();

        // Load Options
        $.ajax({
            url: "{{ route('admin.order.get_options') }}", 
            data: { goods_seq: goodsSeq },
            success: function(data) {
                let html = '<option value="">옵션을 선택하세요</option>';
                data.forEach(opt => {
                    html += `<option value="${opt.option_seq}">${opt.option1} (+${opt.price}원)</option>`;
                });
                $('#replace_new_option_seq').html(html);
            }
        });
    }

    function submitReplace() {
        if(!confirm('정말 교환 처리하시겠습니까? (재고가 즉시 반영됩니다)')) return;

        let data = {
            order_seq: '{{ $order->order_seq ?? "" }}', // View context
            original_item_seq: $('#replace_original_item_seq').val(),
            new_goods_seq: $('#selected_new_goods_seq').val(),
            new_option_seq: $('#replace_new_option_seq').val(),
            change_code: $('#replace_change_code').val()
        };

        if(!data.new_option_seq) {
            alert('옵션을 선택해주세요.');
            return;
        }

        $.ajax({
            url: "{{ route('admin.order.replace_item') }}",
            type: "POST",
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if(res.success) {
                    alert('교환 처리되었습니다.');
                    location.reload();
                } else {
                    alert('오류: ' + res.message);
                }
            },
            error: function(err) {
                alert('서버 오류 발생');
            }
        });
    }
</script>
