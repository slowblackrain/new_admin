@extends('admin.layouts.admin')

@section('content')
<div class="title_group">
    <h2>세트상품 관리</h2>
    <div class="btn_group">
        <button type="button" class="btn" onclick="set_excel();">세트엑셀다운</button>
    </div>
</div>

<div class="search_area">
    <form name="sortcdSchForm" id="sortcdSchForm" method="GET" action="{{ route('admin.goods.set.index') }}">
        <table class="search_table">
            <tr>
                <th>검색어</th>
                <td>
                    <input type="text" name="keyword" value="{{ $keyword }}" placeholder="상품명/상품코드" class="w300">
                    <button type="submit" class="btn">검색</button>
                </td>
            </tr>
        </table>
    </form>
</div>

<div class="content_area goods_set_container">
    <div class="left_area">
        <div class="area_title">
            세트 대표 상품리스트
            <span class="fr"><button type="button" name="add_goods_set" class="btn small">대표상품추가</button></span>
        </div>
        <div id="set_goods_list" class="list_box">
            @foreach($sets as $set)
            <div class="setGoods item_box" id="{{ $set->goods_seq }}" data-id="{{ $set->goods_seq }}">
                <div class="img">
                    <img src="{{ $set->goods->images->first()->image ?? '/images/no_img.gif' }}" width="50">
                </div>
                <div class="info">
                    <div class="scode">[{{ $set->goods->goods_scode }}] ({{ $set->child_count }})</div>
                    <div class="name">{{ $set->goods->goods_name }}</div>
                </div>
            </div>
            @endforeach
            <div class="paging">
                {{ $sets->appends(request()->input())->links() }}
            </div>
        </div>
    </div>

    <div class="right_area" style="margin-left:20px;">
        <div class="area_title">
            세트 세부 상품리스트
            <span class="fr"><button type="button" name="add_goods_dt" class="btn small">세부상품추가</button></span>
        </div>
        <div id="targetList" class="detail_list_box">
            <div class="empty_msg">대표 상품을 선택 하세요.</div>
        </div>
    </div>
</div>

<input type="hidden" id="sel_goods" value="0">

<!-- Add Modal -->
<div id="add_goods_modal" style="display:none;" title="상품 추가">
    <div class="modal_content">
        <p>추가할 상품코드를 입력해주세요.</p>
        <div class="search_box">
            <input type="text" id="s_scode" placeholder="상품코드 입력">
            <button type="button" onclick="code_ck();" class="btn">확인</button>
        </div>
        <div id="result_goods" class="result_box"></div>
    </div>
</div>

