<div class="goods-board-list">
    @if($posts->count() > 0)
        <table class="board-table" style="width:100%; border-top:1px solid #ddd; border-bottom:1px solid #ddd;">
            <thead>
                <tr style="background:#f9f9f9; text-align:center;">
                    <th style="padding:10px; width:10%;">번호</th>
                    <th style="padding:10px;">제목</th>
                    <th style="padding:10px; width:15%;">작성자</th>
                    <th style="padding:10px; width:15%;">작성일</th>
                </tr>
            </thead>
            <tbody>
                @foreach($posts as $post)
                    <tr style="text-align:center; border-top:1px solid #eee;">
                        <td style="padding:10px;">{{ $posts->total() - ($posts->currentPage() - 1) * $posts->perPage() - $loop->index }}</td>
                        <td style="padding:10px; text-align:left;">
                            <a href="{{ route('board.view', ['id' => $boardId, 'seq' => $post->seq]) }}" target="_blank">
                                {{ $post->subject }}
                                @if($post->comment > 0)
                                    <span style="color:red; font-size:11px;">[{{ $post->comment }}]</span>
                                @endif
                            </a>
                        </td>
                        <td style="padding:10px;">{{ $post->name }}</td>
                        <td style="padding:10px;">{{ \Carbon\Carbon::parse($post->r_date)->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-2 text-center">
            {{ $posts->appends(['id' => $boardId, 'goods_seq' => $goodsSeq])->links() }}
        </div>
    @else
        <div style="padding:30px; text-align:center; color:#888;">
            등록된 게시글이 없습니다.
        </div>
    @endif
    
    <div style="text-align:right; margin-top:10px;">
        @auth
            <a href="{{ route('board.write', ['id' => $boardId]) }}" class="button bgblue" style="padding:5px 10px; color:#fff;">글쓰기</a>
        @else
            <a href="{{ route('member.login') }}" class="button bgblue" style="padding:5px 10px; color:#fff;" onclick="alert('로그인이 필요합니다.');">글쓰기</a>
        @endauth
    </div>
</div>
