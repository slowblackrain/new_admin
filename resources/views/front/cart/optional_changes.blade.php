<div class="modal_optional_changes" id="modal_optional_changes" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); width:500px; background:#fff; border:1px solid #ccc; box-shadow:0 0 10px rgba(0,0,0,0.2); z-index:9999; padding:20px;">
    <div class="modal_header" style="border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0; font-size:16px;">선택한 상품의 주문내역 (옵션 변경)</h3>
        <button type="button" class="btn_close_modal" style="background:none; border:none; font-size:20px; cursor:pointer;">&times;</button>
    </div>
    <div class="modal_body">
        <form id="form_optional_changes">
            <input type="hidden" name="cart_seq" value="{{ $cartItem->cart_seq }}">
            
            <div style="margin-bottom:15px;">
                <strong>상품명:</strong> {{ $goods->goods_name }}
            </div>
            
            <div style="margin-bottom:15px;">
                <strong>현재 선택옵션:</strong><br>
                @if($cartItem->options->first())
                    @php $opt = $cartItem->options->first(); @endphp
                    {{ $opt->option1 }} {{ $opt->option2 }} {{ $opt->option3 }} {{ $opt->option4 }} {{ $opt->option5 }}
                @else
                    기본옵션
                @endif
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px;"><strong>변경할 옵션 선택:</strong></label>
                <select name="new_option_seq" id="new_option_seq" style="width:100%; padding:8px; border:1px solid #ddd;">
                    <option value="">옵션을 선택하세요</option>
                    @foreach($options as $opt)
                        <option value="{{ $opt->option_seq }}">
                            {{ $opt->option1 }} {{ $opt->option2 }} {{ $opt->option3 }} {{ $opt->option4 }} {{ $opt->option5 }} 
                            @if(isset($opt->price) && $opt->price > 0)(+{{ number_format($opt->price) }}원)@endif
                            @if(isset($opt->stock) && $opt->stock <= 0) [품절] @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    <div class="modal_footer" style="text-align:center; margin-top:20px;">
        <button type="button" id="btn_save_optional_changes" class="button bluebtn" style="padding:10px 20px; background:#2979ff; color:#fff; border:none; cursor:pointer;">변경하기</button>
        <button type="button" class="btn_close_modal button transparent" style="padding:10px 20px; background:#f7f8f9; border:1px solid #ddd; cursor:pointer; margin-left:10px;">취소</button>
    </div>
</div>

<div class="modal_dimmed" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9998;"></div>
