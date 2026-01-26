@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">매장 {{ $store ? '수정' : '등록' }}</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ route('admin.scm_basic.store.save') }}" method="POST">
                @csrf
                <input type="hidden" name="store_seq" value="{{ $store->store_seq ?? '' }}">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">기본 정보</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">매장명 <span class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="store_name" class="form-control" value="{{ $store->store_name ?? '' }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">매장 URL</label>
                            <div class="col-sm-4">
                                <input type="text" name="store_url" class="form-control" value="{{ $store->store_url ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">기본 매장 설정</label>
                            <div class="col-sm-4">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="store_default" name="store_default" value="1" {{ ($store->store_default ?? 0) == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="store_default">기본 매장으로 사용</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('admin.scm_basic.store') }}" class="btn btn-default">취소</a>
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </div>

                <!-- Warehouse Linking (Future Implementation Placeholder) -->
                <!-- In legacy, this section allowed linking warehouses to the store -->
                <div class="card collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">연결된 창고 (Linked Warehouses)</h3>
                        <div class="card-tools">
                             <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">창고 연결 기능은 추후 구현될 예정입니다.</p>
                        <ul>
                            @foreach($warehouses as $wh)
                                <li>{{ $wh->wh_name }} ({{ $wh->wh_group }})</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

            </form>
        </div>
    </section>
</div>
@endsection
