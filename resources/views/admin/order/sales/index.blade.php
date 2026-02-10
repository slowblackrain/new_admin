@extends('admin.layouts.admin')

@section('content')
<style>
    .log_table { width:100%; border:1px solid #535353; }
    .log_msg { text-align:left; padding-left:10px; border-top:1px solid #535353; }
</style>

<div id="page-title-bar-area">
    <div id="page-title-bar">
        <div class="page-title">
            <h2><span class="darkgray">매출증빙 리스트</span></h2>
        </div>
    </div>
</div>

<div class="search-form-container">
    <form name="fromsearch" id="fromsearch" method="get" action="{{ route('admin.order.sales.index') }}"> 
        {{-- Route name needs to be confirmed. Assuming admin.order.sales.index based on plan --}}
        <table class="search-form-table">
            <tr>
                <td width="40">&nbsp;&nbsp;&nbsp;</td>
                <td width="600">
                    <table class="sf-keyword-table">
                        <tr>
                            <td width="136">
                                <input type="text" name="date" id="date" value="{{ $date }}" title="신청년월" class="datepicker" readonly/>
                            </td>
                            <td class="sfk-td-txt">
                                <input type="text" name="keyword" value="{{ $keyword }}" title="주문자명,주문자아이디" placeholder="주문자명,주문자아이디"/>
                            </td>
                            <td class="sfk-td-btn">
                                <button type="submit" class="btn btn-primary"><span>검색</span></button>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
</div>

<ul class="left-btns clearbox" style="margin-top: 20px; list-style: none; padding: 0;">
    <li style="float: left; margin-right: 10px;">
        <span class="btn small"><a href="javascript:change_state('',3);" class="btn btn-warning btn-sm">미연동</a></span>
        <span class="btn small"><a href="javascript:change_state('',4);" class="btn btn-secondary btn-sm">취소</a></span>
        <span class="btn small"><a href="javascript:change_state('',5);" class="btn btn-danger btn-sm">삭제</a></span>
    </li>
    <li style="float: left; line-height: 30px;">
        <div>검색 <b>{{ number_format($data->total()) }}</b>개</div>
    </li>
</ul>

<div class="clearbox"></div>

<div class="clearbox">
    <table class="info-table-style table table-bordered" style="width:100%">
        <thead>
        <tr>
            <th class="its-th-align center" rowspan="3" style="vertical-align: middle;">
                <input type="checkbox" id="checkboxAll" onclick="chk_all();"/>
            </th>
            <th class="its-th-align center" rowspan="2" colspan="2" style="vertical-align: middle;">주문</th>
            <th class="its-th-align center" colspan="6">매출증빙</th>
        </tr>
        <tr>
            <th class="its-th-align center" colspan="2">1. 신청</th>
            <th class="its-th-align center" colspan="2">2. 처리</th>
            <th class="its-th-align center" colspan="2">3. 결과</th>
        </tr>
        <tr>
            <th class="its-th-align center">신청년월</th>
            <th class="its-th-align center">주문자</th>
            <th class="its-th-align center">신청정보</th>
            <th class="its-th-align center">증빙금액</th>
            <th class="its-th-align center">처리확정일</th>
            <th class="its-th-align center">처리방법</th>
            <th class="its-th-align center">전송로그</th>
            <th class="its-th-align center">처리상태</th>
        </tr>
        </thead>
        <tbody class="ltb">
        @if ($data->count() > 0)
            @foreach ($data as $item)
            <tr>
                <td class="its-td-align center">
                    <input type="checkbox" value="{{ $item->sales_id }}" class="checkeds" name="checkeds[]"/>
                </td>

                <td class="its-td-align center">{{ $item->in_date }}</td>

                <td class="its-td-align center">
                    <div class="userinfo" style="padding-left:5px;">
                        {{ $item->user_name }}<br>({{ $item->userid }})
                    </div>
                </td>

                <td class="its-td-align center">
                    <span class="btn small gray">
                        <a href="javascript:openDetail('{{ $item->sales_id }}');" class="btn btn-info btn-sm" style="width:70px;">신청정보</a>
                    </span>
                </td>

                <td class="its-td-align center" style="width:200px;">
                    <table width="150" style="margin:auto;">
                        <tr>
                            <td align="left">공급가 : </td>
                            <td align="right">{{ number_format($item->supply) }}원</td>
                        </tr>
                        <tr>
                            <td align="left">부가세 : </td>
                            <td align="right">{{ number_format($item->surtax) }}원</td>
                        </tr>
                        <tr>
                            <td align="left">합&nbsp;&nbsp;계 : </td>
                            <td align="right">{{ number_format($item->price) }}원</td>
                        </tr>
                    </table>
                </td>
                <td class="its-td-align center">
                    {{ $item->issue_date }}
                </td>
                <td class="its-td-align center">
                @if ($item->state == 1)
                    <span class="btn small gray"><a href="javascript:tax_run({{ $item->sales_id }});" class="btn btn-primary btn-sm" style="width:70px;">연동</a></span>
                    <div class="clearbox" style="height:3px;"></div>
                    <span class="btn small gray"><a href="javascript:change_state({{ $item->sales_id }},3);" class="btn btn-warning btn-sm" style="width:70px;">미연동</a></span>
                    <div class="clearbox" style="height:3px;"></div>
                    <span class="btn small gray"><a href="javascript:change_state({{ $item->sales_id }},4);" class="btn btn-secondary btn-sm" style="width:70px;">취소</a></span>
                @else
                    {{ $item->state_str }}
                @endif
                </td>
                <td class="its-td-align center">
                    <span class="btn small gray"><a href="javascript:send_log({{ $item->sales_id }});" class="btn btn-light btn-sm" style="width:70px; border:1px solid #ddd;">전송로그</a></span>
                </td>
                <td class="its-td-align center">
                    {{ $item->tstep_str }}
                </td>
            </tr>
            @endforeach
        @else
            <tr>
                <td class="its-td-align center" colspan="9">
                    검색정보 없음
                </td>
            </tr>
        @endif
        </tbody>
    </table>
    
    <div style="text-align: center; margin-top: 20px;">
        {{ $data->links() }}
    </div>
</div>

<div id="send_log_modal" style="display:none;" title="전송로그">
    <div id="send_log_content"></div>
</div>

<script>
$(document).ready(function() {
    // Assuming datepicker is available in global assets
    $(".datepicker").datepicker({
        format: 'yyyymm', // Legacy uses yyyymm
        minViewMode: 1,
        language: "ko",
        autoclose: true
    });
});

function chk_all() {
    if($("#checkboxAll").is(":checked")) $(".checkeds").prop("checked", true);
    else $(".checkeds").prop("checked", false);
}

function send_log(seq){
    $.ajax({
        url : '{{ route("admin.order.sales.log") }}', // Need to add this route
        data : {'seq':seq, '_token': '{{ csrf_token() }}'},
        type : 'post',
        dataType: 'json',
        success : function(data) {
            if(data.result == true) 
            {
                var log_html = '<table class="log_table" cellpadding="0" cellspacing="0">';
                log_html += '<tr>';
                log_html += '<td class="log_msg">'+data.log_msg+'</td>';
                log_html += '</tr>';
                log_html += '</table>';

                $("#send_log_content").html(log_html);
                // Simple Alert for now or use modal logic if available
                alert("전송로그: \n" + data.log_msg.replace(/<br>/g, "\n"));
                // Or implementing a modal later.
            } else {
                alert("전송로그가 없습니다.");
            }
        },
        error: function() {
            alert("통신 오류가 발생했습니다.");
        }
    });
}

function change_state(seq, mode)
{
    if(seq == '') {
        var sales_seq = [];
        $('.checkeds:checked').each(function() {
            sales_seq.push($(this).val());
        });
        
        if(sales_seq.length == 0) {
            alert("선택값이 없습니다.");
            return;
        }
        seq = sales_seq.join(',');
    }

    if(mode == 5) {
        if(!confirm("정말로 삭제하시겠습니까?")) return;
    }

    $.ajax({
        url : '{{ route("admin.order.sales.state") }}', // Need to add this route
        data : {'seq':seq, 'mode':mode, '_token': '{{ csrf_token() }}'},
        type : 'post',
        dataType: 'json',
        success : function(data) {
            if(data.result == true) {
                alert("상태가 변경되었습니다.");
                location.reload();
            } else {
                alert("상태변경에 실패하였습니다.");
            }
        },
        error: function() {
            alert("통신 오류가 발생했습니다.");
        }
    });      
}

function tax_run(seq)
{
    if(!confirm("하이웍스로 전송하시겠습니까?")) return;

    $.ajax({
        url : '{{ route("admin.order.sales.hiworks") }}', // Need to add this route
        data : {'seq':seq, '_token': '{{ csrf_token() }}'},
        type : 'post',
        dataType: 'json',
        success : function(data) {
            alert(data.msg);
            location.reload();     
        },
        error: function() {
            alert("통신 오류가 발생했습니다.");
        }
    });
}

function openDetail(id) {
    // Open in new window or modal
    window.open('{{ route("admin.order.sales.show", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', id), 'SalesDetail', 'width=800,height=600,scrollbars=yes');
}
</script>
@endsection
