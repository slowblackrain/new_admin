<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>재고 수불부 인쇄</title>
    <style>
        body { font-family: "Malgun Gothic", Dotum, sans-serif; font-size: 11px; }
        .print-container { width: 100%; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; border: 1px solid #000; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: center; font-size: 11px; }
        th { background-color: #eee; font-weight: bold; }
        .subtit { background-color: #FDEADA; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .unprints { text-align: right; margin-bottom: 10px; }
        
        @media print {
            .unprints { display: none; }
        }
    </style>
    <script>
        function thisPagePrint() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="print-container">
        <div class="unprints">
            <button onclick="thisPagePrint()" style="padding: 5px 20px; cursor: pointer;">인쇄</button>
        </div>

        <h2 style="text-align:center;">재고 수불부</h2>
        <div style="text-align:right; margin-bottom:5px;">
            기간: {{ $filters['start_date'] }} ~ {{ $filters['end_date'] }}
        </div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2">일자</th>
                    <th rowspan="2">상품명 / 코드</th>
                    <th colspan="3" class="subtit">기초 재고</th>
                    <th colspan="3" class="subtit">입고</th>
                    <th colspan="3" class="subtit">출고</th>
                    <th colspan="3" class="subtit">기말 재고</th>
                </tr>
                <tr>
                    <!-- Pre -->
                    <th>수량</th>
                    <th>단가</th>
                    <th>금액</th>
                    <!-- In -->
                    <th>수량</th>
                    <th>단가</th>
                    <th>금액</th>
                    <!-- Out -->
                    <th>수량</th>
                    <th>단가</th>
                    <th>금액</th>
                    <!-- Cur -->
                    <th>수량</th>
                    <th>단가</th>
                    <th>금액</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>{{ $log->ldg_date }}</td>
                    <td class="text-left">
                        {{ $log->goods_name }}<br>
                        <span style="color:#888;">{{ $log->goods_code }}</span>
                    </td>
                    
                    <!-- Pre -->
                    <td class="text-right">{{ number_format($log->pre_ea) }}</td>
                    <td class="text-right">{{ number_format($log->pre_supply_price) }}</td>
                    <td class="text-right">{{ number_format($log->calc_pre_price) }}</td>

                    <!-- In -->
                    <td class="text-right">{{ number_format($log->in_ea) }}</td>
                    <td class="text-right">
                        @if($log->in_ea > 0)
                            {{ number_format($log->calc_in_price / $log->in_ea) }}
                        @else
                            0
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($log->calc_in_price) }}</td>

                    <!-- Out -->
                    <td class="text-right">{{ number_format($log->out_ea) }}</td>
                    <td class="text-right">{{ number_format($log->calc_out_unit_price) }}</td>
                    <td class="text-right">{{ number_format($log->calc_out_price) }}</td>

                    <!-- Cur -->
                    <td class="text-right">{{ number_format($log->cur_ea) }}</td>
                    <td class="text-right">{{ number_format($log->calc_cur_unit_price) }}</td>
                    <td class="text-right">{{ number_format($log->calc_cur_price) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="14" style="padding: 20px;">조회된 데이터가 없습니다.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
