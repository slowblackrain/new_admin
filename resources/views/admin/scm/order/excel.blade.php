<meta http-equiv="Content-Type" content="application/vnd.ms-excel;charset=UTF-8">
<table border="1">
    <thead>
        <tr>
            <th>번호</th>
            <th>상태</th>
            <th>거래처</th>
            <th>발주코드</th>
            <th>상품코드</th>
            <th>이미지</th>
            <th>상품명</th>
            <th>관리자메모</th>
            <th>발주수량</th>
            <th>원가</th>
            <th>배송비</th>
            <th>합계</th>
            <th>발주시간</th>
            <th>입고예정일</th>
            <th>입고완료일</th>
            <th>배송시작일</th>
            <th>배송완료일</th>
        </tr>
    </thead>
    <tbody>
    @foreach($offers as $offer)
            <td>{{ $offer->sno }}</td>
            <td>{{ $offer->step_text }}</td>
            <td>{{ $offer->trader_name }}</td>
            <td style="mso-number-format:'\@'">{{ $offer->sno }}</td> <!-- Replaced sorder_code with sno -->
            <td style="mso-number-format:'\@'">{{ $offer->goods_code }}</td>
            <td>
                @if($offer->image_url)
                <img src="{{ url($offer->image_url) }}" width="50" height="50">
                @endif
            </td>
            <td>{{ $offer->goods_name }}</td>
            <td>{{ $offer->scm_memo ?? '' }}</td>
            <td>{{ $offer->ship_in_total }}</td>
            <td>{{ $offer->supply_price }}</td>
            <td>{{ $offer->ord_shipping }}</td>
            <td>{{ $offer->tot_price }}</td>
            
            <td>{{ $offer->regist_date }}</td>
            <td>{{ $offer->ordering_date }}</td>
            <td>{{ $offer->cn_date }}</td>
            <td>{{ $offer->shipment_date }}</td>
            <td>{{ $offer->end_date }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
