<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>재입고 알림 신청</title>
    <link rel="stylesheet" href="{{ asset('css/legacy/common.css') }}">
    <style>
        body { padding: 20px; background: #fff; }
        .restock_wrap { max-width: 400px; margin: 0 auto; }
        h1 { font-size: 18px; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .goods_info { margin-bottom: 20px; font-weight: bold; }
        .form_group { margin-bottom: 15px; }
        .form_group label { display: block; margin-bottom: 5px; font-size: 12px; }
        .form_group input { width: 100%; padding: 8px; border: 1px solid #ddd; }
        .btn_area { text-align: center; margin-top: 20px; }
        .btn_submit { background: #333; color: #fff; border: 0; padding: 10px 20px; cursor: pointer; }
        .btn_cancel { background: #ccc; color: #fff; border: 0; padding: 10px 20px; cursor: pointer; }
    </style>
</head>
<body>

<div class="restock_wrap">
    <h1>재입고 알림 신청</h1>

    <div class="goods_info">
        상품명: {{ $goods->goods_name }}
    </div>

    <form action="{{ route('goods.restock.store') }}" method="post">
        @csrf
        <input type="hidden" name="goods_seq" value="{{ $goods->goods_seq }}">
        
        <div class="form_group">
            <label>휴대폰 번호</label>
            <input type="text" name="cellphone" value="{{ $user->cellphone ?? '' }}" placeholder="010-0000-0000" required>
        </div>

        <div class="btn_area">
            <button type="submit" class="btn_submit">신청하기</button>
            <button type="button" class="btn_cancel" onclick="window.close()">닫기</button>
        </div>
    </form>
</div>

</body>
</html>
