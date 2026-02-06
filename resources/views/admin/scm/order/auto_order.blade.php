@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">자동 발주 추천 목록 (Auto Order Candidates)</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 안전재고 미달 상품 목록입니다. 발주 수량을 입력하고 등록 버튼을 누르면 발주 대기(Draft) 상태로 저장됩니다.
                </div>

                <form method="GET" action="{{ route('admin.scm_order.auto_order') }}" class="mb-4">
                    <div class="form-row align-items-center">
                         <div class="col-auto">
                            <input type="text" class="form-control mb-2" name="keyword" placeholder="상품명/코드 Search" value="{{ $filters['keyword'] ?? '' }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary mb-2">검색</button>
                        </div>
                    </div>
                </form>

                <form method="POST" action="#" id="batchForm"> 
                    @csrf
                    <!-- Temporary action # until bulk controller is ready -->
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th><input type="checkbox" id="checkAll"></th>
                                    <th>상품정보</th>
                                    <th>옵션</th>
                                    <th>현재고</th>
                                    <th>안전재고</th>
                                    <th>부족분</th>
                                    <th>발주수량</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($candidates as $item)
                                    @php
                                        $shortage = $item->safe_stock - $item->stock;
                                        $suggested = ($shortage > 0) ? $shortage : 0;
                                    @endphp
                                <tr>
                                    <td><input type="checkbox" name="items[]" value="{{ $item->option_seq }}" class="chk"></td>
                                    <td class="text-left">
                                        {{ $item->goods_name }} <br>
                                        <small class="text-muted">{{ $item->goods_code }}</small>
                                    </td>
                                    <td>{{ $item->option_name }}</td>
                                    <td>{{ number_format($item->stock) }}</td>
                                    <td>{{ number_format($item->safe_stock) }}</td>
                                    <td class="text-danger font-weight-bold">{{ number_format($shortage) }}</td>
                                    <td style="width: 100px;">
                                        <input type="number" class="form-control form-control-sm text-right" name="order_ea[{{ $item->option_seq }}]" value="{{ $suggested }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-primary" onclick="alert('Single registration pending')">등록</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8">발주 대상 상품이 없습니다. (No candidates found)</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
                
                <div class="mt-3">
                    {{ $candidates->appends($filters)->links() }}
                </div>
            </div>
            
            <div class="card-footer">
                <button type="button" class="btn btn-success" onclick="alert('Bulk registration pending')">선택 상품 발주 등록 (Register Selected)</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
    $('#checkAll').click(function() {
        $('.chk').prop('checked', this.checked);
    });
</script>
@endsection
