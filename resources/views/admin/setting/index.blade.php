@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>환경설정</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>Basic</h3>
                            <p>상점 기본 정보</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <a href="{{ route('admin.setting.basic') }}" class="small-box-footer">Go <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- Add more boxes for other settings -->
                 <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>Policy</h3>
                            <p>운영/판매 정책</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <a href="{{ route('admin.setting.operating') }}" class="small-box-footer">Go <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
