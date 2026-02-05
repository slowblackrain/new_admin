@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <h2 class="h3 mb-4 page-title">상품 관리 (SCM Goods)</h2>

        <div class="card shadow mb-4">
            <div class="card-header">
                <form action="{{ route('admin.scm_manage.goods') }}" method="GET">
                    <div class="form-group row">
                        <label class="col-sm-1 col-form-label">검색어</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" name="keyword" value="{{ $filters['keyword'] }}" placeholder="상품명, 상품코드, 상품번호">
                        </div>
                        <label class="col-sm-1 col-form-label">창고</label>
                        <div class="col-sm-5">
                            <select class="form-control" name="wh_seq" id="wh_seq">
                                <option value="">전체 창고 (Total Stock)</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->wh_seq }}" {{ ($filters['wh_seq'] == $wh->wh_seq) ? 'selected' : '' }}>
                                        {{ $wh->wh_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-sm-1 col-form-label">거래처</label>
                        <div class="col-sm-5 form-inline">
                            <select class="form-control mr-2" name="trader_group">
                                <option value="">전체 그룹</option>
                                @foreach($traderGroups as $tg)
                                    <option value="{{ $tg->trader_group }}">{{ $tg->trader_group }}</option>
                                @endforeach
                            </select>
                            <select class="form-control" name="trader_seq">
                                <option value="">전체 거래처</option>
                                @foreach($traders as $trader)
                                    <option value="{{ $trader->trader_seq }}">{{ $trader->trader_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="col-sm-1 col-form-label">분류</label>
                        <div class="col-sm-5">
                            <select class="form-control" name="category_code">
                                <option value="">1차 분류 선택</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->category_code }}">{{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-10 offset-sm-1">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="warning_only" name="warning_only" value="1" {{ $filters['warning_only'] ? 'checked' : '' }}>
                                <label class="form-check-label text-danger font-weight-bold" for="warning_only">
                                    재고부족 경고 (Warning Only)
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-1">
                            <button type="submit" class="btn btn-primary btn-block">검색</button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="form-inline">
                            <select class="form-control form-control-sm mr-2" id="excel_form">
                                <option value="supply">자동발주정보</option>
                                <option value="shop">쇼핑몰별안전재고</option>
                                <option value="stock">창고별 재고</option>
                            </select>
                            <button type="button" class="btn btn-sm btn-outline-success mr-1" onclick="excel_download('select')">선택 다운로드</button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="excel_download('list')">검색 다운로드</button>
                        </div>
                    </div>
                    <div class="col-6 text-right">
                        <button type="button" class="btn btn-sm btn-dark" onclick="openAddAutoOrderGoodsPopup()">자동발주상품 등록</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="location.href='{{ route('admin.goods.excel') }}'">일괄 등록/수정</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center table-sm small" style="font-size: 12px;">
                        <!-- Table Content -->
                        <thead class="thead-light">
                            <!-- ... -->

                            <tr>
                                <th rowspan="2" class="align-middle"><input type="checkbox" id="chkAll" onclick="checkAll(this)"></th>
                                <th rowspan="2" class="align-middle">상품번호<br>(Seq)</th>
                                <th rowspan="2" class="align-middle">옵션번호<br>(Opt Seq)</th>
                                <th rowspan="2" class="align-middle">상품코드<br>(Code)</th>
                                <th rowspan="2" class="align-middle info-col" style="min-width: 250px;">상품 (Goods)</th>
                                <th rowspan="2" class="align-middle">옵션 (Option)</th>
                                <th colspan="3" class="align-middle border-bottom-0">자동발주 정보 (Auto Order)</th>
                                <th colspan="3" class="align-middle border-bottom-0">
                                    @if($filters['wh_seq'])
                                        {{ $warehouses->where('wh_seq', $filters['wh_seq'])->first()->wh_name ?? '선택창고' }}
                                    @else
                                        전체창고 (Total WH)
                                    @endif
                                </th>
                                <th colspan="3" class="align-middle border-bottom-0">판매 정보 (Sales Info)</th>
                            </tr>
                            <tr>
                                <th class="align-middle bg-white">주거래처<br>(Trader)</th>
                                <th class="align-middle bg-white">발주가액<br>(Supply Price)</th>
                                <th class="align-middle bg-white">부가세<br>(Tax)</th>
                                
                                <th class="align-middle bg-white">매입가액<br>(In Price)</th>
                                <th class="align-middle bg-white">로케이션<br>(Loc)</th>
                                <th class="align-middle bg-white">재고(불량)<br>(Stock)</th>
                                
                                <th class="align-middle bg-white">안전재고<br>(Safe)</th>
                                <th class="align-middle bg-white">정가<br>(Consumer)</th>
                                <th class="align-middle bg-white">판매가격<br>(Price)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($goods as $item)
                                @php
                                    $isWarning = false;
                                    if ($filters['wh_seq']) {
                                        if ($item->total_stock < $item->safe_stock) $isWarning = true; // Logic check: legacy uses location_stock if wh selected, here total_stock is aliased to wh_stock in service if wh selected? No, in service: `total_stock` is always `fm_goods_supply.stock`, and `wh_stock` is added if wh selected.
                                        // Wait, my service update returns `total_stock` from `goods_supply`.
                                        // If WH selected, `wh_stock` is the specific stock.
                                        // Legacy: if WH selected, use `location_stock` (which is WH stock).
                                        // I need to use `$item->wh_stock` if exists.
                                        $currentStock = $item->wh_stock ?? $item->total_stock;
                                    } else {
                                        $currentStock = $item->total_stock;
                                    }
                                    if ($currentStock < $item->safe_stock) $isWarning = true;
                                @endphp
                                <tr class="{{ $isWarning ? 'table-danger' : '' }}">
                                    <td class="align-middle"><input type="checkbox" name="chk[]" value="{{ $item->goods_seq }}"></td>
                                    <td class="align-middle">{{ $item->goods_seq }}</td>
                                    <td class="align-middle">{{ $item->option_seq ?? '-' }}</td>
                                    <td class="align-middle">{{ $item->goods_code }}</td>
                                    <td class="text-left align-middle" onclick="window.open('{{ route('admin.goods.regist', ['goods_seq' => $item->goods_seq]) }}', '_blank')" style="cursor:pointer;">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $item->goods_image ? asset($item->goods_image) : asset('images/noimage.gif') }}" width="40" height="40" class="mr-2 border rounded">
                                            <div>
                                                <div class="font-weight-bold text-primary">{{ $item->goods_name }}</div>
                                                <div class="small text-muted">{{ $item->scm_category ?? '분류없음' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle text-left">{{ $item->option_name ?? '기본옵션' }}</td>
                                    
                                    <!-- Auto Order Info -->
                                    <td class="align-middle">
                                        @if($item->trader_seq)
                                            <a href="#" onclick="window.open('{{ route('admin.scm.trader_regist', ['trader_seq' => $item->trader_seq]) }}', 'trader', 'width=800,height=600')">{{ $item->trader_name }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-right align-middle">
                                        @if($item->auto_type == 'Y')
                                            {{ number_format($item->supply_price) }}%
                                        @else
                                            @if($item->supply_price_type && $item->supply_price_type != 'KRW')
                                                <span data-toggle="tooltip" title="환율: {{ $item->supply_price_type }}">
                                                    {{ number_format($item->supply_price, 2) }} {{ $item->supply_price_type }}
                                                </span>
                                            @else
                                                {{ number_format($item->supply_price) }}
                                            @endif
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        {{ $item->use_supply_tax == 'Y' ? '있음' : '없음' }}
                                    </td>

                                    <!-- Warehouse / Stock Info -->
                                    <td class="text-right align-middle">
                                        @if($filters['wh_seq'])
                                            {{-- Location Supply Price (Usually Avg Price or similar, legacy shows location_supply_price) --}}
                                            {{-- I don't have location_supply_price in query yet, assumed supply_price for now --}}
                                            -
                                        @else
                                            
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($filters['wh_seq'])
                                            {{-- Location Code --}}
                                            -
                                        @endif
                                    </td>
                                    <td class="text-right align-middle {{ $isWarning ? 'text-danger font-weight-bold' : '' }}" 
                                        onclick="openStockDetail('{{ $item->goods_seq }}')" style="cursor:pointer;">
                                        @if($filters['wh_seq'])
                                            {{ number_format($item->wh_stock ?? 0) }}
                                        @else
                                            {{ number_format($item->total_stock) }}
                                        @endif
                                        {{-- Bad stock not yet in query --}}
                                        (0)
                                    </td>

                                    <!-- Sales Info -->
                                    <td class="text-right align-middle">{{ number_format($item->safe_stock) }}</td>
                                    <td class="text-right align-middle">{{ number_format($item->consumer_price) }}</td>
                                    <td class="text-right align-middle">{{ number_format($item->price) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="py-4">데이터가 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $goods->appends($filters)->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auto Order Registration Modal -->
<div class="modal fade" id="autoOrderModal" tabindex="-1" role="dialog" aria-labelledby="autoOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="autoOrderModalLabel">자동발주상품 등록</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.scm_manage.process.auto_order') }}" method="POST" id="autoOrderForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        선택된 상품을 자동발주상품에 등록하시겠습니까?
                    </div>
                    
                    <input type="hidden" name="goods_seq_list" id="modal_goods_seq_list">

                    <div class="form-group">
                        <div class="custom-control custom-radio">
                            <input type="radio" id="addTypeDirect" name="add_ea_type" value="direct" class="custom-control-input" checked>
                            <label class="custom-control-label" for="addTypeDirect">
                                직접 입력: 수량 <input type="number" name="direct_ea" value="1" style="width: 80px; display: inline-block;" class="form-control form-control-sm"> 개 (1이상 입력)
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-radio">
                            <input type="radio" id="addTypeAuto" name="add_ea_type" value="auto" class="custom-control-input">
                            <label class="custom-control-label" for="addTypeAuto">
                                자동 계산: 
                                <select name="store_seq" class="form-control form-control-sm d-inline-block" style="width: 150px;">
                                    @php $stores = \App\Models\Scm\ScmStore::all(); @endphp
                                    @foreach($stores as $store)
                                        <option value="{{ $store->store_seq }}">{{ $store->store_name }}</option>
                                    @endforeach
                                </select> 안전재고 - 
                                <select name="warehouse_seq" class="form-control form-control-sm d-inline-block" style="width: 150px;">
                                    <option value="">전체창고</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->wh_seq }}">{{ $wh->wh_name }}</option>
                                    @endforeach
                                </select> 재고 = 발주수량
                            </label>
                        </div>
                        <small class="form-text text-muted ml-4">
                            * 계산된 수량이 1 이상인 경우에만 등록됩니다.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">등록하기</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
    function checkAll(ele) {
        var checkboxes = document.getElementsByName('chk[]');
        if (ele.checked) {
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type == 'checkbox') {
                    checkboxes[i].checked = true;
                }
            }
        } else {
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type == 'checkbox') {
                    checkboxes[i].checked = false;
                }
            }
        }
    }

    function openStockDetail(goodsSeq) {
        var whSeq = document.getElementById('wh_seq').value;
        var url = "{{ route('admin.scm_manage.ledger_detail') }}";
        url += "?goods_seq=" + goodsSeq;
        if(whSeq) url += "&wh_seq=" + whSeq;
        
        window.open(url, 'ledger_detail', 'width=1200,height=800,scrollbars=yes,resizable=yes');
    }

    function openAddAutoOrderGoodsPopup() {
        var chk = document.getElementsByName("chk[]");
        var len = chk.length;
        var check = false;
        var seqList = [];
        for(var i=0; i<len; i++){
            if(chk[i].checked == true){
                check = true;
                seqList.push(chk[i].value);
            }
        }
        if(!check){
            alert("상품을 선택해 주세요.");
            return;
        }
        
        document.getElementById('modal_goods_seq_list').value = seqList.join(',');
        $('#autoOrderModal').modal('show');
    }

    function excel_download(type) {
        if (type == 'select') {
             var chk = document.getElementsByName("chk[]");
            var len = chk.length;
            var check = false;
            var seqList = [];
            for(var i=0; i<len; i++){
                if(chk[i].checked == true){
                    check = true;
                    seqList.push(chk[i].value);
                }
            }
            if(!check){
                alert("상품을 선택해 주세요.");
                return;
            }
            // Temporarily use current URL or separate route
            var excelType = document.getElementById('excel_form').value;
            var url = "{{ route('admin.goods.excel_download') }}?type=" + excelType + "&target=select&goods_seq=" + seqList.join(',');
            location.href = url;
        } else {
             // List download - params from filters
             var excelType = document.getElementById('excel_form').value;
             var url = "{{ route('admin.goods.excel_download') }}?type=" + excelType + "&target=list&" + $('form').serialize();
             location.href = url;
        }
    }

    function sel_ledger(goods_seq, option_type, option_seq, price) {
        openStockDetail(goods_seq);
    }
</script>
@endsection
