@extends('admin.layouts.admin')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Super Admin Dashboard</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="m-0">Welcome</h5>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title">도매토피아 관리자 페이지입니다.</h6>
                            <p class="card-text">
                                좌측 메뉴를 사용하여 쇼핑몰 전체를 관리할 수 있습니다.
                            </p>
                            <a href="#" class="btn btn-primary">Go to Order List</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection