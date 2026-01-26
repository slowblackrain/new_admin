@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">상품 수정 : {{ $goods->goods_name }}</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Basic Info -->
            <div class="card collapsed-card"> <!-- Collapsed by default to focus on Options -->
                <div class="card-header">
                    <h3 class="card-title">기본 정보</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>상품명</label>
                        <input type="text" class="form-control" value="{{ $goods->goods_name }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>상품 코드</label>
                        <input type="text" class="form-control" value="{{ $goods->goods_code }}" readonly>
                    </div>
                    <!-- More fields can be added here -->
                </div>
            </div>

            <!-- Option Management -->
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">필수 옵션 관리</h3>
                </div>
                <form action="{{ route('admin.goods.update_options') }}" method="POST">
                    @csrf
                    <input type="hidden" name="goods_seq" value="{{ $goods->goods_seq }}">
                    
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr class="text-center bg-light">
                                    <th style="width: 50px">No</th>
                                    <th>옵션명 (옵션1 / 옵션2 ...)</th>
                                    <th style="width: 150px">판매가</th>
                                    <th style="width: 120px">재고</th>
                                    <th style="width: 80px">상태</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($options as $opt)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $opt->option1 }} 
                                        @if($opt->option2) / {{ $opt->option2 }} @endif
                                        @if($opt->option3) / {{ $opt->option3 }} @endif
                                        @if($opt->default_option == 'y') <span class="badge badge-info">기본</span> @endif
                                    </td>
                                    <td class="text-right">
                                        <input type="text" name="options[{{ $opt->option_seq }}][price]" class="form-control form-control-sm text-right" value="{{ number_format($opt->price) }}">
                                    </td>
                                    <td class="text-right">
                                        <input type="number" name="options[{{ $opt->option_seq }}][stock]" class="form-control form-control-sm text-right font-weight-bold" value="{{ $opt->stock }}">
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-success">판매중</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-right">
                         <button type="submit" class="btn btn-primary">옵션 정보 일괄 수정</button>
                    </div>
                </form>
            </div>
            
            <div class="text-center mt-3">
                <a href="{{ route('admin.goods.catalog') }}" class="btn btn-default">목록으로</a>
            </div>
        </div>
    </section>
</div>
@endsection
