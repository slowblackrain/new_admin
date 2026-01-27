@extends('layouts.front')

@section('content')
<div class="location_wrap hidden-mobile">
    <div class="location_cont">
        <em><a href="/" class="local_home">HOME</a> &gt; 마이페이지 &gt; 관심상품</em>
    </div>
</div>

<div class="content_wrap clearbox" style="padding-bottom: 100px;">
    <!-- Left Sidebar (Desktop Only) -->
    <div class="hidden-mobile">
        @include('front.mypage.sidebar')
    </div>

    <!-- Right Content -->
    <div id="mypage_content" class="mypage-content-responsive">
        <div class="cart_title_area">
            <h3>관심상품</h3>
        </div>

        <div class="cart_list_area">
            <table class="cart_table hidden-mobile">
                <colgroup>
                    <col width="50" />
                    <col width="100" />
                    <col width="*" />
                    <col width="120" />
                    <col width="150" />
                    <col width="100" />
                </colgroup>
                <thead>
                    <tr>
                        <th scope="col"><input type="checkbox" id="check_all" onclick="checkAll(this)"></th>
                        <th scope="col">이미지</th>
                        <th scope="col">상품정보</th>
                        <th scope="col">판매가</th>
                        <th scope="col">담은날짜</th>
                        <th scope="col">관리</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wishes as $wish)
                        @php
                            $goods = $wish->goods;
                            $imgSrc = '/images/no_image.gif';
                            if ($goods && $goods->images) {
                                $mainImage = $goods->images->where('image_type', 'list1')->first();
                                if ($mainImage) {
                                    $imgSrc = '/data/goods/' . $mainImage->image;
                                }
                            }
                        @endphp
                        <tr>
                            <td><input type="checkbox" name="wish_seq[]" value="{{ $wish->wish_seq }}"></td>
                            <td class="img_cell">
                                <a href="{{ route('goods.view', $wish->goods_seq) }}">
                                    <img src="{{ $imgSrc }}" alt="{{ $goods->goods_name ?? '' }}" width="60">
                                </a>
                            </td>
                            <td class="info_cell" style="text-align: left; padding-left: 10px;">
                                <a href="{{ route('goods.view', $wish->goods_seq) }}" style="font-weight: bold; color: #333;">
                                    {{ $goods->goods_name ?? '상품 정보 없음' }}
                                </a>
                            </td>
                            <td class="price_bold">
                                {{ number_format($goods->price ?? 0) }}원
                            </td>
                            <td>{{ substr($wish->regist_date, 0, 10) }}</td>
                            <td>
                                <!-- Single delete only for now -->
                                <form action="{{ route('mypage.wishlist.destroy', $wish->wish_seq) }}" method="POST" onsubmit="return confirm('삭제하시겠습니까?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn_s_white">삭제</button>
                                </form>
                                <button type="button" class="btn_s_black" onclick="addToCart({{ $wish->goods_seq }})" style="margin-top: 5px;">장바구니</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 50px 0; text-align: center;">관심상품 내역이 없습니다.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Mobile List View --}}
            <div class="hidden-desktop">
                 @forelse($wishes as $wish)
                    @php
                        $goods = $wish->goods;
                        $imgSrc = '/images/no_image.gif';
                        if ($goods && $goods->images) {
                            $mainImage = $goods->images->where('image_type', 'list1')->first();
                            if ($mainImage) {
                                $imgSrc = '/data/goods/' . $mainImage->image;
                            }
                        }
                    @endphp
                    <div style="border:1px solid #ddd; margin-bottom:10px; padding:10px; background:#fff; position: relative;">
                         <form action="{{ route('mypage.wishlist.destroy', $wish->wish_seq) }}" method="POST" onsubmit="return confirm('삭제하시겠습니까?');" style="position: absolute; top: 10px; right: 10px;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="border: none; background: none; color: #999; font-size: 20px;">&times;</button>
                        </form>

                         <div class="clearbox">
                             <div style="float:left; width: 60px; margin-right: 10px;">
                                <a href="{{ route('goods.view', $wish->goods_seq) }}">
                                    <img src="{{ $imgSrc }}" alt="{{ $goods->goods_name ?? '' }}" width="100%">
                                </a>
                             </div>
                             <div style="float:left; width: calc(100% - 80px);">
                                <a href="{{ route('goods.view', $wish->goods_seq) }}" style="display:block; font-weight: bold; color: #333; margin-bottom: 5px;">
                                    {{ $goods->goods_name ?? '상품 정보 없음' }}
                                </a>
                                <span style="font-weight:bold; color:#d00;">{{ number_format($goods->price ?? 0) }}원</span>
                                <div style="margin-top: 5px; font-size: 12px; color: #888;">담은 날짜: {{ substr($wish->regist_date, 0, 10) }}</div>
                             </div>
                         </div>
                         <div style="margin-top:10px; text-align:center;">
                             <button type="button" class="btn_base" onclick="addToCart({{ $wish->goods_seq }})" style="width: 100%;">장바구니 담기</button>
                         </div>
                    </div>
                @empty
                    <div style="padding: 50px 0; text-align: center; border:1px solid #ddd; background:#fff;">관심상품이 없습니다.</div>
                @endforelse
            </div>

            <div class="paging_area">
                {{ $wishes->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    function checkAll(source) {
        checkboxes = document.getElementsByName('wish_seq[]');
        for(var i=0, n=checkboxes.length;i<n;i++) {
            checkboxes[i].checked = source.checked;
        }
    }

    // Reuse existing add to cart logic or redirect
    function addToCart(goodsSeq) {
        location.href = '/goods/view/' + goodsSeq; // Direct to goods view for options usually
    }
</script>

<style>
    .btn_s_white { background: #fff; border: 1px solid #ccc; padding: 3px 8px; color: #666; font-size: 11px; cursor: pointer; }
    .btn_s_black { background: #333; border: 1px solid #333; padding: 3px 8px; color: #fff; font-size: 11px; cursor: pointer; }
</style>
@endsection
