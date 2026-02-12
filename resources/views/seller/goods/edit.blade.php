@extends('seller.layouts.app')

@section('title', '상품 수정')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">상품 수정</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('seller.goods.index') }}">상품 목록</a></li>
                <li class="breadcrumb-item active">상품 수정</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('seller.goods.update', $goods->goods_seq) }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">기본 정보 수정</h3>
            </div>
            <div class="card-body">
                <!-- Category -->
                <div class="form-group row">
                    <label for="category1" class="col-sm-2 col-form-label">카테고리 <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <input type="hidden" name="old_category1" value="{{ $currentCat1 }}">
                        <select name="category1" id="category1" class="form-control" required>
                            <option value="">대분류 선택</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->category_code }}" {{ old('category1', $currentCat1) == $cat->category_code ? 'selected' : '' }}>{{ $cat->title }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">현재는 대분류 변경만 지원합니다.</small>
                    </div>
                </div>

                <!-- Goods Code -->
                 <div class="form-group row">
                    <label class="col-sm-2 col-form-label">상품코드</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" value="{{ $goods->goods_scode }}" readonly>
                    </div>
                </div>

                <!-- Goods Name -->
                <div class="form-group row">
                    <label for="goods_name" class="col-sm-2 col-form-label">상품명 <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <input type="text" name="goods_name" class="form-control" id="goods_name" required value="{{ old('goods_name', $goods->goods_name) }}">
                    </div>
                </div>

                 <!-- Summary -->
                <div class="form-group row">
                    <label for="summary" class="col-sm-2 col-form-label">상품 요약설명</label>
                    <div class="col-sm-10">
                        <input type="text" name="summary" class="form-control" id="summary" value="{{ old('summary', $goods->summary) }}">
                    </div>
                </div>

                <!-- Keyword -->
                <div class="form-group row">
                    <label for="keyword" class="col-sm-2 col-form-label">검색 키워드</label>
                    <div class="col-sm-10">
                        <input type="text" name="keyword" class="form-control" id="keyword" value="{{ old('keyword', $goods->keyword) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">가격 및 재고 정보</h3>
            </div>
            <div class="card-body">
                 <!-- Prices -->
                <div class="form-group row">
                    <label for="consumer_price" class="col-sm-2 col-form-label">정가 (소비자가)</label>
                    <div class="col-sm-4">
                        <input type="number" name="consumer_price" class="form-control" id="consumer_price" value="{{ old('consumer_price', $goods->defaultOption->consumer_price ?? 0) }}">
                    </div>
                    <label for="price" class="col-sm-2 col-form-label">판매가 <span class="text-danger">*</span></label>
                    <div class="col-sm-4">
                        <input type="number" name="price" class="form-control" id="price" required value="{{ old('price', $goods->defaultOption->price ?? 0) }}">
                    </div>
                </div>

                 <div class="form-group row">
                    <label for="supply_price" class="col-sm-2 col-form-label">공급가 (매입가) <span class="text-danger">*</span></label>
                    <div class="col-sm-4">
                        <input type="number" name="supply_price" class="form-control" id="supply_price" required value="{{ old('supply_price', $goods->supply_price) }}">
                    </div>
                     <label for="stock" class="col-sm-2 col-form-label">재고수량</label>
                    <div class="col-sm-4">
                        <input type="number" name="stock" class="form-control" id="stock" value="{{ old('stock', $goods->stock) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-info">
             <div class="card-header">
                <h3 class="card-title">노출 및 상태 설정</h3>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">노출여부</label>
                    <div class="col-sm-10">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="goods_view" id="view_look" value="look" {{ old('goods_view', $goods->goods_view) == 'look' ? 'checked' : '' }}>
                            <label class="form-check-label" for="view_look">노출함</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="goods_view" id="view_not_look" value="not_look" {{ old('goods_view', $goods->goods_view) == 'not_look' ? 'checked' : '' }}>
                            <label class="form-check-label" for="view_not_look">노출안함</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">판매상태</label>
                    <div class="col-sm-10">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="goods_status" id="status_normal" value="normal" {{ old('goods_status', $goods->goods_status) == 'normal' ? 'checked' : '' }}>
                            <label class="form-check-label" for="status_normal">정상판매</label>
                        </div>
                         <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="goods_status" id="status_runout" value="runout" {{ old('goods_status', $goods->goods_status) == 'runout' ? 'checked' : '' }}>
                            <label class="form-check-label" for="status_runout">품절</label>
                        </div>
                         <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="goods_status" id="status_unsold" value="unsold" {{ old('goods_status', $goods->goods_status) == 'unsold' ? 'checked' : '' }}>
                            <label class="form-check-label" for="status_unsold">판매중지</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4 text-center">
                <button type="submit" class="btn btn-warning btn-lg px-5">수정 저장</button>
                <a href="{{ route('seller.goods.index') }}" class="btn btn-secondary btn-lg px-5">취소</a>
            </div>
        </div>

    </form>
</div>
@endsection
