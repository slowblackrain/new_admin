@extends('layouts.front')

@section('content')
<div class="location_wrap">
    <div class="location_cont">
        <em><a href="/" class="local_home">HOME</a> &gt; 게시판 &gt; {{ $boardConfig->title ?? '게시판' }}</em>
    </div>
</div>

<div class="content_wrap clearbox" style="padding-bottom: 100px;">
    @include('front.mypage.sidebar') {{-- Assuming board is accessed often by members, sidebar is useful --}}

    <div id="board_content" style="float: right; width: 960px;">
        <div class="cart_title_area">
            <h3>{{ $boardConfig->title ?? '글쓰기' }} 작성</h3>
        </div>

        <form action="{{ route('board.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="board_id" value="{{ $boardId }}">

            <table class="form_table" style="width:100%; border-top:2px solid #333; border-collapse:collapse;">
                <colgroup>
                    <col width="150" />
                    <col width="*" />
                </colgroup>
                <tbody>
                    <tr>
                        <th style="background:#f9f9f9; padding:15px; border-bottom:1px solid #ddd; text-align:left;">제목</th>
                        <td style="padding:15px; border-bottom:1px solid #ddd;">
                            <input type="text" name="subject" class="input_text" style="width:100%; padding:5px;" required>
                        </td>
                    </tr>
                    <tr>
                        <th style="background:#f9f9f9; padding:15px; border-bottom:1px solid #ddd; text-align:left;">내용</th>
                        <td style="padding:15px; border-bottom:1px solid #ddd;">
                            <textarea name="contents" style="width:100%; height:300px; padding:10px;" required></textarea>
                        </td>
                    </tr>
                    {{-- File upload can be added here later --}}
                </tbody>
            </table>

            <div class="btn_area_center" style="margin-top:30px; text-align:center;">
                <button type="submit" style="padding:10px 30px; background:#333; color:#fff; border:none; font-weight:bold; cursor:pointer;">등록</button>
                <a href="{{ route('board.index', ['id' => $boardId]) }}" style="padding:10px 30px; background:#fff; color:#333; border:1px solid #ddd; text-decoration:none; font-weight:bold;">취소</a>
            </div>
        </form>
    </div>
</div>
@endsection
