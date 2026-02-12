@extends('seller.layouts.app')

@section('title', '내 정보 관리')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark">내 정보 관리</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">내 정보 관리</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">기본 정보 수정</h3>
                </div>
                <form action="{{ route('seller.my.update') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>아이디</label>
                            <input type="text" class="form-control" value="{{ $seller->provider_id }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>업체명</label>
                            <input type="text" class="form-control" value="{{ $seller->provider_name }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>담당자 이메일</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $seller->charge_email) }}" required>
                        </div>
                        <div class="form-group">
                            <label>담당자 연락처</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $seller->charge_tel) }}">
                        </div>
                        
                        <hr>
                        <h5 class="text-muted">비밀번호 변경 (선택사항)</h5>
                        <div class="form-group">
                            <label>새 비밀번호</label>
                            <input type="password" name="password" class="form-control" placeholder="변경시에만 입력하세요">
                        </div>
                        <div class="form-group">
                            <label>새 비밀번호 확인</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="비밀번호 재입력">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">정보 수정</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
