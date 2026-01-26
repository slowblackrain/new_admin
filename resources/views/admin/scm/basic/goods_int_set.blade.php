@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">기본단가요율설정 (Goods Rate Setting)</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('admin.scm_basic.save_goods_int_set') }}" method="POST">
                @csrf
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">기본 마진 및 세율 설정</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">기본 마진율 (%)</label>
                            <div class="col-sm-4">
                                <input type="number" step="0.01" name="default_margin_rate" class="form-control" value="{{ $data['default_margin_rate'] ?? '10' }}">
                                <small class="text-muted">상품 등록 시 기본으로 적용될 마진율입니다.</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">과세율 (%)</label>
                            <div class="col-sm-4">
                                <input type="number" step="0.01" name="tax_rate" class="form-control" value="{{ $data['tax_rate'] ?? '10' }}">
                            </div>
                        </div>
                         <!-- Add more specific legacy fields as needed: x_list, g_list, etc. For now, basic global settings. -->
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">저장 (Save)</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
