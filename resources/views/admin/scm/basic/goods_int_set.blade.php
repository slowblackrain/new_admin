@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">기본단가요율설정 (Goods Rate)</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-dark" onclick="scmSubmit();">저장하기</button>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form name="detailForm" id="detailForm" method="post" action="{{ route('admin.scm_basic.save_goods_int_set') }}">
                @csrf
                
                <!-- Season Stats -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">시즌통계 설정</h3></div>
                    <div class="card-body">
                         <div class="form-row">
                             <label class="col-sm-1 col-form-label">비율 설정</label>
                             <div class="col-sm-11 form-inline">
                                 매출 : <input type="text" name="price" value="{{ $data['price'] ?? 0 }}" class="form-control form-control-sm mx-1" style="width:80px">
                                 판건 : <input type="text" name="order_cnt" value="{{ $data['order_cnt'] ?? 0 }}" class="form-control form-control-sm mx-1" style="width:80px">
                                 판량 : <input type="text" name="order_ea" value="{{ $data['order_ea'] ?? 0 }}" class="form-control form-control-sm mx-1" style="width:80px">
                                 클릭 : <input type="text" name="view" value="{{ $data['view'] ?? 0 }}" class="form-control form-control-sm mx-1" style="width:80px">
                             </div>
                         </div>
                    </div>
                </div>

                <!-- Basic Data -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">기초자료설정</h3></div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">환율및 기본값</label>
                            <div class="col-sm-10">
                                <div class="form-inline mb-2">
                                    <span style="width:80px">환율:</span>
                                    <input type="text" name="exchange" value="{{ $data['exchange'] ?? 0 }}" class="form-control form-control-sm">
                                </div>
                                <div class="form-inline mb-2">
                                    <span style="width:80px">운송비:</span>
                                    <input type="text" name="transport" value="{{ $data['transport'] ?? 0 }}" class="form-control form-control-sm">
                                </div>
                                <div class="form-inline mb-2">
                                    <span style="width:80px">관부가세:</span>
                                    <input type="text" name="customs" value="{{ $data['customs'] ?? 0 }}" class="form-control form-control-sm">
                                </div>
                                <div class="form-inline mb-2">
                                    <span style="width:80px">부수비용:</span>
                                    <input type="text" name="incidental" value="{{ $data['incidental'] ?? 0 }}" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="form-group row">
                             <label class="col-sm-2 col-form-label">수량단계별 마진</label>
                             <div class="col-sm-10">
                                <table class="table table-sm table-bordered text-center">
                                    <thead>
                                        <tr>
                                            <th>단계</th>
                                            <th>도토상품</th>
                                            <th>국내상품</th>
                                            <th>XTS상품</th>
                                            <th>참고 (대상코드/도착가 Test)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>단계①</td>
                                            <td><input type="text" name="step_one" value="{{ $data['step_one'] ?? 0 }}" class="form-control form-control-sm"></td>
                                            <td><input type="text" name="gtk_step_one" value="{{ $data['gtk_step_one'] ?? 0 }}" class="form-control form-control-sm"></td>
                                            <td><input type="text" name="xt_step_one" value="{{ $data['xt_step_one'] ?? 0 }}" class="form-control form-control-sm"></td>
                                            <td rowspan="4" class="align-middle text-left bg-light">
                                                * 테스트 기능 (UI만 구현됨)<br>
                                                대상코드: <input type="text" size="5"> <br>
                                                도착가: <input type="text" size="8"> <br>
                                                = 결과값 ...
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>단계②</td>
                                            <td><input type="text" name="step_two" value="{{ $data['step_two'] ?? 0 }}" class="form-control form-control-sm"></td>
                                            <td><input type="text" name="gtk_step_two" value="{{ $data['gtk_step_two'] ?? 0 }}" class="form-control form-control-sm"></td>
                                            <td><input type="text" name="xt_step_two" value="{{ $data['xt_step_two'] ?? 0 }}" class="form-control form-control-sm"></td>
                                        </tr>
                                        <tr>
                                            <td>단계③</td>
                                            <td><input type="text" name="step_three" value="{{ $data['step_three'] ?? 0 }}" class="form-control form-control-sm"></td>
                                            <td><input type="text" name="gtk_step_three" value="{{ $data['gtk_step_three'] ?? 0 }}" class="form-control form-control-sm"></td>
                                            <td><input type="text" name="xt_step_three" value="{{ $data['xt_step_three'] ?? 0 }}" class="form-control form-control-sm"></td>
                                        </tr>
                                        <tr>
                                            <td>단계④</td>
                                            <td><input type="text" name="step_four" value="{{ $data['step_four'] ?? 0 }}" class="form-control form-control-sm"></td>
                                            <td><input type="text" name="gtk_step_four" value="{{ $data['gtk_step_four'] ?? 0 }}" class="form-control form-control-sm"></td>
                                            <td><input type="text" name="xt_step_four" value="{{ $data['xt_step_four'] ?? 0 }}" class="form-control form-control-sm"></td>
                                        </tr>
                                        <tr>
                                            <td>AT상품</td>
                                            <td colspan="3"><input type="text" name="step_ad" value="{{ $data['step_ad'] ?? 0 }}" class="form-control form-control-sm"></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Rate Settings -->
                <div class="card">
                    <div class="card-header"><h3 class="card-title">각 계열 단계별 요율 및 수량 설정</h3></div>
                    <div class="card-body">
                        
                        <!-- XTS -->
                        <div class="form-group row border-bottom pb-3">
                            <label class="col-sm-2 col-form-label">XTS (금액별수량)</label>
                            <div class="col-sm-10">
                                <table class="table table-borderless table-sm">
                                    <tbody id="xtOverEdit">
                                        @forelse($xt_list as $k => $v)
                                        <tr>
                                            <td>
                                                <input type="text" name="xs[]" value="{{ $v['xs'] }}" size="7" class="text-right">원 ~ 
                                                <input type="text" name="xe[]" value="{{ $v['xe'] }}" size="7" class="text-right">원 →
                                                <span class="text-primary font-weight-bold">단계①</span> <input type="text" name="xt_one_ea[]" value="{{ $v['xt_one_ea'] }}" size="2" class="text-right">EA
                                                <span class="text-primary font-weight-bold">②</span> <input type="text" name="xt_two_ea[]" value="{{ $v['xt_two_ea'] }}" size="2" class="text-right">EA
                                                <span class="text-primary font-weight-bold">③</span> <input type="text" name="xt_three_ea[]" value="{{ $v['xt_three_ea'] }}" size="2" class="text-right">EA
                                                <span class="text-primary font-weight-bold">④</span> <input type="text" name="xt_four_ea[]" value="{{ $v['xt_four_ea'] }}" size="2" class="text-right">EA
                                            </td>
                                            <td>
                                                @if($k == 0)
                                                    <a href="javascript:xt_addOver()" class="btn btn-xs btn-info">[추가]</a>
                                                @else
                                                    <a href="javascript:void(0)" onclick="xt_delOver(this)" class="btn btn-xs btn-danger">[제거]</a>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td>
                                                 <input type="text" name="xs[]" value="0" size="7" class="text-right">원 ~ 
                                                <input type="text" name="xe[]" value="0" size="7" class="text-right">원 →
                                                <span class="text-primary font-weight-bold">단계①</span> <input type="text" name="xt_one_ea[]" value="1" size="2" class="text-right">EA
                                                <span class="text-primary font-weight-bold">②</span> <input type="text" name="xt_two_ea[]" value="1" size="2" class="text-right">EA
                                                <span class="text-primary font-weight-bold">③</span> <input type="text" name="xt_three_ea[]" value="1" size="2" class="text-right">EA
                                                <span class="text-primary font-weight-bold">④</span> <input type="text" name="xt_four_ea[]" value="1" size="2" class="text-right">EA
                                            </td>
                                            <td><a href="javascript:xt_addOver()" class="btn btn-xs btn-info">[추가]</a></td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- GTS -->
                        <div class="form-group row border-bottom pb-3">
                            <label class="col-sm-2 col-form-label">GTS/GKS (금액별수량)</label>
                            <div class="col-sm-10">
                                <table class="table table-borderless table-sm">
                                    <tbody id="tbOverIn">
                                        @forelse($gtx_list as $k => $v)
                                        <tr>
                                            <td>
                                                <input type="text" name="s1[]" value="{{ $v['s1'] }}" size="7" class="text-right">원 ~ 
                                                <input type="text" name="e1[]" value="{{ $v['e1'] }}" size="7" class="text-right">원 →
                                                <span class="text-primary font-weight-bold">단계①</span> <input type="text" name="one_ea[]" value="{{ $v['one_ea'] }}" size="2" class="text-right">EA
                                                <span class="text-primary font-weight-bold">②</span> <input type="text" name="two_ea[]" value="{{ $v['two_ea'] }}" size="2" class="text-right">EA
                                                <span class="text-primary font-weight-bold">③</span> <input type="text" name="three_ea[]" value="{{ $v['three_ea'] }}" size="2" class="text-right">EA
                                                <span class="text-primary font-weight-bold">④</span> <input type="text" name="four_ea[]" value="{{ $v['four_ea'] }}" size="2" class="text-right">EA
                                            </td>
                                            <td>
                                                @if($k == 0)
                                                    <a href="javascript:addOver()" class="btn btn-xs btn-info">[추가]</a>
                                                @else
                                                    <a href="javascript:void(0)" onclick="delOver(this)" class="btn btn-xs btn-danger">[제거]</a>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                          <tr>
                                            <td>
                                                <input type="text" name="s1[]" value="0" size="7" class="text-right">원 ~ 
                                                <input type="text" name="e1[]" value="0" size="7" class="text-right">원 →
                                                <span class="text-primary font-weight-bold">단계①</span> <input type="text" name="one_ea[]" value="1" size="2" class="text-right">EA
                                                <span class="text-primary font-weight-bold">②</span> <input type="text" name="two_ea[]" value="1" size="2" class="text-right">EA
                                                <span class="text-primary font-weight-bold">③</span> <input type="text" name="three_ea[]" value="1" size="2" class="text-right">EA
                                                <span class="text-primary font-weight-bold">④</span> <input type="text" name="four_ea[]" value="1" size="2" class="text-right">EA
                                            </td>
                                            <td><a href="javascript:addOver()" class="btn btn-xs btn-info">[추가]</a></td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                         <!-- ATS -->
                        <div class="form-group row border-bottom pb-3">
                            <label class="col-sm-2 col-form-label">ATS (금액별할인)</label>
                            <div class="col-sm-10">
                                <table class="table table-borderless table-sm">
                                    <tbody id="atOverEdit">
                                        @forelse($at_list as $k => $v)
                                        <tr>
                                            <td>
                                                <input type="text" name="as[]" value="{{ $v['as'] }}" size="7" class="text-right">원 ~ 
                                                <input type="text" name="ae[]" value="{{ $v['ae'] }}" size="7" class="text-right">원 →
                                                <span class="text-primary font-weight-bold">할인율①</span> <input type="text" name="at_dc[]" value="{{ $v['at_dc'] }}" size="3" class="text-right">%
                                            </td>
                                            <td>
                                                @if($k == 0)
                                                    <a href="javascript:at_addOver()" class="btn btn-xs btn-info">[추가]</a>
                                                @else
                                                    <a href="javascript:void(0)" onclick="at_delOver(this)" class="btn btn-xs btn-danger">[제거]</a>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                         <tr>
                                            <td>
                                                <input type="text" name="as[]" value="0" size="7" class="text-right">원 ~ 
                                                <input type="text" name="ae[]" value="0" size="7" class="text-right">원 →
                                                <span class="text-primary font-weight-bold">할인율①</span> <input type="text" name="at_dc[]" value="0" size="3" class="text-right">%
                                            </td>
                                            <td><a href="javascript:at_addOver()" class="btn btn-xs btn-info">[추가]</a></td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                         <!-- GT -->
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">GT (금액별할인)</label>
                            <div class="col-sm-10">
                                <table class="table table-borderless table-sm">
                                    <tbody id="gtOverEdit">
                                        @forelse($gt_list as $k => $v)
                                        <tr>
                                            <td>
                                                <input type="text" name="gs[]" value="{{ $v['gs'] }}" size="7" class="text-right">원 ~ 
                                                <input type="text" name="ge[]" value="{{ $v['ge'] }}" size="7" class="text-right">원 →
                                                <span class="text-primary font-weight-bold">할인①</span> <input type="text" name="gt_dc1[]" value="{{ $v['gt_dc1'] }}" size="3" class="text-right">%
                                                <span class="text-primary font-weight-bold">②</span> <input type="text" name="gt_dc2[]" value="{{ $v['gt_dc2'] }}" size="3" class="text-right">%
                                                <span class="text-primary font-weight-bold">③</span> <input type="text" name="gt_dc3[]" value="{{ $v['gt_dc3'] }}" size="3" class="text-right">%
                                            </td>
                                            <td>
                                                @if($k == 0)
                                                    <a href="javascript:gt_addOver()" class="btn btn-xs btn-info">[추가]</a>
                                                @else
                                                    <a href="javascript:void(0)" onclick="gt_delOver(this)" class="btn btn-xs btn-danger">[제거]</a>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td>
                                                <input type="text" name="gs[]" value="0" size="7" class="text-right">원 ~ 
                                                <input type="text" name="ge[]" value="0" size="7" class="text-right">원 →
                                                <span class="text-primary font-weight-bold">할인①</span> <input type="text" name="gt_dc1[]" value="0" size="3" class="text-right">%
                                                <span class="text-primary font-weight-bold">②</span> <input type="text" name="gt_dc2[]" value="0" size="3" class="text-right">%
                                                <span class="text-primary font-weight-bold">③</span> <input type="text" name="gt_dc3[]" value="0" size="3" class="text-right">%
                                            </td>
                                            <td><a href="javascript:gt_addOver()" class="btn btn-xs btn-info">[추가]</a></td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

            </form>
        </div>
    </section>
