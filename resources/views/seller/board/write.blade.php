@extends('seller.layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $boardName }} 작성</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <form action="{{ route('seller.board.store', $boardId) }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="subject">제목</label>
                            <input type="text" name="subject" class="form-control" id="subject" placeholder="제목을 입력하세요" required>
                        </div>
                        <div class="form-group">
                            <label for="contents">내용</label>
                            <textarea name="contents" class="form-control" id="contents" rows="10" placeholder="문의 내용을 입력하세요" required></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">등록</button>
                        <a href="{{ route('seller.board.index', $boardId) }}" class="btn btn-default">취소</a>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection
