@extends('admin.layouts.admin')

@section('title', '엑셀 송장처리')

@section('content')
<style type="text/css">
    .upload-form { margin-bottom:30px; padding-left:35px; }
    .upload-form .notice { color:red; margin-top:5px; }
    .input-upload { margin-top:10px; }
    .upload-log { margin-top:40px; width:100%; }
    .upload-notice { margin-top:20px; width:100%; }
    .td_css { text-align:center; height:20px; background-color:#EAEAEA; }
    .page-title-bar { margin-bottom: 20px; }
</style>

<div class="row">
    <div class="col-12">
        <!-- Page Title -->
        <div class="page-title-bar d-flex justify-content-between align-items-center">
            <h2 class="page-title">엑셀 송장처리</h2>
            <div class="page-buttons-right">
                <button type="button" class="btn btn-warning" onclick="download_sample();">업로드 샘플파일 다운로드</button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form name="excelUpload" id="excelUpload" method="post" action="{{ route('admin.order.invoice.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="item-title" style="text-align:left; font-weight:bold;">.CSV 형식으로 저장된 파일 업로드</div>
                    
                    <div class="notice" style="color:#000; margin-bottom: 10px;">
                        <div style="text-align:left;">
                            Excel 데이터 작성후 파일메뉴『다른이름으로 저장』, 파일형식『CSV(쉼표로분리)』 선택 저장하여 업로드 바랍니다.<br>
                            단, 위 형식과 다를 시 처리가 불가합니다.
                        </div>
                    </div>

                    <div class="input-upload" style="text-align:left;">
                        <input type="file" name="export_excel_file" id="export_excel_file" class="form-control d-inline-block" style="width: auto; height: auto;" />
                        
                        <div class="mt-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="mode" id="mode_all" value="all" checked>
                                <label class="form-check-label" for="mode_all">송장전송 처리(출고완료넘어감)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="mode" id="mode_only" value="only">
                                <label class="form-check-label" for="mode_only">운송장번호 변경(상태변경없음)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="mode" id="mode_insert" value="insert">
                                <label class="form-check-label" for="mode_insert">운송장번호 입력(출고준비로넘어감)</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success mt-3">업로드</button>
                    </div>

                    <div class="notice mt-2" style="text-align:left;">[주의] <b>.csv</b> 확장자로 저장하셔야 합니다.</div>
                </form>
            </div>
        </div>

        <!-- Courier Codes Table -->
        <div class="card mt-4">
            <div class="card-header bg-warning">
                <strong>택배사 코드 안내</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-sm text-center mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>코드</th><th>택배사명</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>code0</td><td>CJ대한통운</td></tr>
                        <tr><td>code1</td><td>DHL코리아</td></tr>
                        <tr><td>code2</td><td>KGB택배</td></tr>
                        <tr><td>code3</td><td>경동택배</td></tr>
                        <tr><td>code6</td><td>로젠택배</td></tr>
                        <tr><td>code7</td><td>우체국택배</td></tr>
                        <tr><td>code8</td><td>하나로택배</td></tr>
                        <tr><td>code9</td><td>한진택배</td></tr>
                        <tr><td>code10</td><td>롯데택배</td></tr>
                        <tr><td>code11</td><td>동원택배</td></tr>
                        <tr><td>code12</td><td>대신택배</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@section('custom_js')
<script>
    function download_sample(){
        window.open('https://dometopia.com/data/export_sample.csv');
    }

    @if(session('alert'))
        alert("{{ session('alert') }}");
    @endif
</script>
@endsection
@endsection
