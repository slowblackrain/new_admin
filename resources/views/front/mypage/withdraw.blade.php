@extends('layouts.front')

@section('content')
@push('styles')
<style>
    /* Legacy-style minimal UI for Mypage */
    .mypage_wrap { width: 100%; max-width: 1200px; margin: 0 auto; padding: 40px 15px; display: flex; gap: 30px; }
    .mypage_sidebar { width: 200px; flex-shrink: 0; }
    .mypage_sidebar h2 { font-size: 22px; font-weight: 700; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .mypage_sidebar ul { list-style: none; padding: 0; margin: 0; }
    .mypage_sidebar li { margin-bottom: 1px; }
    .mypage_sidebar a { display: block; padding: 12px 15px; background: #f8f8f8; color: #555; text-decoration: none; font-size: 14px; border: 1px solid #eee; transition: all 0.2s; }
    .mypage_sidebar a:hover, .mypage_sidebar li.active a { background: #333; color: #fff; border-color: #333; }
    
    .mypage_content { flex: 1; min-width: 0; }
    .page_title { font-size: 24px; font-weight: bold; margin-bottom: 20px; color: #111; padding-bottom: 15px; border-bottom: 1px solid #ddd; }
    
    /* Withdrawal Form Styles */
    .withdraw_notice { background: #fff5f5; border: 1px solid #ffcccc; padding: 20px; margin-bottom: 30px; color: #e53e3e; line-height: 1.6; }
    .withdraw_notice h3 { margin-top: 0; margin-bottom: 10px; font-size: 16px; font-weight: bold; }
    .withdraw_notice ul { margin: 0; padding-left: 20px; }
    
    .withdraw_form_area { border-top: 2px solid #333; border-bottom: 1px solid #ddd; padding: 30px 0; }
    .form_row { display: flex; margin-bottom: 15px; align-items: flex-start; }
    .form_row.vertical { flex-direction: column; }
    .form_label { width: 150px; font-weight: bold; padding-top: 8px; flex-shrink: 0; }
    .form_input { flex: 1; }
    
    select.w_select { width: 100%; max-width: 400px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
    textarea.w_textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; height: 100px; margin-top: 10px; display: none; }
    
    .agree_wrap { margin-top: 20px; font-weight: bold; padding: 15px; background: #f9f9f9; text-align: center; border: 1px solid #eee; }
    .agree_wrap input[type="checkbox"] { margin-right: 8px; accent-color: #e53e3e; width: 16px; height: 16px; vertical-align: middle; }
    
    .btn_area { margin-top: 30px; text-align: center; }
    .btn_submit { background: #333; color: #fff; border: none; padding: 15px 40px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
    .btn_submit:hover { background: #111; }
    .btn_cancel { background: #fff; color: #333; border: 1px solid #ccc; padding: 14px 40px; font-size: 16px; font-weight: bold; cursor: pointer; margin-left: 10px; text-decoration: none; display: inline-block; }
    
    .error_msg { color: #e53e3e; font-size: 13px; margin-top: 5px; display: block; }
</style>
@endpush

<div class="mypage_wrap">
    <!-- Sidebar -->
    <div class="mypage_sidebar">
        <h2>마이페이지</h2>
        <ul>
            <li><a href="{{ route('mypage.order.list') }}">주문/배송조회</a></li>
            <li><a href="{{ route('mypage.order.claim') }}">취소/반품/교환 조회</a></li>
            <li><a href="{{ route('mypage.wishlist') }}">관심상품</a></li>
            <li><a href="{{ route('mypage.coupon') }}">쿠폰내역</a></li>
            <li><a href="{{ route('mypage.emoney') }}">예치금내역</a></li>
            <li><a href="{{ route('mypage.point') }}">적립금내역</a></li>
            <li class="active"><a href="{{ route('mypage.withdraw') }}">회원 탈퇴</a></li>
        </ul>
    </div>

    <!-- Content -->
    <div class="mypage_content">
        <h3 class="page_title">회원 탈퇴</h3>

        @if(session('error'))
            <div class="alert alert-danger" style="color:red; margin-bottom:20px;">
                {{ session('error') }}
            </div>
        @endif

        <div class="withdraw_notice">
            <h3>탈퇴 안내</h3>
            <ul>
                <li>탈퇴 시 고객님의 개인정보는 개인정보 처리방침에 따라 즉시 파기되거나 분리 보관됩니다.</li>
                <li>보유중인 쿠폰, 적립금, 예치금 등은 모두 소멸되며 복구할 수 없습니다.</li>
                <li>진행 중인 주문 내역(상품 배송, 교환/반품 등)이 있을 경우 탈퇴가 제한될 수 있습니다.</li>
                <li>동일 아이디나 정보로 일정 기간 재가입이 불가능할 수 있습니다.</li>
            </ul>
        </div>

        <form action="{{ route('mypage.withdraw.process') }}" method="POST" id="withdrawForm">
            @csrf
            <div class="withdraw_form_area">
                <div class="form_row vertical">
                    <div class="form_label">무엇이 불편하셨나요? (탈퇴 사유)</div>
                    <div class="form_input">
                        <select name="reason_code" id="reason_code" class="w_select" required>
                            <option value="">탈퇴 사유를 선택해 주세요.</option>
                            <option value="고객서비스 불만">고객서비스 불만 (상담 지연, 불친절 등)</option>
                            <option value="배송 지연 및 불만">배송 지연 및 배송 상태 불만</option>
                            <option value="상품 가격 불만">상품 가격 불만</option>
                            <option value="상품 품질 불만">상품 품질 불만</option>
                            <option value="교환/환불 지연">교환/환불 지연</option>
                            <option value="사이트 이용 불편">사이트 이용(UI/UX, 속도 등) 불편</option>
                            <option value="이용 빈도 낮음">이용 빈도 낮음 / 재방문 의사 없음</option>
                            <option value="other">기타 (직접 입력)</option>
                        </select>
                        <textarea name="reason_desc" id="reason_desc" class="w_textarea" placeholder="상세 사유를 입력해 주세요 (최대 255자)"></textarea>
                        @error('reason_code')<span class="error_msg">{{ $message }}</span>@enderror
                        @error('reason_desc')<span class="error_msg">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="agree_wrap">
                <label>
                    <input type="checkbox" name="agree" value="1" required>
                    회원 탈퇴로 인한 혜택 소멸 및 데이터 완전 삭제에 동의합니다.
                </label>
                @error('agree')<span class="error_msg">{{ $message }}</span>@enderror
            </div>

            <div class="btn_area">
                <button type="button" class="btn_submit" id="btn_submit">탈퇴하기</button>
                <a href="{{ route('mypage.order.list') }}" class="btn_cancel">취소</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reasonCode = document.getElementById('reason_code');
        const reasonDesc = document.getElementById('reason_desc');
        
        reasonCode.addEventListener('change', function() {
            if (this.value === 'other') {
                reasonDesc.style.display = 'block';
                reasonDesc.setAttribute('required', 'required');
            } else {
                reasonDesc.style.display = 'none';
                reasonDesc.removeAttribute('required');
                reasonDesc.value = '';
            }
        });

        document.getElementById('btn_submit').addEventListener('click', function() {
            if (!document.getElementById('reason_code').value) {
                alert('탈퇴 사유를 선택해 주세요.');
                return;
            }
            if (!document.querySelector('input[name="agree"]').checked) {
                alert('탈퇴 안내 사항에 동의해 주세요.');
                return;
            }
            if (confirm('정말로 탈퇴하시겠습니까? 돌이킬 수 없습니다.')) {
                document.getElementById('withdrawForm').submit();
            }
        });
    });
</script>
@endsection
