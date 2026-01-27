@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>상품 일괄 업데이트</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <form method="get" class="form-inline">
                        <input type="text" name="keyword" class="form-control mr-2" placeholder="상품명/코드" value="{{ $keyword }}">
                        <button type="submit" class="btn btn-primary">검색</button>
                    </form>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.goods.batch.modify') }}" method="POST">
                        @csrf
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="checkAll"></th>
                                    <th>상품코드</th>
                                    <th>상품명</th>
                                    <th>판매상태</th>
                                    <th>등록일</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($goods as $item)
                                <tr>
                                    <td><input type="checkbox" name="chk[]" value="{{ $item->goods_seq }}"></td>
                                    <td>{{ $item->goods_code }}</td>
                                    <td>{{ $item->goods_name }}</td>
                                    <td>
                                        <select name="goodsStatus_{{ $item->goods_seq }}" class="form-control form-control-sm">
                                            <option value="normal" {{ $item->goods_status == 'normal' ? 'selected' : '' }}>정상</option>
                                            <option value="runout" {{ $item->goods_status == 'runout' ? 'selected' : '' }}>품절</option>
                                            <option value="unsold" {{ $item->goods_status == 'unsold' ? 'selected' : '' }}>판매중지</option>
                                        </select>
                                    </td>
                                    <td>{{ substr($item->regist_date, 0, 10) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center">데이터가 없습니다.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">일괄 수정 저장</button>
                        </div>
                        <div class="mt-3">
                            {{ $goods->links() }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
