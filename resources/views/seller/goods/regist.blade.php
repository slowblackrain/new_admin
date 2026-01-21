@extends('seller.layouts.app')

@section('title', '상품 등록')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">상품 등록</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">상품 등록</li>
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

    <form action="{{ route('seller.goods.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">기본 정보</h3>
            </div>
            <div class="card-body">
                <!-- Category -->
                <div class="form-group row">
                    <label for="category1" class="col-sm-2 col-form-label">카테고리 <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <div class="row">
                            <div class="col-3">
                                <select name="category1" id="category1" class="form-control">
                                    <option value="">대분류 선택</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->category_code }}">{{ $cat->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Subcategories placeholders for future implementation -->
                            <div class="col-3">
                                <select name="category2" id="category2" class="form-control" disabled>
                                    <option value="">중분류</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <select name="category3" id="category3" class="form-control" disabled>
                                    <option value="">소분류</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <select name="category4" id="category4" class="form-control" disabled>
                                    <option value="">세분류</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Goods Code -->
                 <div class="form-group row">
                    <label for="goods_scode" class="col-sm-2 col-form-label">상품코드</label>
                    <div class="col-sm-4">
                        <input type="text" name="goods_scode" class="form-control" id="goods_scode" value="{{ $gscode }}" readonly>
                    </div>
                </div>

                <!-- Goods Name -->
                <div class="form-group row">
                    <label for="goods_name" class="col-sm-2 col-form-label">상품명 <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <input type="text" name="goods_name" class="form-control" id="goods_name" placeholder="상품명을 입력하세요" required value="{{ old('goods_name') }}">
                    </div>
                </div>

                 <!-- Summary -->
                <div class="form-group row">
                    <label for="summary" class="col-sm-2 col-form-label">상품 요약설명</label>
                    <div class="col-sm-10">
                        <input type="text" name="summary" class="form-control" id="summary" placeholder="짧은 설명" value="{{ old('summary') }}">
                    </div>
                </div>

                <!-- Keyword -->
                <div class="form-group row">
                    <label for="keyword" class="col-sm-2 col-form-label">검색 키워드</label>
                    <div class="col-sm-10">
                        <input type="text" name="keyword" class="form-control" id="keyword" placeholder="쉼표(,)로 구분" value="{{ old('keyword') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">가격 및 재고 정보</h3>
            </div>
            <div class="card-body">
                 <!-- Prices -->
                <div class="form-group row">
                    <label for="consumer_price" class="col-sm-2 col-form-label">정가 (소비자가)</label>
                    <div class="col-sm-4">
                        <input type="number" name="consumer_price" class="form-control" id="consumer_price" value="{{ old('consumer_price', 0) }}">
                    </div>
                    <label for="price" class="col-sm-2 col-form-label">판매가 <span class="text-danger">*</span></label>
                    <div class="col-sm-4">
                        <input type="number" name="price" class="form-control" id="price" required value="{{ old('price') }}">
                    </div>
                </div>

                 <div class="form-group row">
                    <label for="supply_price" class="col-sm-2 col-form-label">공급가 (매입가) <span class="text-danger">*</span></label>
                    <div class="col-sm-4">
                        <input type="number" name="supply_price" class="form-control" id="supply_price" required value="{{ old('supply_price') }}">
                    </div>
                     <label for="stock" class="col-sm-2 col-form-label">재고수량</label>
                    <div class="col-sm-4">
                        <input type="number" name="stock" class="form-control" id="stock" value="{{ old('stock', 999) }}">
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
                            <input class="form-check-input" type="radio" name="goods_view" id="view_look" value="look" checked>
                            <label class="form-check-label" for="view_look">노출함</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="goods_view" id="view_not_look" value="not_look">
                            <label class="form-check-label" for="view_not_look">노출안함</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">판매상태</label>
                    <div class="col-sm-10">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="goods_status" id="status_normal" value="normal" checked>
                            <label class="form-check-label" for="status_normal">정상판매</label>
                        </div>
                         <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="goods_status" id="status_runout" value="runout">
                            <label class="form-check-label" for="status_runout">품절</label>
                        </div>
                         <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="goods_status" id="status_unsold" value="unsold">
                            <label class="form-check-label" for="status_unsold">판매중지</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4 text-center">
                <button type="submit" class="btn btn-primary btn-lg px-5">상품 등록</button>
                <a href="{{ route('seller.dashboard') }}" class="btn btn-secondary btn-lg px-5">취소</a>
            </div>
        </div>

    </form>
</div>
@endsection