</div>

<script>
    function scmSubmit() {
        document.getElementById('detailForm').submit();
    }

    // Dynamic Row Scripts - Ported from Legacy
    // XTS
    function xt_addOver() {
        var table = document.getElementById('xtOverEdit');
        var row = table.insertRow(-1);
        var cell1 = row.insertCell(0);
        var cell2 = row.insertCell(1);

        // Copy innerHTML from first row but reset values
        cell1.innerHTML = table.rows[0].cells[0].innerHTML;
        // Simple Reset for Inputs
        $(cell1).find('input').val(0);
        $(cell1).find('input[name^="xt_"]').val(1);
        
        cell2.innerHTML = '<a href="javascript:void(0)" onclick="xt_delOver(this)" class="btn btn-xs btn-danger">[제거]</a>';
    }
    function xt_delOver(obj) {
        var row = obj.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }

    // GTS
    function addOver() { // Legacy name 'addOver' for GTS
        var table = document.getElementById('tbOverIn');
        var row = table.insertRow(-1);
        var cell1 = row.insertCell(0);
        var cell2 = row.insertCell(1);

        cell1.innerHTML = table.rows[0].cells[0].innerHTML;
        $(cell1).find('input').val(0);
        $(cell1).find('input[name$="_ea[]"]').val(1);

        cell2.innerHTML = '<a href="javascript:void(0)" onclick="delOver(this)" class="btn btn-xs btn-danger">[제거]</a>';
    }
    function delOver(obj) {
         var row = obj.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }

    // ATS
    function at_addOver() {
        var table = document.getElementById('atOverEdit');
         var row = table.insertRow(-1);
        var cell1 = row.insertCell(0);
        var cell2 = row.insertCell(1);

        cell1.innerHTML = table.rows[0].cells[0].innerHTML;
        $(cell1).find('input').val(0);

        cell2.innerHTML = '<a href="javascript:void(0)" onclick="at_delOver(this)" class="btn btn-xs btn-danger">[제거]</a>';
    }
    function at_delOver(obj) {
         var row = obj.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }

    // GT
    function gt_addOver() {
        var table = document.getElementById('gtOverEdit');
         var row = table.insertRow(-1);
        var cell1 = row.insertCell(0);
        var cell2 = row.insertCell(1);

        cell1.innerHTML = table.rows[0].cells[0].innerHTML;
        $(cell1).find('input').val(0);

        cell2.innerHTML = '<a href="javascript:void(0)" onclick="gt_delOver(this)" class="btn btn-xs btn-danger">[제거]</a>';
    }
    function gt_delOver(obj) {
         var row = obj.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }
</script>
@endsection
