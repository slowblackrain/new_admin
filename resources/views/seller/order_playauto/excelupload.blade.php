@extends('seller.layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">일괄 주문업로드/재고확인</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-right">
                    @if(isset($orderData) && count($orderData) > 0)
                        <button type="button" class="btn btn-warning" onclick="history.back()">뒤로가기</button>
                        <button type="button" class="btn btn-success" onclick="submitOrders()">주문 접수</button>
                    @else
                        <a href="/data/dometopia_doc3.csv" class="btn btn-info"><i class="fas fa-download"></i> 샘플파일 다운로드</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        <!-- Error Alert -->
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if(isset($result_error) && !empty($result_error))
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">오류 목록 ({{ count($result_error) }}건)</h3>
                </div>
                <div class="card-body table-responsive p-0" style="max-height: 200px;">
                    <table class="table table-hover text-nowrap table-sm">
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>Error Message</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($result_error as $err)
                            <tr>
                                <td>{{ $err['line'] ?? '-' }}</td>
                                <td class="text-danger font-weight-bold">{{ $err['error'] ?? 'Unknown Error' }}</td>
                                <td>{{ Str::limit(json_encode($err, JSON_UNESCAPED_UNICODE), 50) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                     <button type="button" class="btn btn-warning" onclick="history.back()">뒤로가기</button>
                </div>
            </div>
        @endif

        @if(isset($orderData) && count($orderData) > 0)
        <!-- Preview & Submit Form -->
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">등록 가능 목록 ({{ count($orderData) }}건)</h3>
            </div>
            <div class="card-body p-0">
                <div class="p-3 bg-light border-bottom">
                    <strong>{{ Auth::guard('seller')->user()->provider_name }}({{ Auth::guard('seller')->user()->provider_id }})</strong>님의 적립금은 
                    <strong class="text-success">{{ number_format(\App\Models\Member::find(Auth::guard('seller')->user()->member_seq)->emoney ?? 0) }}</strong>원 입니다.
                </div>
                
                <form action="{{ route('seller.order.excel_store') }}" method="POST" id="orderConfirmForm">
                    @csrf
                   @foreach($orderData as $index => $row)
                        @foreach($row as $key => $value)
                            <input type="hidden" name="orders[{{ $index }}][{{ $key }}]" value="{{ $value }}">
                        @endforeach
                    @endforeach

                    <div class="table-responsive" style="max-height: 500px;">
                        <table class="table table-bordered table-head-fixed text-nowrap">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>수령인</th>
                                    <th>연락처</th>
                                    <th>주소</th>
                                    <th>상품명 (코드)</th>
                                    <th>수량</th>
                                    <th>공급가</th>
                                    <th>배송비</th>
                                    <th>합계</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderData as $idx => $row)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $row['recive_name'] }}</td>
                                    <td>{{ $row['recive_phone'] }} / {{ $row['recive_cell'] ?? '' }}</td>
                                    <td>({{ $row['zipcode'] }}) {{ $row['addr'] }}</td>
                                    <td>
                                        {{ $row['goods_name'] }} <br>
                                        <small class="text-muted">({{ $row['goods_code'] }})</small>
                                    </td>
                                    <td>{{ number_format($row['goods_ea']) }}</td>
                                    <td>{{ number_format($row['price']) }}</td>
                                    <td>{{ number_format($row['delivery_price']) }}</td>
                                    <td>{{ number_format($row['settleprice']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="card-footer clearfix">
                 <div class="float-left pt-2">
                    총 결제 예정 금액: <strong>{{ number_format(collect($orderData)->sum('settleprice')) }}</strong> 원
                    (예상 잔액: {{ number_format($orderData[count($orderData)-1]['now_emoney'] ?? 0) }} 원)
                </div>
                <div class="float-right">
                    <button type="button" class="btn btn-secondary mr-2" onclick="history.back()">뒤로가기</button>
                    <button type="button" class="btn btn-success" onclick="submitOrders()">주문 접수</button>
                </div>
            </div>
        </div>

        @else

        <!-- Upload Form -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">파일 업로드</h3>
                <div class="card-tools">
                    <a href="/data/dometopia_doc3.csv" class="btn btn-tool" style="color:white; text-decoration: underline;">
                        <i class="fas fa-download"></i> 샘플파일 다운로드
                    </a>
                </div>
            </div>
            <form action="{{ route('seller.order.excel_upload_process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="csv_file">CSV 파일 선택</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="csv_file" name="csv_file" accept=".csv, .txt">
                                <label class="custom-file-label" for="csv_file">파일을 선택하세요</label>
                            </div>
                            <div class="input-group-append">
                                <span class="input-group-text">Upload</span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info mt-3">
                        <h5><i class="fas fa-info-circle"></i> 주의사항</h5>
                        <ul>
                            <li><strong>.csv</strong> 확장자로 저장된 파일만 업로드 가능합니다.</li>
                            <li>상품명에 상품코드 OR 관리코드 두가지 입력이 가능합니다.</li>
                            <li>입력 가능한 필드 외에 임의로 필드를 추가하거나 수정하지 마세요.</li>
                            <li>무료배송 조건 충족 시 배송비가 무료로 적용됩니다. (도서산간 별도)</li>
                        </ul>
                        <div class="mt-2 text-center">
                             <img src="/images/seller/deliver_csv_example.jpg" class="img-fluid border" alt="CSV 예시 이미지" style="max-width: 100%;">
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <strong>{{ Auth::guard('seller')->user()->provider_name }}({{ Auth::guard('seller')->user()->provider_id }})</strong>님의 현재 적립금: 
                        <span class="text-success font-weight-bold">{{ number_format(\App\Models\Member::find(Auth::guard('seller')->user()->member_seq)->emoney ?? 0) }}</span> 원
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <button type="submit" class="btn btn-primary float-right">업로드</button>
                </div>
            </form>
        </div>
        
        <!-- Stock Check Form (Placeholder) -->
        <div class="card mt-4 collapsed-card">
            <div class="card-header">
                <h3 class="card-title">재고 확인 (단품 검색)</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="card-body" style="display: none;">
                <form class="form-inline">
                    <select class="form-control mr-2">
                        <option>관리코드</option>
                        <option>상품코드</option>
                    </select>
                    <input type="text" class="form-control mr-2" placeholder="상품코드 입력" style="width: 300px;">
                    <button type="button" class="btn btn-success" onclick="alert('준비중입니다.');">재고확인</button>
                </form>
            </div>
        </div>

        @endif

    </div>
</section>

<script>
    // Custom file input label update
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = document.getElementById("csv_file").files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });

    function submitOrders() {
        if(confirm('총 {{ count($orderData ?? []) }}건의 주문을 접수하시겠습니까?')) {
            document.getElementById('orderConfirmForm').submit();
        }
    }
</script>
@endsection
