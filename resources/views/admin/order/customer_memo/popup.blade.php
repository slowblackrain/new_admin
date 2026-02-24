<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>고객상담 메모 선택</title>
    <!-- Include basic bootstrap for simple styling in popup -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; background-color: #f4f6f9; }
        .memo-row { cursor: pointer; transition: background-color 0.2s; }
        .memo-row:hover { background-color: #e9ecef; }
        .point-memo { font-weight: bold; color: #dc3545; }
    </style>
</head>
<body>

<div class="container-fluid">
    <h4 class="mb-3">상담 메모(템플릿) 선택</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead>
                <tr class="text-center">
                    <th style="width: 80%;">메모 템플릿 내용</th>
                    <th style="width: 20%;">선택</th>
                </tr>
            </thead>
            <tbody>
                @foreach($memos as $memo)
                <tr class="memo-row" onclick="selectMemo('{{ addslashes($memo->memo) }}')">
                    <td class="{{ $memo->point == 'y' ? 'point-memo' : '' }}">
                        {{ $memo->memo }}
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary">선택</button>
                    </td>
                </tr>
                @endforeach
                @if($memos->isEmpty())
                <tr>
                    <td colspan="2" class="text-center">등록된 상담 메모가 없습니다.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<script>
    // 팝업창에서 선택시 부모 창의 특정 Textarea 값을 변경
    function selectMemo(memoText) {
        if(window.opener && !window.opener.closed) {
            // 부모 창의 cs_memo_txt 아이디를 가진 텍스트 박스를 찾습니다.
            var targetObj = window.opener.document.getElementById('admin_memo_txt');
            if (targetObj) {
                // 기존 내용이 있으면 줄바꿈 후 삽입, 없으면 그냥 삽입
                var existVal = targetObj.value;
                if(existVal.length > 0) {
                    targetObj.value = existVal + '\n' + memoText;
                } else {
                    targetObj.value = memoText;
                }
                // 삽입 후 팝업 닫기
                window.close();
            } else {
                alert('부모 창에서 메모 입력 항목을 찾을 수 없습니다.');
            }
        } else {
            alert('부모 창이 닫혀있거나 접근할 수 없습니다.');
        }
    }
</script>
</body>
</html>
