@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="page-title">상품 엑셀 일괄관리</h1>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">엑셀 대량 등록/수정</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>[안내]</strong> 엑셀 파일을 업로드하여 상품을 일괄 등록하거나 수정할 수 있습니다.<br>
                        대량 등록 샘플 파일을 다운로드하여 형식을 맞춰주세요.
                    </div>

                    <form action="{{ route('admin.goods.batch.excel_upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="excel_file" class="form-label">엑셀 파일 선택 (.xlsx, .xls)</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" required accept=".xlsx, .xls, .csv">
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">처리 방식</label>
                            <div>
                                <input type="radio" id="mode_regist" name="mode" value="regist" checked>
                                <label for="mode_regist">신규 등록</label>
                                
                                <input type="radio" id="mode_update" name="mode" value="update" class="ms-3">
                                <label for="mode_update">기존 상품 수정 (상품코드 기준)</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">엑셀 업로드 실행</button>
                        <a href="{{ route('admin.goods.batch.excel_download') }}" class="btn btn-secondary ms-2">현재 상품리스트 엑셀 다운로드</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
