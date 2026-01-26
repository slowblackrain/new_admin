@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">창고 {{ $warehouse ? '수정' : '등록' }}</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ route('admin.scm_basic.warehouse.save') }}" method="POST">
                @csrf
                <input type="hidden" name="wh_seq" value="{{ $warehouse->wh_seq ?? '' }}">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">기본 정보</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">창고명 <span class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="wh_name" class="form-control" value="{{ $warehouse->wh_name ?? '' }}" required>
                            </div>
                            <label class="col-sm-2 col-form-label">창고 그룹</label>
                            <div class="col-sm-4">
                                <input type="text" name="wh_group" class="form-control" value="{{ $warehouse->wh_group ?? '' }}" list="whGroupList">
                                <datalist id="whGroupList">
                                    @foreach($whGroup as $group)
                                        <option value="{{ $group }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">주소</label>
                            <div class="col-sm-10">
                                <input type="text" name="wh_address" class="form-control mb-1" placeholder="기본 주소" value="{{ $warehouse->wh_address ?? '' }}">
                                <input type="text" name="wh_address_detail" class="form-control" placeholder="상세 주소" value="{{ $warehouse->wh_address_detail ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">관리자 메모</label>
                            <div class="col-sm-10">
                                <textarea name="wh_admin_memo" class="form-control" rows="3">{{ $warehouse->wh_admin_memo ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">기본 창고 설정</label>
                            <div class="col-sm-4">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="wh_default" name="wh_default" value="1" {{ ($warehouse->wh_default ?? 0) == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="wh_default">기본 창고로 사용</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('admin.scm_basic.warehouse') }}" class="btn btn-default">취소</a>
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