<style>
.goods_set_container { display: flex; align-items: flex-start; }
.left_area, .right_area { width: 50%; border:1px solid #ddd; background:#fff; min-height:600px; }
.area_title { background:#f1f1f1; padding:10px; font-weight:bold; border-bottom:1px solid #ddd; }
.list_box { padding:10px; height: 550px; overflow-y: auto; }
.item_box { border-bottom:1px solid #eee; padding:5px; cursor:pointer; display:flex; align-items:center; }
.item_box:hover { background:#f9f9f9; }
.item_box.selected { background:#e7f2fc; }
.item_box .img { margin-right:10px; }
.item_box .info { font-size:12px; }
.detail_list_box { height:550px; overflow-y: auto; padding:10px; }
.empty_msg { text-align:center; padding:50px; color:#999; }
.btn.small { padding: 2px 8px; font-size: 11px; height: 24px; line-height: 18px; }
</style>

<script>
$(document).ready(function() {
// Improved Event Delegation
    $("#set_goods_list").on("click", ".setGoods", function(){
        console.log("Clicked setGoods:", $(this).data('id'));
        $(".setGoods").removeClass("selected");
        $(this).addClass("selected");
        
        var gno = $(this).data('id');
        $("#sel_goods").val(gno);

        loadDetails(gno);
    });

    $("button[name='add_goods_set']").click(function(){
        $("#sel_goods").val(0);
        $("#result_goods").html('');
        $("#s_scode").val('');
        $("#add_goods_modal").dialog({
            width: 400,
            modal: true,
            title: "대표 상품 추가",
            classes: {
                "ui-dialog": "custom-dialog-zindex"
            }
        });
    });

    $("button[name='add_goods_dt']").click(function(){
        var pno = $("#sel_goods").val();
        if(pno > 0){
             $("#result_goods").html('');
             $("#s_scode").val('');
             $("#add_goods_modal").dialog({
                width: 400,
                modal: true,
                title: "세부 상품 추가",
                classes: {
                    "ui-dialog": "custom-dialog-zindex"
                }
            });
        } else {
            alert('선택된 대표 상품이 없습니다.');
        }
    });
});

function loadDetails(gno) {
    console.log("Loading details for:", gno);
    $.ajax({
        type: 'post',
        url: "{{ route('admin.goods.set.detail') }}",
        data: { 
            gno: gno,
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            console.log("Details loaded successfully");
            $('#targetList').html(data);
        },
        error: function(xhr, status, error) {
            console.error("Load failed:", error);
            alert('세부 상품을 불러오는데 실패했습니다: ' + error);
        }
    });
}

function code_ck(){
    var scode = $("#s_scode").val();
    if(!scode){ alert('상품코드를 입력하세요.'); return; }

    $.ajax({
        type: 'post',
        url: "{{ route('admin.goods.set.search') }}",
        data: {
            scode: scode, 
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            var str = '<div style="margin-top:10px;">' + 
                      '[' + data.goods_name + '] <button type="button" class="btn small" onclick="insert_set('+data.goods_seq+');">추가</button>' + 
                      '</div>';
            $('#result_goods').html(str);
        },
        error: function() {
            alert('상품이 존재하지 않습니다.');
        }
    });
}

function insert_set(seq){
    var pno = $("#sel_goods").val();
    var ea = 1;
    
    if(pno > 0) {
        ea = prompt("수량을 입력하세요", "1");
        if(ea === null) return;
    }

    $.ajax({
        type: 'post',
        url: "{{ route('admin.goods.set.store') }}",
        data: {
            seq: seq,
            pno: pno,
            ea: ea,
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            if(data == 'OK'){
                alert('등록 되었습니다.');
                if(pno > 0) {
                    // Refresh detail list only
                    loadDetails(pno);
                    $("#add_goods_modal").dialog("close");
                    // Update child count in left list (optional optimization)
                    var countEl = $("#"+pno+" .scode");
                    // Simple text update might be tricky without full reload or parsing current text.
                    // For now, let's keep it simple. Full reload for Main Set add, partial for Detail add.
                    location.reload(); 
                } else {
                    location.reload();
                }
            } else if(data == 'Double'){
                alert('이미 등록된 상품입니다.');
            } else {
                alert('등록 실패: ' + data);
            }
        }
    });
}

function del_goods(seq){
    if(confirm("삭제 하시겠습니까?")) {
        $.ajax({
            type: 'post',
            url: "{{ route('admin.goods.set.destroy') }}",
            data: {
                seq: seq,
                _token: "{{ csrf_token() }}"
            },
            success: function(data) {
                if(data == 'OK'){
                    alert('삭제 되었습니다.');
                    var pno = $("#sel_goods").val();
                    if(pno > 0) {
                         loadDetails(pno);
                         // Also reload to update counts on left side
                         location.reload();
                    } else {
                        location.reload(); 
                    }
                } else {
                    alert('삭제 실패');
                }
            }
        });
    }
}

function set_excel() {
    alert('Excel Download Not Implemented Yet');
}
</script>
<style>
.custom-dialog-zindex { z-index: 10000 !important; }
</style>
@endsection

@section('custom_js')
<!-- jQuery UI -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
@endsection
