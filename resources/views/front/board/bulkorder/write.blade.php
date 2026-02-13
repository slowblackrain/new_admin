@extends('layouts.front')

@section('content')
<div class="content-container">
    <h2 class="board-title">{{ $boardConfig->name ?? '대량견적' }}</h2>

    <form action="{{ route('board.store') }}" method="POST">
        @csrf
        <input type="hidden" name="board_id" value="{{ $boardId }}">

        <table class="write-table">
            <tr>
                <th>제목</th>
                <td><input type="text" name="subject" class="input-full" required></td>
            </tr>
            <tr>
                <th>회사명</th>
                <td><input type="text" name="company" class="input-half"></td>
            </tr>
            <tr>
                <th>담당자명</th>
                <td><input type="text" name="person_name" class="input-half"></td>
            </tr>
            <tr>
                <th>연락처</th>
                <td>
                    <input type="text" name="tel1" class="input-short" placeholder="000"> - 
                    <input type="text" name="tel2" class="input-short" placeholder="0000"> - 
                    <input type="text" name="tel3" class="input-short" placeholder="0000">
                    (Legacy DB expects customized fields, adjusting to tel1/tel2 usually)
                    {{-- Actually legacy has tel1, tel2. Let's just use one field for simplicity or split if needed --}}
                </td>
            </tr>
            <tr>
                <th>이메일</th>
                <td><input type="email" name="email" class="input-half"></td>
            </tr>
            <tr>
                <th>내용</th>
                <td><textarea name="contents" class="input-area" required></textarea></td>
            </tr>
        </table>

        <div class="btn-area">
            <button type="submit" class="btn-submit">등록</button>
            <a href="{{ route('board.index', ['id' => $boardId]) }}" class="btn-cancel">취소</a>
        </div>
    </form>
</div>

<style>
    .content-container { padding: 15px; }
    .board-title { font-size: 20px; font-weight: bold; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .write-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .write-table th { width: 120px; border-bottom: 1px solid #eee; padding: 10px; background: #f9f9f9; text-align: left; }
    .write-table td { border-bottom: 1px solid #eee; padding: 10px; }
    .input-full { width: 100%; padding: 5px; box-sizing: border-box; }
    .input-half { width: 50%; padding: 5px; box-sizing: border-box; }
    .input-short { width: 80px; padding: 5px; box-sizing: border-box; }
    .input-area { width: 100%; height: 200px; padding: 5px; box-sizing: border-box; }
    .btn-area { text-align: center; margin-top: 20px; }
    .btn-submit { padding: 10px 30px; background: #333; color: #fff; border: none; cursor: pointer; border-radius: 3px; font-weight: bold; }
    .btn-cancel { padding: 10px 30px; background: #fff; border: 1px solid #ccc; color: #333; text-decoration: none; border-radius: 3px; margin-left: 10px; display: inline-block; }
</style>
@endsection
