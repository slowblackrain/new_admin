@forelse($goodsList as $item)
    <li>
        <div class="right_quick_goods_box">
             <div class="right_quick_goods">
                <a href="{{ route('goods.view', ['no' => $item->goods_seq]) }}">
                    {{-- Use helper or direct path for image. Assuming legacy path structure or helper exists --}}
                    @php
                        $imgSrc = $item->image[0]->image ?? '/images/legacy/common/noimage.gif';
                        if (!str_starts_with($imgSrc, 'http') && !str_starts_with($imgSrc, '/')) {
                             $imgSrc = '/data/goods/'.$imgSrc;
                        }
                    @endphp
                    <img src="{{ $imgSrc }}" style="width: 40px; height: 60px;" />
                </a>
            </div>
            
            {{-- Delete Button --}}
            <img src="/images/legacy/common/btn_del_s.gif" class="right_quick_btn_delete" 
                 style="cursor:pointer; position:absolute; top:0; right:0; visibility:hidden;"
                 onclick="rightDeleteItem('right_item_recent', '{{ $item->goods_seq }}', $(this));" />

            {{-- Hover Detail --}}
            <div class="rightQuickitemDetail" style="visibility:hidden;">
                <a href="{{ route('goods.view', ['no' => $item->goods_seq]) }}">
                    <p class="right_item_title">{{ $item->goods_name }}</p>
                    <p class="right_item_price">{{ number_format($item->price) }}원</p>
                </a>
            </div>
        </div>
    </li>
@empty
    {{-- No items --}}
@endforelse
