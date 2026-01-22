@extends('admin.layouts.admin')

@section('content')
<style>
    .metric-row {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .metric-row:hover {
        background-color: #f8f9fa;
    }
    .metric-row.focused {
        font-size: 1.2rem;
        font-weight: bold;
        background-color: #fff3cd !important; /* Yellow highlight */
    }
    .metric-row.focused td {
        padding: 1rem !important;
    }
    .metric-row.hidden-row {
        display: none;
    }
    #resetFocusBtn {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
    }
</style>

<div class="container-fluid">
    <button id="resetFocusBtn" class="btn btn-primary shadow-lg" onclick="resetFocus()">
        <i class="fas fa-undo"></i> 전체 보기
    </button>

    <div class="row mb-3">
        <div class="col-12">
            <h1 class="m-0 text-dark">대시보드</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">일별 매출 현황</h3>
                    <div class="card-tools">
                        <form action="{{ route('admin.dashboard') }}" method="GET" class="form-inline">
                            <div class="form-check mr-3">
                                <input type="checkbox" class="form-check-input" id="show30" name="show_30_days" value="1" {{ $show30Days ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label" for="show30">30일 보기</label>
                            </div>
                            <div class="form-check mr-3">
                                <input type="checkbox" class="form-check-input" id="compareYear" name="compare_year" value="1" {{ $compareYear ?? false ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label" for="compareYear">작년 대비</label>
                            </div>
                            <div class="input-group input-group-sm">
                                <input type="date" name="search_date" class="form-control" value="{{ $searchDate }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0">
                    <table class="table table-bordered text-nowrap text-center">
                        <thead>
                            <tr>
                                <th style="width: 150px; position:sticky; left:0; background-color: #fff; z-index: 100;">구분</th>
                                @foreach($data as $date => $metrics)
                                    <th>{{ \Carbon\Carbon::parse($date)->format('m/d (D)') }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $metricLabels = [
                                    'retail' => ['label' => 'ⓐ소매매출', 'color' => 'dark'],
                                    'wholesale' => ['label' => 'ⓑ도매매출', 'color' => 'dark'],
                                    'retail_wholesale_sum' => ['label' => 'ⓐ+ⓑ도소매매출', 'color' => 'dark', 'bg' => '#f6f8fc', 'bold' => true],
                                    'retail_ad_cost' => ['label' => '☆도토광고비', 'sub_label' => '☆/(ⓐ+ⓑ) 비율', 'color' => 'dark', 'is_ad' => true, 'ratio_key' => 'retail_ad_ratio'],
                                    'b2b_shipping' => ['label' => 'ⓒB2B 배송대행', 'color' => 'dark'],
                                    'b2b_affiliate' => ['label' => 'ⓓB2B 제휴매출', 'color' => 'dark'],
                                    'b2b_promo' => ['label' => 'ⓔB2B 판촉매출', 'color' => 'dark'],
                                    'startup' => ['label' => 'ⓕ창업사매출', 'color' => 'dark'],
                                    'doto_total' => ['label' => '(ⓐ...ⓕ) 매출합계', 'color' => 'dark', 'bg' => '#f6f8fc', 'bold' => true],
                                    'linked' => ['label' => '연동매출', 'color' => 'dark'],
                                    'open_market' => ['label' => 'ⓐ 오픈마켓', 'color' => 'dark'],
                                    'rocket' => ['label' => 'ⓑ 로켓배송', 'color' => 'dark'],
                                    'general_mall' => ['label' => 'ⓒ 종합몰&홈쇼핑', 'color' => 'dark'],
                                    'marketing_total' => ['label' => '(ⓐ+ⓑ+ⓒ) 마케팅매출', 'sub_label' => '[발주총액]', 'color' => 'dark', 'bg' => '#f6f8fc', 'bold' => true],
                                    'marketing_ad_cost' => ['label' => '★마케팅광고비', 'sub_label' => '광고/발주 비율', 'color' => 'dark', 'is_ad' => true, 'ratio_key' => 'marketing_ad_ratio'],
                                    'grand_total' => ['label' => '매출총계', 'sub_label' => '[작년 대비]', 'color' => 'dark', 'bg' => '#d5dee5', 'bold' => true],
                                ];
                            @endphp

                            @foreach($metricLabels as $key => $meta)
                                <tr class="metric-row" id="row-{{ $key }}" onclick="toggleFocus('{{ $key }}')" style="background-color: {{ $meta['bg'] ?? '#ffffff' }}; font-weight: {{ ($meta['bold'] ?? false) ? 'bold' : 'normal' }};">
                                    <td style="position:sticky; left:0; background-color: {{ $meta['bg'] ?? '#ffffff' }}; z-index: 100;">
                                        {{ $meta['label'] }}
                                        @if(isset($meta['sub_label']))
                                            <br><small>{{ $meta['sub_label'] }}</small>
                                        @endif
                                    </td>
                                    @foreach($data as $date => $metrics)
                                        <td>
                                            @if(isset($metrics[$key]))
                                                @php $item = $metrics[$key]; @endphp
                                                
                                                @if($item['amount'] != 0 || ($item['count'] ?? 0) != 0)
                                                    <div class="text-{{ $meta['color'] ?? 'dark' }}">
                                                        {{ number_format($item['amount']) }}
                                                    </div>
                                                    
                                                    @if(isset($item['count']) && $key !== 'retail_ad_cost' && $key !== 'marketing_ad_cost')
                                                        <div class="text-muted small">
                                                            (Cnt: {{ number_format($item['count']) }})
                                                        </div>
                                                    @endif

                                                    @if(isset($meta['is_ad']) && isset($meta['ratio_key']))
                                                        @php 
                                                            $ratioKey = $meta['ratio_key'];
                                                            $ratioVal = $metrics[$ratioKey]['amount'] ?? 0;
                                                        @endphp
                                                        <div class="small font-weight-bold text-danger">
                                                            {{ $ratioVal }} %
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="text-muted">-</div>
                                                @endif

                                                {{-- Year Comparison Logic --}}
                                                @if($compareYear ?? false)
                                                    @php $ratio = $item['ratio'] ?? 0; @endphp
                                                    @if($ratio != 0)
                                                        <div class="small font-weight-bold {{ $ratio >= 100 ? 'text-primary' : 'text-danger' }}">
                                                            [ {{ $ratio }} % ]
                                                        </div>
                                                    @endif
                                                @endif
                                            @else
                                                <div class="text-muted">-</div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                     <div class="callout callout-info">
                        <h5><i class="fas fa-info"></i> 참고:</h5>
                        원하는 항목을 클릭하면 해당 행만 확대해서 볼 수 있습니다. (전체 보기 버튼으로 복구)
                    </div>
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>

<script>
    function toggleFocus(key) {
        const rows = document.querySelectorAll('.metric-row');
        const targetRow = document.getElementById('row-' + key);
        const resetBtn = document.getElementById('resetFocusBtn');
        
        // If already focused, reset
        if (targetRow.classList.contains('focused')) {
            resetFocus();
            return;
        }

        // Hide all rows
        rows.forEach(row => {
            row.classList.add('hidden-row');
            row.classList.remove('focused');
        });

        // Show and focus target row
        targetRow.classList.remove('hidden-row');
        targetRow.classList.add('focused');
        
        // Show reset button
        resetBtn.style.display = 'block';
    }

    function resetFocus() {
        const rows = document.querySelectorAll('.metric-row');
        const resetBtn = document.getElementById('resetFocusBtn');

        rows.forEach(row => {
            row.classList.remove('hidden-row');
            row.classList.remove('focused');
        });

        resetBtn.style.display = 'none';
    }
</script>
@endsection