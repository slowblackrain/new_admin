@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>회원/적립금 설정</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">기업(B2B) 가입 승인 정책</h3>
                </div>
                <form action="{{ route('admin.setting.member.save') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label>B2B 회원 가입 승인</label>
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="autoApprove0" name="b2b_auto_approve" value="0" {{ !isset($config->b2b_auto_approve) || $config->b2b_auto_approve == 0 ? 'checked' : '' }}>
                                <label for="autoApprove0" class="custom-control-label">수동 승인 (가입 시 '승인 대기' 상태 부여 - 권장)</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="autoApprove1" name="b2b_auto_approve" value="1" {{ isset($config->b2b_auto_approve) && $config->b2b_auto_approve == 1 ? 'checked' : '' }}>
                                <label for="autoApprove1" class="custom-control-label">자동 승인 (가입 즉시 B2B 등급 자동 부여)</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
