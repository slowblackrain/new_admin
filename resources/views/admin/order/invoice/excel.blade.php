@extends('admin.layouts.admin')

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">송장 엑셀 처리</h3>
        </div>
        <div class="card-body">
            @if(session('alert'))
                <div class="alert alert-info">
                    {!! nl2br(session('alert')) !!}
                </div>
            @endif

            <form action="{{ route('admin.order.invoice.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label>처리 모드</label>
                    <div class="radio-list">
                        <label class="radio-inline">
                            <input type="radio" name="mode" value="all" checked> 
                            <strong>송장전송 처리 (Step 55/65)</strong> 
                            <span class="text-muted">- 송장번호 입력 및 출고완료 처리 (가장 많이 사용)</span>
                        </label>
                        <br/>
                        <label class="radio-inline">
                            <input type="radio" name="mode" value="insert"> 
                            <strong>운송장번호 입력 (Step 45)</strong>
                            <span class="text-muted">- 송장번호만 입력하고 '출고준비' 상태로 변경</span>
                        </label>
                        <br/>
                        <label class="radio-inline">
                            <input type="radio" name="mode" value="only"> 
                            <strong>운송장번호 변경 (상태유지)</strong>
                            <span class="text-muted">- 이미 입력된 송장번호만 수정 (주문상태 변경 없음)</span>
                        </label>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label>엑셀 파일 업로드 (CSV)</label>
                    <input type="file" name="export_excel_file" class="form-control" accept=".csv, .txt">
                    <small class="form-text text-muted">
                        * 파일 형식: CSV (UTF-8 권장)<br/>
                        * 컬럼 순서: <strong>주문번호(Seq), 택배사코드, 송장번호, [메모], [SMS]</strong><br/>
                        * 첫 줄(헤더)은 자동으로 무시됩니다.
                    </small>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">엑셀 업로드 및 처리</button>
                    <a href="{{ route('admin.order.invoice.excel') }}" class="btn btn-default btn-lg">새로고침</a>
                </div>
            </form>
        </div>
        <div class="card-footer">
            <h4>[택배사 코드 안내]</h4>
            <ul>
                <li><strong>CJ대한통운</strong>: code0 (cj)</li>
                <li><strong>우체국택배</strong>: code7 (epost)</li>
                <li><strong>한진택배</strong>: code9 (hanjin)</li>
                <li><strong>롯데택배</strong>: code10 (lotte)</li>
                <li><strong>로젠택배</strong>: code6 (logen)</li>
                <li><strong>경동택배</strong>: code3 (kdexp)</li>
                <li><strong>대신택배</strong>: code12 (daesin)</li>
                <li><strong>천일택배</strong>: code15 (chunil)</li>
                <li>* 엑셀 파일에는 <strong>code0, code7</strong> 형식으로 입력해주세요.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
