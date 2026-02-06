@if(count($children) > 0)
    @foreach($children as $child)
    <div class="item_box" style="cursor:default;">
        <div class="img">
            <img src="{{ $child->goods->images->first()->image ?? '/images/no_img.gif' }}" width="50">
        </div>
        <div class="info" style="flex-grow:1;">
            <div class="scode">[{{ $child->goods->goods_scode }}]</div>
            <div class="name">
                {{ $child->goods->goods_name }} 
                <span style="color:red; font-weight:bold;">({{ $child->goods_ea }}개)</span>
            </div>
        </div>
        <div class="btn_area">
            <button type="button" class="btn small" onclick="del_goods({{ $child->set_seq }});">삭제</button>
        </div>
    </div>
    @endforeach
@else
    <div class="empty_msg">등록된 세부 상품이 없습니다.</div>
@endif
