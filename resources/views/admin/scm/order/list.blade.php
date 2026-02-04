@extends('admin.layouts.admin')

@section('content')
<style>
.search-form-table th { background-color: #f4f6f9; text-align: center; font-weight: bold; width: 120px; }
.search-form-table td { padding: 5px 10px; }
.list-table th { text-align: center; background-color: #f4f6f9; white-space: nowrap; font-size: 11px; }
.list-table td { vertical-align: middle; font-size: 11px; }
.nested-table { width: 100%; border-collapse: collapse; margin: 0; }
.nested-table td { border: 1px solid #ddd; padding: 2px; text-align: center; }
.step-row-100 { background-color: #f7f0b9; } /* Agency */
.step-row-1 { background-color: #FFC4F1; } /* Order */
.step-row-6 { background-color: #00cc00; } /* Baldae */
.step-row-11 { background-color: #EEEEEE; } /* Stocked */
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">발주 리스트 (SCM Doto)</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
            @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

            <!-- Search Area -->
            <div class="card">
                <div class="card-body">
                    <form method="get">
                        <table class="table table-bordered search-form-table mb-0">
                            <tr>
                                <th>검색어</th>
                                <td colspan="3">
                                    <input type="text" name="keyword" class="form-control form-control-sm d-inline-block" style="width: 200px;" value="{{ request('keyword') }}" placeholder="상품명/코드">
                                </td>
                                <th>거래처</th>
                                <td>
                                    <select name="trader_seq" class="form-control form-control-sm d-inline-block" style="width: 150px;">
                                        <option value="">전체</option>
                                        @foreach($traders as $t)
                                        <option value="{{ $t->trader_seq }}" {{ request('trader_seq') == $t->trader_seq ? 'selected' : '' }}>
                                            {{ $t->trader_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>상태</th>
                                <td colspan="5">
                                    <!-- Legacy Radio Buttons -->
                                    <div class="d-flex flex-wrap">
                                        <div class="mr-3"><input type="radio" name="sch_step" value="all" id="st_all" {{ request('sch_step') == 'all' || !request('sch_step') ? 'checked' : '' }}> <label for="st_all">전체</label></div>
                                        <div class="mr-3"><input type="radio" name="sch_step" value="100" id="st_100" {{ request('sch_step') == '100' ? 'checked' : '' }}> <label for="st_100">대행</label></div>
                                        <div class="mr-3"><input type="radio" name="sch_step" value="1" id="st_1" {{ request('sch_step') == '1' ? 'checked' : '' }}> <label for="st_1">주문</label></div>
                                        <div class="mr-3"><input type="radio" name="sch_step" value="6" id="st_6" {{ request('sch_step') == '6' ? 'checked' : '' }}> <label for="st_6">발대</label></div>
                                        <div class="mr-3"><input type="radio" name="sch_step" value="2" id="st_2" {{ request('sch_step') == '2' ? 'checked' : '' }}> <label for="st_2">시장주문</label></div>
                                        <div class="mr-3"><input type="radio" name="sch_step" value="4" id="st_4" {{ request('sch_step') == '4' ? 'checked' : '' }}> <label for="st_4">중입예</label></div>
                                        <div class="mr-3"><input type="radio" name="sch_step" value="8" id="st_8" {{ request('sch_step') == '8' ? 'checked' : '' }}> <label for="st_8">선적</label></div>
                                        <div class="mr-3"><input type="radio" name="sch_step" value="11" id="st_11" {{ request('sch_step') == '11' ? 'checked' : '' }}> <label for="st_11">한입고</label></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>날짜검색</th>
                                <td colspan="5">
                                    <select name="sc_date_gubun" class="form-control form-control-sm d-inline-block" style="width: 100px;">
                                        <option value="regist_date" {{ request('sc_date_gubun') == 'regist_date' ? 'selected' : '' }}>발주일</option>
                                        <option value="ordering_date" {{ request('sc_date_gubun') == 'ordering_date' ? 'selected' : '' }}>입예일</option>
                                        <option value="cn_date" {{ request('sc_date_gubun') == 'cn_date' ? 'selected' : '' }}>입고일</option>
                                        <option value="shipment_date" {{ request('sc_date_gubun') == 'shipment_date' ? 'selected' : '' }}>선적일</option>
                                    </select>
                                    <input type="date" name="sc_sdate" class="form-control form-control-sm d-inline-block" style="width: 130px;" value="{{ request('sc_sdate') }}">
                                    ~
                                    <input type="date" name="sc_edate" class="form-control form-control-sm d-inline-block" style="width: 130px;" value="{{ request('sc_edate') }}">
                                    <button type="submit" class="btn btn-sm btn-primary ml-2">검색</button>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>

            <!-- List Area -->
            <form action="{{ route('admin.scm_order.update_status') }}" method="POST" id="listForm">
                @csrf
                <div class="mb-2 d-flex align-items-center">
                    <span class="mr-2 font-weight-bold">선택 변경:</span>
                    <button type="submit" name="action" value="status_1" class="btn btn-sm btn-primary mr-1" onclick="return confirm('선택한 상품을 발주(주문) 상태로 변경하시겠습니까?')">발주처리(1)</button>
                    <button type="submit" name="action" value="status_11" class="btn btn-sm btn-info mr-1" onclick="return confirm('선택한 상품을 입고완료 상태로 변경하시겠습니까?')">입고완료(11)</button>
                    <button type="submit" name="action" value="soldout" class="btn btn-sm btn-danger mr-1" onclick="return confirm('선택한 상품을 단종 처리하시겠습니까? (상품 상태 Solout 변경)')">단종처리</button>
                    <div class="vr mx-2"></div>
                    <button type="submit" name="action" value="cancel" class="btn btn-sm btn-dark" onclick="return confirm('선택한 항목을 삭제(취소) 하시겠습니까?\nAgency 상품은 자동 환불됩니다.')">삭제/취소</button>
                    <div class="vr mx-2"></div>
                    <button type="button" class="btn btn-sm btn-success" onclick="downloadExcel()">
                        <i class="fas fa-file-excel"></i> 엑셀 다운로드
                    </button>
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-bordered list-table table-sm" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th width="30"><input type="checkbox" id="chkAll"></th>
                                    <th width="40">No</th>
                                    <th width="60">이미지</th>
                                    <th>상품정보</th>
                                    <th width="100">거래처</th>
                                    <th width="200">주문정보</th>
                                    <th width="80">수량</th>
                                    <th width="80">매입가</th>
                                    <th width="80">합계</th>
                                    <th width="60">상태</th>
                                    <th width="80">관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($offers as $offer)
                                <tr style="background-color: {{ $offer->bgcolor ?? '#ffffff' }};">
                                    <td class="text-center">
                                        <input type="checkbox" name="chk[]" value="{{ $offer->sno }}">
                                    </td>
                                    <td class="text-center">{{ $offer->sno }}</td>
                                    <td class="text-center">
                                        <img src="{{ $offer->image_url }}" width="40" height="40" style="border:1px solid #ddd;">
                                    </td>
                                    <td>
                                        <div><a href="#" class="font-weight-bold">{{ $offer->goods_name }}</a></div>
                                        <div class="text-muted">{{ $offer->goods_code }}</div>
                                        @if($offer->is_agency) <span class="badge badge-warning">Agency</span> @endif
                                    </td>
                                    <td>{{ $offer->trader_name }}<br><small>{{ $offer->trader_phone }}</small></td>
                                    <td class="p-0">
                                        <!-- Nested Table for Order Info (Dates) -->
                                        <table class="nested-table" style="width:100%; border:none; margin:0;">
                                            <tr>
                                                <td class="bg-light" style="width:30px; border-bottom:1px solid #eee; padding:2px;">주문</td>
                                                <td style="border-bottom:1px solid #eee; padding:2px;">
                                                    <input type="date" class="form-control form-control-sm p-0 border-0 bg-transparent" style="height:20px; font-size:11px; width:100%;" 
                                                        value="{{ $offer->regist_date ? substr($offer->regist_date, 0, 10) : '' }}" 
                                                        onchange="update_order_info(this, 'regist_date', {{ $offer->sno }})">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light" style="width:30px; border-bottom:1px solid #eee; padding:2px;">입예</td>
                                                <td style="border-bottom:1px solid #eee; padding:2px;">
                                                    <input type="date" class="form-control form-control-sm p-0 border-0 bg-transparent" style="height:20px; font-size:11px; width:100%;" 
                                                        value="{{ $offer->ordering_date ? substr($offer->ordering_date, 0, 10) : '' }}" 
                                                        onchange="update_order_info(this, 'ordering_date', {{ $offer->sno }})">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light" style="border-bottom:1px solid #eee; padding:2px;">입고</td>
                                                <td style="border-bottom:1px solid #eee; padding:2px;">
                                                    <input type="date" class="form-control form-control-sm p-0 border-0 bg-transparent" style="height:20px; font-size:11px; width:100%;" 
                                                        value="{{ $offer->cn_date ? substr($offer->cn_date, 0, 10) : '' }}" 
                                                        onchange="update_order_info(this, 'cn_date', {{ $offer->sno }})">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="bg-light" style="padding:2px;">선적</td>
                                                <td style="padding:2px;">
                                                    <input type="date" class="form-control form-control-sm p-0 border-0 bg-transparent" style="height:20px; font-size:11px; width:100%;" 
                                                        value="{{ $offer->shipment_date ? substr($offer->shipment_date, 0, 10) : '' }}" 
                                                        onchange="update_order_info(this, 'shipment_date', {{ $offer->sno }})">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td class="text-right p-1">
                                        <input type="text" class="form-control form-control-sm text-right p-1 border-0 bg-transparent" style="height:24px; font-size:12px;" 
                                            value="{{ $offer->ship_in_total }}" 
                                            onchange="update_order_info(this, 'ship_in_total', {{ $offer->sno }})">
                                    </td>
                                    <td class="text-right p-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <small class="mr-1" style="width:30px;">원가</small> 
                                            <input type="text" class="form-control form-control-sm text-right p-1 border-0 bg-transparent" style="height:20px; font-size:11px;" 
                                            value="{{ $offer->supply_price }}" 
                                            onchange="update_order_info(this, 'supply_price', {{ $offer->sno }})">
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <small class="mr-1" style="width:30px;">운송</small> 
                                            <input type="text" class="form-control form-control-sm text-right p-1 border-0 bg-transparent" style="height:20px; font-size:11px;" 
                                            value="{{ $offer->ord_shipping }}" 
                                            onchange="update_order_info(this, 'ord_shipping', {{ $offer->sno }})">
                                        </div>
                                    </td>
                                    <td class="text-right font-weight-bold">
                                         <input type="text" class="form-control form-control-sm text-right p-1 border-0 bg-transparent font-weight-bold" style="height:24px;" 
                                            value="{{ number_format($offer->tot_price ?? 0) }}" readonly>
                                    </td>
                                    <td class="text-center">{{ $offer->step_text }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-default border" onclick="showDetail({{ $offer->sno }})">상세</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5">검색 결과가 없습니다.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mt-3 d-flex justify-content-center">
                    {{ $offers->appends(request()->except('page'))->links() }}
                </div>
            </form>
        </div>
    </section>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<script>
document.getElementById('chkAll').addEventListener('change', function() {
    let checked = this.checked;
    document.querySelectorAll('input[name="chk[]"]').forEach(function(el) {
        el.checked = checked;
    });
});


function downloadExcel() {
    const urlParams = new URLSearchParams(window.location.search);
    window.location.href = "{{ route('admin.scm_order.excel') }}?" + urlParams.toString();
}

function update_order_info(el, field, sno) {
    el.style.backgroundColor = '#ffffcc'; // Pending color
    fetch('{{ route("admin.scm_order.update_field") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ sno: sno, field: field, value: el.value })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            el.style.backgroundColor = '#e6fffa'; // Success green
            setTimeout(() => el.style.backgroundColor = '', 1000);
        } else {
            alert('업데이트 실패: ' + (data.error || 'Unknown error'));
            el.style.backgroundColor = '#ffe6e6'; // Error red
        }
    })
    .catch(err => {
        alert('오류 발생: ' + err);
        el.style.backgroundColor = '#ffe6e6';
    });
}

function showDetail(sno) {
    $('#detailModal .modal-content').html('<div class="p-4 text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
    $('#detailModal').modal('show');
    
    fetch('{{ route("admin.scm_order.detail") }}?sno=' + sno)
        .then(response => response.text())
        .then(html => {
            $('#detailModal .modal-content').html(html);
        })
        .catch(error => {
            console.error('Error:', error);
            $('#detailModal .modal-content').html('<div class="alert alert-danger m-3">상세 정보를 불러오는데 실패했습니다.</div>');
        });
}
</script>
@endsection


