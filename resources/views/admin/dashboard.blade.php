<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dometopia Admin</title>
    <!-- Add Tailwind or Bootstrap here if needed -->
</head>

<body>
    <h1>관리자 대시보드</h1>
    <p>환영합니다, 관리자님.</p>

    <div id="stats">
        <h2>현황 요약</h2>
        <p>총 주문 수: {{ number_format($orderCount) }}</p>
        <p>총 회원 수: {{ number_format($memberCount) }}</p>
    </div>
</body>

</html>