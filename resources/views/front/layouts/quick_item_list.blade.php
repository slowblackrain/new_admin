@forelse($goodsList as $item)
    <li>
        <div class="right_quick_goods_box">
             <div class="right_quick_goods">
                <a href="{{ route('goods.view', ['no' => $item->goods_seq]) }}">
                    {{-- Parity Image Logic: Check thumbScroll (cut=1) > thumbScroll > list1 (cut=1) > list1 > first --}}
                    @php
                        $imgSrc = '/images/legacy/common/noimage.gif';
                        
                        if ($item->images && $item->images->isNotEmpty()) {
                            // Prioritize cut_number = 1
                            $imgObj = $item->images->where('image_type', 'thumbScroll')->where('cut_number', 1)->first()
                                   ?? $item->images->where('image_type', 'thumbScroll')->first()
                                   ?? $item->images->where('image_type', 'list1')->where('cut_number', 1)->first()
                                   ?? $item->images->where('image_type', 'list1')->first()
                                   ?? $item->images->first();

                            if ($imgObj && !empty($imgObj->image)) {
                                $imgSrc = $imgObj->image;
                            }
                        }
                        
                        // Prefix path check
                        if ($imgSrc !== '/images/legacy/common/noimage.gif' && !str_starts_with($imgSrc, 'http') && !str_starts_with($imgSrc, '/')) {
                             $imgSrc = '/data/goods/'.$imgSrc;
                        }
                    @endphp
                    <img src="{{ $imgSrc }}" 
                         onerror="this.src='/images/legacy/common/noimage.gif'" />
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
