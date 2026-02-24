@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">진열번호 관리</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-success" onclick="downloadExcel()"><i class="fas fa-file-excel"></i> 진열번호엑셀</button>
                </div>
            </div>
            
            <div class="card-body">
                <!-- 검색창 및 일괄등록 -->
                <form id="searchForm" method="GET" action="{{ route('admin.goods.sortcd_catalog') }}" class="mb-4 p-3 bg-light border">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="input-group mb-2">
                                <input type="text" name="keyword" class="form-control" placeholder="진열번호, 상품코드 검색" value="{{ request('keyword') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> 검색</button>
                                </div>
                            </div>
                            <div>
                                <label class="mr-3"><input type="radio" name="gubun" value="all" {{ request('gubun', 'all') == 'all' ? 'checked' : '' }}> 전체보기</label>
                                <label class="mr-3"><input type="radio" name="gubun" value="y" {{ request('gubun') == 'y' ? 'checked' : '' }}> 상품연결 有</label>
                                <label><input type="radio" name="gubun" value="n" {{ request('gubun') == 'n' ? 'checked' : '' }}> 상품연결 無</label>
                            </div>
                        </div>
                        <div class="col-md-6 border-left">
                            <textarea id="batch_sortcd_contents" class="form-control mb-2" rows="2" placeholder="상품에 연결할 진열번호를 일괄 추가하려면 여러줄 이나 쉼표(,)로 구분하여 입력하세요"></textarea>
                            <button type="button" class="btn btn-warning float-right" onclick="addBatchSortcd()">진열번호 다중 추가</button>
                        </div>
                    </div>
                </form>

                <!-- 목록 타이틀 영역 -->
                <div class="d-flex justify-content-between mb-2">
                    <h5>진열번호 리스트 (총 {{ number_format($memos->total()) }}건)</h5>
                    <div>
                        <select id="orderby" name="orderby" class="form-control d-inline-block w-auto" onchange="changeFilter()">
                            <option value="asc@goods_sortcd" {{ request('orderby', 'asc@goods_sortcd') == 'asc@goods_sortcd' ? 'selected' : '' }}>진열번호순↑</option>
                            <option value="desc@goods_sortcd" {{ request('orderby') == 'desc@goods_sortcd' ? 'selected' : '' }}>진열번호순↓</option>			
                            <option value="asc@goods_scode" {{ request('orderby') == 'asc@goods_scode' ? 'selected' : '' }}>상품코드순↑</option>
                            <option value="desc@goods_scode" {{ request('orderby') == 'desc@goods_scode' ? 'selected' : '' }}>상품코드순↓</option>
                        </select>
                        <select id="perpage" name="perpage" class="form-control d-inline-block w-auto" onchange="changeFilter()">
                            <option value="50" {{ request('perpage') == 50 ? 'selected' : '' }}>50개씩</option>
                            <option value="100" {{ request('perpage', 100) == 100 ? 'selected' : '' }}>100개씩</option>
                            <option value="300" {{ request('perpage') == 300 ? 'selected' : '' }}>300개씩</option>
                            <option value="500" {{ request('perpage') == 500 ? 'selected' : '' }}>500개씩</option>
                        </select>
                    </div>
                </div>

                <!-- 테이블 폼 -->
                <form id="listForm">
                    <div class="mb-2 text-right">
                        <button type="button" class="btn btn-primary" onclick="saveBulkModify()">선택 일괄저장</button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered text-center table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 5%">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th style="width: 35%">진열번호</th>
                                    <th style="width: 35%">상품코드</th>
                                    <th style="width: 25%">관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- 개별 추가 로우 -->
                                <tr class="bg-light">
                                    <td>[NEW]</td>
                                    <td><input type="text" id="new_sortcd" class="form-control text-center" placeholder="새 진열번호"></td>
                                    <td><input type="text" id="new_scode" class="form-control text-center" placeholder="매핑 상품코드"></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" onclick="addSingleSortcd()">새로 추가</button>
                                    </td>
                                </tr>

                                @forelse($memos as $memo)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="seq_arr[]" class="chk-item" value="{{ $memo->sortcd_seq }}">
                                    </td>
                                    <td>
                                        <input type="text" id="goods_sortcd_{{ $memo->sortcd_seq }}" name="goods_sortcd_{{ $memo->sortcd_seq }}" class="form-control text-center" value="{{ $memo->goods_sortcd }}" onkeypress="if(event.keyCode==13){ event.preventDefault(); updateSingleSortcd({{ $memo->sortcd_seq }}); }">
                                    </td>
                                    <td>
                                        <input type="text" id="goods_scode_{{ $memo->sortcd_seq }}" name="goods_scode_{{ $memo->sortcd_seq }}" class="form-control text-center" value="{{ $memo->goods_scode }}" onkeypress="if(event.keyCode==13){ event.preventDefault(); updateSingleSortcd({{ $memo->sortcd_seq }}); }">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success" onclick="updateSingleSortcd({{ $memo->sortcd_seq }})">수정</button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteSortcd({{ $memo->sortcd_seq }})">삭제</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4">검색된 진열번호 내역이 없습니다. (혹은 데이터가 비어있습니다)</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>

                <div class="mt-3">
                    {{ $memos->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
    // 옵션 필터링 변경
    function changeFilter() {
        var orderby = $('#orderby').val();
        var perpage = $('#perpage').val();
        var form = $('#searchForm');
        
        $('<input>').attr('type','hidden').attr('name','orderby').attr('value',orderby).appendTo(form);
        $('<input>').attr('type','hidden').attr('name','perpage').attr('value',perpage).appendTo(form);
        
        form.submit();
    }

    // 전체 선택/해제
    $('#checkAll').on('change', function() {
        $('.chk-item').prop('checked', $(this).prop('checked'));
    });

    // 엑셀 다운로드
    function downloadExcel() {
        var queryStr = $('#searchForm').serialize();
        queryStr += '&orderby=' + $('#orderby').val();
        location.href = "{{ route('admin.goods.sortcd_catalog.excel') }}?" + queryStr;
    }

    // AJAX 헬퍼
    function callAjax(url, data, successMsg) {
        $.ajax({
            url: url,
            type: "POST",
            data: $.extend(data, { _token: '{{ csrf_token() }}' }),
            success: function(res) {
                if(res.success) {
                    alert(res.message || successMsg);
                    location.reload();
                } else {
                    alert('실패: ' + res.message);
                }
            },
            error: function() {
                alert('서버 오류가 발생했습니다.');
            }
        });
    }

    // 진열번호 다중 추가
    function addBatchSortcd() {
        var contents = $('#batch_sortcd_contents').val();
        if(contents.replace(/\s/g, '').length < 1) {
            alert('등록할 진열번호를 입력해주세요.');
            return;
        }
        callAjax("{{ route('admin.goods.sortcd_catalog.store') }}", {
            mode: 'insert_list',
            contents: contents
        });
    }

    // 단일 추가
    function addSingleSortcd() {
        var sortcd = $('#new_sortcd').val();
        var scode = $('#new_scode').val();
        
        if(!sortcd) {
            alert('진열번호를 입력해주세요.');
            return;
        }

        callAjax("{{ route('admin.goods.sortcd_catalog.store') }}", {
            mode: 'insert',
            sortcd: sortcd,
            scode: scode
        });
    }

    // 단건 수정
    function updateSingleSortcd(seq) {
        var sortcd = $('#goods_sortcd_' + seq).val();
        var scode = $('#goods_scode_' + seq).val();

        if(!sortcd) {
            alert('진열번호는 공백일 수 없습니다.');
            return;
        }

        callAjax("{{ route('admin.goods.sortcd_catalog.update') }}", {
            mode: 'modify',
            sortcd_seq: seq,
            sortcd: sortcd,
            scode: scode
        });
    }

    // 일괄 수정
    function saveBulkModify() {
        if($('.chk-item:checked').length < 1) {
            alert('일괄 저장할 항목을 하나 이상 체크해주세요.');
            return;
        }
        
        var formData = $('#listForm').serializeArray();
        formData.push({name: 'mode', value: 'modify_multiple'});

        var dataObj = {};
        $(formData).each(function(index, obj){
            if(dataObj[obj.name]) {
                if(!Array.isArray(dataObj[obj.name])) {
                    dataObj[obj.name] = [dataObj[obj.name]];
                }
                dataObj[obj.name].push(obj.value);
            } else {
                dataObj[obj.name] = obj.value;
            }
        });

        callAjax("{{ route('admin.goods.sortcd_catalog.update') }}", dataObj);
    }

    // 삭제
    function deleteSortcd(seq) {
        if(!confirm('해당 진열번호를 삭제하시겠습니까? (상품쪽 DB의 연결값은 해제되지 않습니다)')) return;

        callAjax("{{ route('admin.goods.sortcd_catalog.destroy') }}", {
            mode: 'del',
            sortcd_seq: seq
        });
    }
</script>
@endsection
