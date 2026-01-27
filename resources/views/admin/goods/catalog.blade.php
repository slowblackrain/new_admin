@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">판매상품 관리</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Advanced Search Form -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">상품 검색</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" action="{{ route('admin.goods.catalog') }}">
                        <div class="row">
                            <!-- Keyword -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>검색어</label>
                                    <input type="text" name="keyword" class="form-control" placeholder="상품명, 상품코드" value="{{ $keyword }}">
                                </div>
                            </div>
                            <!-- Date Range -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>등록일</label>
                                    <div class="input-group">
                                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                                        <span class="input-group-text">~</span>
                                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                                    </div>
                                </div>
                            </div>
                            <!-- Provider Status -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>승인상태</label>
                                    <select name="provider_status" class="form-control">
                                        <option value="">전체</option>
                                        <option value="1" {{ $providerStatus == '1' ? 'selected' : '' }}>승인</option>
                                        <option value="0" {{ $providerStatus === '0' ? 'selected' : '' }}>미승인</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- New Filters -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>입점사</label>
                                    <select name="provider_seq" class="form-control">
                                        <option value="">전체</option>
                                        @foreach($providers as $p)
                                            <option value="{{ $p->provider_seq }}" {{ $scProvider == $p->provider_seq ? 'selected' : '' }}>{{ $p->provider_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>판매가 범위</label>
                                    <div class="input-group">
                                        <input type="text" name="min_price" class="form-control" placeholder="최소" value="{{ $minPrice }}">
                                        <span class="input-group-text">~</span>
                                        <input type="text" name="max_price" class="form-control" placeholder="최대" value="{{ $maxPrice }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>브랜드</label>
                                    <input type="text" name="brand" class="form-control" placeholder="브랜드명/코드" value="{{ $scBrand }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>모델명</label>
                                    <input type="text" name="model" class="form-control" placeholder="모델명" value="{{ $scModel }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>제조사</label>
                                    <input type="text" name="maker" class="form-control" placeholder="제조사" value="{{ $scMaker }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>원산지</label>
                                    <input type="text" name="origin" class="form-control" placeholder="원산지" value="{{ $scOrigin }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Goods Status (Checkbox) -->
                            <div class="col-md-12">
                                <label>판매 상태</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="goods_status[]" value="normal" id="status_normal" 
                                        {{ in_array('normal', $goodsStatus ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_normal">정상</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="goods_status[]" value="runout" id="status_runout"
                                        {{ in_array('runout', $goodsStatus ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_runout">품절</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="goods_status[]" value="stop" id="status_stop"
                                        {{ in_array('stop', $goodsStatus ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_stop">판매중지</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> 검색</button>
                            <a href="{{ route('admin.goods.catalog') }}" class="btn btn-secondary">초기화</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Goods List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">목록 (총 {{ number_format($goods->total()) }}개)</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.goods.batch.excel_download', request()->query()) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-file-excel"></i> 엑셀 다운로드
                        </a>
                        <a href="{{ route('admin.goods.batch.excel_form') }}" class="btn btn-sm btn-info">
                            <i class="fas fa-upload"></i> 엑셀 업로드
                        </a>
                        <button type="button" onclick="$('#batchForm').submit();" class="btn btn-sm btn-primary">
                            <i class="fas fa-save"></i> 선택 일괄수정
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <form id="batchForm" method="post" action="{{ route('admin.goods.batch.modify') }}">
                        @csrf
                        <input type="hidden" name="mode" value="status_update">
                        <table class="table table-bordered table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th style="width: 50px"><input type="checkbox" id="checkAll"></th>
                                    <th style="width: 80px">이미지</th>
                                    <th>상품 정보</th>
                                    <th style="width: 120px">발주현황/판매일</th>
                                    <th style="width: 120px">판매가 (할인가)</th>
                                    <th style="width: 100px">재고</th>
                                    <th style="width: 100px">상태/배송</th>
                                    <th style="width: 120px">관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($goods as $item)
                                <tr style="{{ $item->is_buying_service ? 'background-color: #ffccff;' : '' }}">
                                    <td class="text-center align-middle">
                                        <input type="checkbox" name="chk[]" value="{{ $item->goods_seq }}">
                                        <div class="mt-1 text-xs text-muted">{{ $loop->iteration }}</div>
                                    </td>
                                    <td class="align-middle text-center">
                                        @php
                                            $imgSrc = $item->image;
                                            if ($imgSrc && !str_starts_with($imgSrc, 'http') && !str_starts_with($imgSrc, '/')) {
                                                $imgSrc = "/data/goods/" . $imgSrc;
                                            }
                                        @endphp
                                        @if($item->image)
                                            <img src="{{ $imgSrc }}" style="width: 80px; height: 80px; object-fit: cover; border:1px solid #ddd;" onerror="this.src='/images/no_img.gif'">
                                        @else
                                            <span class="text-muted text-xs">No Img</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <!-- Provider Info -->
                                        <div class="mb-1">
                                            @if($item->provider_seq == 1)
                                                <span class="badge badge-success">매입</span>
                                            @else
                                                <span class="badge badge-info">{{ $item->provider_name }}</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Goods Info -->
                                        <div style="font-weight: bold; font-size: 1.1em;">{{ $item->goods_name }}</div>
                                        <div class="text-muted text-sm">
                                            [<span class="text-dark font-weight-bold">{{ $item->goods_scode }}</span>] 
                                            ({{ $item->goods_code }})
                                        </div>
                                        
                                        <!-- Legacy Icons/Buttons -->
                                        <div class="mt-1">
                                            @if($item->offer_chk == 'A') <span class="badge badge-warning">KC인증</span> @endif
                                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="copyToClipboard('{{ $item->goods_name }}')">이름복사</button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="copyToClipboard('{{ $item->goods_code }}')">코드복사</button>
                                        </div>
                                        
                                        <!-- Category -->
                                        <div class="mt-1 text-xs text-info">
                                            {{ $item->category_title }}
                                        </div>
                                        
                                        <div class="mt-1">
                                            <button type="button" class="btn btn-xs btn-default">정보</button>
                                            <button type="button" class="btn btn-xs btn-success">상세</button>
                                            <button type="button" class="btn btn-xs btn-danger">제작</button>
                                            <button type="button" class="btn btn-xs btn-primary">샵온</button>
                                        </div>
                                    </td>
                                    <td class="align-middle p-0">
                                        <!-- Legacy Offer Info Table -->
                                        {!! $item->offer_info !!}
                                        
                                        <!-- Fallback Recent Sale (if no offer info) -->
                                        @if(empty($item->offer_info) && $item->l_date)
                                        <div class="text-center p-2">
                                            <small class="text-muted">최근판매일:<br>{{ substr($item->l_date, 0, 10) }}</small>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="align-middle text-right pr-2 pl-0">
                                        <!-- Legacy Price Display -->
                                        {!! $item->disp_price !!}
                                        <div class="text-right">
                                            <small class="text-muted">({{ $item->opt_count }} 옵션)</small>
                                        </div>
                                    </td>
                                    <td class="align-middle text-right">
                                        <!-- Stock -->
                                        <div class="{{ $item->n_stock < 0 ? 'text-danger font-weight-bold' : '' }}">
                                            {{ number_format($item->n_stock) }} <small class="text-muted">실재고</small>
                                        </div>
                                        <div class="{{ $item->n_rstock < 0 ? 'text-danger font-weight-bold' : '' }}">
                                            {{ number_format($item->n_rstock) }} <small class="text-muted">가용</small>
                                        </div>
                                        
                                        <button class="btn btn-xs btn-secondary mt-1">옵션/재고</button>
                                        <button class="btn btn-xs btn-warning mt-1">입출고</button>
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($item->goods_view == 'look')
                                            <span class="badge badge-success mb-1">노출</span>
                                        @else
                                            <span class="badge badge-secondary mb-1">미노출</span>
                                        @endif
                                        <br>
                                        <select name="goodsStatus_{{ $item->goods_seq }}" class="form-control form-control-sm">
                                            <option value="normal" {{ $item->goods_status == 'normal' ? 'selected' : '' }}>정상</option>
                                            <option value="runout" {{ $item->goods_status == 'runout' ? 'selected' : '' }}>품절</option>
                                            <option value="stop" {{ $item->goods_status == 'stop' ? 'selected' : '' }}>판매중지</option>
                                            <option value="unsold" {{ $item->goods_status == 'unsold' ? 'selected' : '' }}>판매종료</option>
                                        </select>
                                    </td>
                                <td class="align-middle text-center">
                                    {{ substr($item->regist_date, 0, 10) }}<br>
                                    <a href="{{ route('admin.goods.edit', $item->goods_seq) }}" class="btn btn-sm btn-info mt-1">수정</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">검색된 상품이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $goods->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
