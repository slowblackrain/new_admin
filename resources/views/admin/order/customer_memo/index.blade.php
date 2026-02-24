@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">고객상담 메모(템플릿) 관리</h3>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                
                <table class="table table-bordered text-center">
                    <colgroup>
                        <col style="width:10%;">
                        <col style="width:60%;">
                        <col style="width:10%;">
                        <col style="width:20%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>우선순위(정렬)</th>
                            <th>메모 템플릿 내용</th>
                            <th>중요 표시</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($memos as $memo)
                            <tr>
                                <td>
                                    <input type="number" id="sort_{{ $memo->memo_idx }}" value="{{ $memo->sort }}" class="form-control text-center" style="width: 80px; margin: 0 auto;">
                                </td>
                                <td>
                                    <input type="text" id="memo_{{ $memo->memo_idx }}" value="{{ $memo->memo }}" class="form-control">
                                </td>
                                <td>
                                    <input type="checkbox" id="point_{{ $memo->memo_idx }}" value="y" {{ $memo->point == 'y' ? 'checked' : '' }}>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="updateMemo({{ $memo->memo_idx }})">수정</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteMemo({{ $memo->memo_idx }})">삭제</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">등록된 메모 템플릿이 없습니다.</td>
                            </tr>
                        @endforelse
                        
                        <!-- 추가 폼 -->
                        <tr class="table-active">
                            <form action="{{ route('admin.order.customer_memo.store') }}" method="POST">
                                @csrf
                                <td>
                                    <input type="number" name="sort" value="0" class="form-control text-center" style="width: 80px; margin: 0 auto;">
                                </td>
                                <td>
                                    <input type="text" name="memo" placeholder="새로운 템플릿 문구를 입력하세요" class="form-control" required>
                                </td>
                                <td>
                                    <input type="checkbox" name="point" value="y">
                                </td>
                                <td>
                                    <button type="submit" class="btn btn-sm btn-primary">추가하기</button>
                                </td>
                            </form>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>
@endsection

@section('custom_js')
<script>
    function updateMemo(idx) {
        var sort = $('#sort_' + idx).val();
        var memo = $('#memo_' + idx).val();
        var point = $('#point_' + idx).is(':checked') ? 'y' : 'n';

        $.ajax({
            url: "{{ route('admin.order.customer_memo.update') }}",
            type: "POST",
            data: {
                _token: '{{ csrf_token() }}',
                memo_idx: idx,
                sort: sort,
                memo: memo,
                point: point
            },
            success: function(res) {
                if(res.success) {
                    alert('수정되었습니다.');
                    location.reload();
                } else {
                    alert('수정 실패: ' + res.message);
                }
            },
            error: function() {
                alert('서버 에러가 발생했습니다.');
            }
        });
    }

    function deleteMemo(idx) {
        if(!confirm('정말 삭제하시겠습니까?')) return;

        $.ajax({
            url: "{{ route('admin.order.customer_memo.destroy') }}",
            type: "POST",
            data: {
                _token: '{{ csrf_token() }}',
                memo_idx: idx
            },
            success: function(res) {
                if(res.success) {
                    alert('삭제되었습니다.');
                    location.reload();
                } else {
                    alert('삭제 실패: ' + res.message);
                }
            },
            error: function() {
                alert('서버 에러가 발생했습니다.');
            }
        });
    }
</script>
@endsection
