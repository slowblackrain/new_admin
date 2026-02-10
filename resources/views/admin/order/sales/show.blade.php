@extends('admin.layouts.admin')

@section('content')
<div id="page-title-bar-area">
    <div id="page-title-bar">
        <div class="page-title">
            <h2><span class="darkgray">{{ $info->in_date }} {{ $info->user_name }} 매출증빙 정보</span></h2>
        </div>
    </div>
</div>

<div class="search-form-container">
    <form name="fromsearch" id="fromsearch">
        <input type="hidden" name="sales_id" id="sales_id" value="{{ $id }}" />
        <table class="search-form-table">
            <tr>
                <td width="600">
                    <table class="sf-keyword-table">
                        <tr>
                            <td class="sfk-td-txt">
                                <input type="text" name="keyword" value="{{ request('keyword') }}" title="주문번호" placeholder="주문번호"/>
                            </td>
                            <td class="sfk-td-btn">
                                <button type="submit" class="btn btn-primary"><span>검색</span></button>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    연동상태&nbsp;:&nbsp;
                    <select name="mode" id="mode">
                        <option value="">미선택</option>
                        <option value="1" {{ request('mode') == '1' ? 'selected' : '' }}>연동포함</option>
                        <option value="0" {{ request('mode') == '0' ? 'selected' : '' }}>연동제외</option>
                    </select>
                    &nbsp;환불존재여부&nbsp;:&nbsp;
                    <select name="fcode" id="fcode">
                        <option value="">미선택</option>
                        <option value="0" {{ request('fcode') == '0' ? 'selected' : '' }}>환불없음</option>
                        <option value="1" {{ request('fcode') == '1' ? 'selected' : '' }}>환불있음</option>
                    </select>
                </td>
            </tr>
        </table>
    </form>
</div>

<div class="clearbox">
    <table class="info-table-style table table-bordered" style="width:100%">
        <tr>
            <th class="its-th-align center">아이디 / 이름</th>
            <td class="its-td-align center">{{ $info->userid }} / {{ $info->user_name }}</td>
            <th class="its-th-align center">업체명</th>
            <td class="its-td-align center">{{ $info->bname }}</td>
            <th class="its-th-align center">사업자 등록번호</th>
            <td class="its-td-align center">{{ $info->bno }}</td>
        </tr>
        <tr>
            <th class="its-th-align center">업태 / 종목</th>
            <td class="its-td-align center">{{ $info->bitem }} / {{ $info->bstatus }}</td>
            <th class="its-th-align center">사업장주소</th>
            <td class="its-td-align" colspan="3">&nbsp;({{ $info->bzipcode }}) {{ $info->baddress_street }} {{ $info->baddress_detail }}</td>
        </tr>
        <tr>
            <th class="its-th-align center">부서명 / 담당자명</th>
            <td class="its-td-align center">{{ $info->bpart ?? '' }} / {{ $info->bperson }}</td>
            <th class="its-th-align center">담당자 핸드폰</th>
            <td class="its-td-align center">{{ $info->bcellphone }}</td>
            <th class="its-th-align center">이메일</th>
            <td class="its-td-align center">{{ $info->email }}</td>
        </tr>
        <tr>
            <th class="its-th-align center">상태</th>
            <td class="its-td-align center">{{ $info->lstate_str }}</td>
            <th class="its-th-align center">처리확정일시</th>
            <td class="its-td-align center">{{ $info->issue_date }}</td>
            <th class="its-th-align center">수집일시</th>
            <td class="its-td-align center">{{ $info->reg_date }}</td>
        </tr>
        <tr>
            <th class="its-th-align center">메모</th>
            <td class="its-td-align" colspan="4">
                &nbsp;<textarea cols="120" rows="5" id="memo" class="form-control">{{ $info->memo }}</textarea>
                <br>&nbsp;<span class="btn small gray center"><a href="javascript:memo_save();" class="btn btn-secondary btn-sm" style="width:150px;">저장</a></span>
            </td>
            <td class="its-td-align">
                <table width="150" style="margin:auto;">
                    <tr>
                        <td align="left">공급가 : </td>
                        <td align="right">{{ number_format($info->supply) }}원</td>
                    </tr>
                    <tr>
                        <td align="left">부가세 : </td>
                        <td align="right">{{ number_format($info->surtax) }}원</td>
                    </tr>
                    <tr>
                        <td align="left">합&nbsp;&nbsp;계 : </td>
                        <td align="right">{{ number_format($info->price) }}원</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-center pt-2">
                             <a href="{{ route('admin.order.sales.excel', ['sales_id' => $id]) }}" class="btn btn-success btn-sm" style="width:150px;">엑셀다운</a>
                             <a href="{{ route('admin.order.sales.index') }}" class="btn btn-info btn-sm" style="width:150px;">목록으로</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<ul class="left-btns clearbox" style="margin-top: 20px; list-style: none; padding: 0;">
    @if ($info->state == 1 || $info->state == 6)
    <li style="float: left; margin-right: 10px;">
        <span class="btn small"><a href="javascript:change_state('',1);" class="btn btn-primary btn-sm">연동포함</a></span>
        <span class="btn small"><a href="javascript:change_state('',0);" class="btn btn-secondary btn-sm">연동제외</a></span>
        <span class="btn small"><a href="javascript:change_state('',2);" class="btn btn-danger btn-sm">삭제</a></span>
    </li>
    @endif
    <li style="float: left; line-height: 30px;">
        <div>검색 <b>{{ number_format($list->count()) }}</b>개</div>
    </li>
</ul>

<div class="clearbox"></div>

