@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">거래처 등록/수정</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">기본 정보</h3>
                </div>
                <form action="{{ route('admin.scm_basic.trader.save') }}" method="POST">
                    @csrf
                    @if($trader)
                    <input type="hidden" name="trader_seq" value="{{ $trader->trader_seq }}">
                    @endif
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>거래처명</label>
                                    <input type="text" name="trader_name" class="form-control" value="{{ $trader->trader_name ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>거래처 ID</label>
                                    <input type="text" name="trader_id" class="form-control" value="{{ $trader->trader_id ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>거래처 그룹</label>
                                    <select name="trader_group" class="form-control">
                                        <option value="국내" {{ ($trader->trader_group ?? '') == '국내' ? 'selected' : '' }}>국내</option>
                                        <option value="해외" {{ ($trader->trader_group ?? '') == '해외' ? 'selected' : '' }}>해외</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>사용 여부</label>
                                    <select name="trader_use" class="form-control">
                                        <option value="Y" {{ ($trader->trader_use ?? '') == 'Y' ? 'selected' : '' }}>사용</option>
                                        <option value="N" {{ ($trader->trader_use ?? '') == 'N' ? 'selected' : '' }}>미사용</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>대표자명</label>
                                    <input type="text" name="company_owner" class="form-control" value="{{ $trader->company_owner ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>사업자번호</label>
                                    <input type="text" name="regist_number" class="form-control" value="{{ $trader->regist_number ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>전화번호</label>
                                    <input type="text" name="phone_number" class="form-control" value="{{ $trader->phone_number ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>이메일</label>
                                    <input type="email" name="email" class="form-control" value="{{ $trader->email ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>관리자 메모</label>
                            <textarea name="admin_memo" class="form-control" rows="3">{{ $trader->admin_memo ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">저장</button>
                        <a href="{{ route('admin.scm_basic.trader') }}" class="btn btn-default">목록</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
