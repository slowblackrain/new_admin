<table>
    <thead>
    <tr>
        <th>주문번호</th>
        <th>주문일시</th>
        <th>공급가</th>
        <th>부가세</th>
        <th>합계</th>
        <th>상태</th>
        <th>환불번호</th>
    </tr>
    </thead>
    <tbody>
    @foreach($list as $item)
        <tr>
            <td>{{ $item->order_seq }}</td>
            <td>{{ $item->order_date }}</td>
            <td>{{ $item->supply }}</td>
            <td>{{ $item->surtax }}</td>
            <td>{{ $item->price }}</td>
            <td>{{ $item->dstate_str }}</td>
            <td>{{ $item->refund_code }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