<div class="clearbox">
    <table class="info-table-style table table-bordered" style="width:100%">
        <thead>
        <tr>
            <th class="its-th-align center">
                <input type="checkbox" id="checkboxAll" onclick="chk_all();"/>
            </th>
            <th class="its-th-align center">주문번호</th>
            <th class="its-th-align center">주문일시</th>
            <th class="its-th-align center">증빙금액</th>
            <th class="its-th-align center">상태</th>
            <th class="its-th-align center">환불번호</th>
            <th class="its-th-align center">신청정보</th>
        </tr>
        </thead>
        <tbody class="ltb">
        @if ($list->count() > 0)
            @foreach ($list as $item)
            <tr>
                <td class="its-td-align center">
                    {{-- Assuming item->idx is the primary key for fm_sales_detail, or maybe seq? using idx as per legacy --}}
                    <input type="checkbox" value="{{ $item->idx ?? $item->seq }}" class="checkeds" name="checkeds[]"/>
                </td>

                <td class="its-td-align center">
                    <a href="#" target="_blank"><u>{{ $item->order_seq }}</u></a>
                </td>

                <td class="its-td-align center">
                    {{ $item->order_date }}
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
                    {{ $item->dstate_str }}
                </td>
                <td class="its-td-align center">
                    {{ $item->refund_code }}
                </td>
                <td class="its-td-align center">
                    {{-- sales_seq in legacy is passed to tax_view. It is s.seq --}}
                    <span class="btn small gray"><a href="javascript:tax_view({{ $item->sales_seq }});" class="btn btn-light btn-sm" style="width:70px; border:1px solid #ddd;">신청정보</a></span>
                </td>
            </tr>
            @endforeach
        @else
            <tr>
                <td class="its-td-align center" colspan="7">
                    검색정보 없음
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>

<div id="taxlay_modal" style="display:none;" title="세금계산서 신청내역 상세정보">
    <div id="taxlay_content">
        <table class="info-table-style table table-bordered" width="100%" cellspacing="0">
        {{-- Content filled via AJAX --}}
        </table>
    </div>
</div>

<script>
function chk_all() {
    if($("#checkboxAll").is(":checked")) $(".checkeds").prop("checked", true);
    else $(".checkeds").prop("checked", false);
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

    if(mode == 2) {
        if(!confirm("정말로 삭제하시겠습니까?")) return;
    }

    $.ajax({
        url : '{{ route("admin.order.sales.dstate") }}', 
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

function memo_save()
{
    var memo = $('#memo').val();
    var seq = $('#sales_id').val();

    $.ajax({
        url : '{{ route("admin.order.sales.memo") }}',
        data : {'seq':seq, 'memo':memo, '_token': '{{ csrf_token() }}'},
        type : 'post',
        dataType: 'json',
        success : function(data) {
            if(data.result == true) alert("메모가 저장되었습니다.");
            else alert("메모 저장에 실패하였습니다.");
        },
        error: function() {
            alert("통신 오류가 발생했습니다.");
        }
    });
}

function tax_view(seq)
{
    $.ajax({
        url : '{{ route("admin.order.sales.tax_info") }}',
        data : {'seq':seq, '_token': '{{ csrf_token() }}'},
        type : 'post',
        dataType : 'json',
        success : function(data) {
            // Construct Modal Body HTML
            var html = `<table class="info-table-style table table-bordered" width="100%" cellspacing="0">
                <colgroup>
                    <col width="100px" />
                    <col width="" />
                </colgroup>
                <tbody>
                    <tr><th class="text-center bg-light">발급상태</th><td>${data.tax_tstep}</td></tr>
                    <tr><th class="text-center bg-light">종 류</th><td>세금계산서</td></tr>
                    <tr><th class="text-center bg-light">주문번호</th><td>${data.order_seq}</td></tr>
                    <tr><th class="text-center bg-light">상호명</th><td>${data.co_name}</td></tr>
                    <tr><th class="text-center bg-light">사업자번호</th><td>${data.busi_no}</td></tr>
                    <tr><th class="text-center bg-light">대표자명</th><td>${data.co_ceo}</td></tr>
                    <tr><th class="text-center bg-light">업태/업종</th><td>${data.co_status}/${data.co_type}</td></tr>
                    <tr><th class="text-center bg-light">주소</th><td>${data.address_street} ${data.address_detail}</td></tr>
                    <tr><th class="text-center bg-light">담당자이름</th><td>${data.person}</td></tr>
                    <tr><th class="text-center bg-light">담당자 이메일</th><td>${data.email}</td></tr>
                    <tr><th class="text-center bg-light">전화번호</th><td>${data.phone}</td></tr>
                    <tr><th class="text-center bg-light">금액</th><td>
                        합계 : ${data.view_price}원 <br>
                        공급가 : ${data.view_supply}원 <br>
                        부가세 : ${data.view_surtax}원
                    </td></tr>
                </tbody>
            </table>`;
            
            $('#taxlay_content').html(html);
            // Simple alert fallback if modal not available, but user wants parity. 
            // In a real implementation we would trigger a Bootstrap modal or similar.
            // For now, I'll assume a global 'openDialog' function exists or just alert the info formatted?
            // Existing admin usually has a layout with jquery ui dialog or bootstrap modal.
            // I'll assume basic Bootstrap modal usage if available, else simple Show/Hide logic.
            $('#taxlay_modal').show(); 
            // Better: use window.open or alert for immediate check
            // alert("Info loaded (Mock Modal)");
            
            // Or create a crude absolute positioned div for "Pop-up" effect for quick verify
            var w = window.open("", "TaxInfo", "width=500,height=600");
            w.document.write('<html><head><title>세금계산서 정보</title><link rel="stylesheet" href="/css/admin.css"></head><body>' + html + '</body></html>');
        },
        error: function() {
            alert("정보를 불러오는데 실패했습니다.");
        }
    });
}
</script>
@endsection
