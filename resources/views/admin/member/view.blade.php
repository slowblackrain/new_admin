@extends('admin.layouts.admin')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>회원 상세 정보</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.member.catalog') }}">회원 관리</a></li>
                    <li class="breadcrumb-item active">회원 상세</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Customer Info -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user"></i> 고객 정보</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-sm">
                    <colgroup>
                        <col style="width: 15%">
                        <col style="width: 35%">
                        <col style="width: 15%">
                        <col style="width: 35%">
                    </colgroup>
                    <tbody>
                        <tr>
                            <th class="bg-light text-center">가입일</th>
                            <td>{{ $member->regist_date }}</td>
                            <th class="bg-light text-center">상태</th>
                            <td>
                                @if($member->status == 'done') <span class="badge badge-success">승인</span>
                                @elseif($member->status == 'hold') <span class="badge badge-warning">대기</span>
                                @elseif($member->status == 'withdrawal') <span class="badge badge-danger">탈퇴</span>
                                @else {{ $member->status }} @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light text-center">아이디</th>
                            <td>{{ $member->userid }}</td>
                            <th class="bg-light text-center">등급</th>
                            <td>{{ $member->group_name }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-center">이름</th>
                            <td>{{ $member->user_name }}</td>
                            <th class="bg-light text-center">닉네임</th>
                            <td>{{ $member->nickname }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-center">이메일</th>
                            <td>{{ $member->email }}</td>
                            <th class="bg-light text-center">휴대폰</th>
                            <td>{{ $member->cellphone }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-center">전화번호</th>
                            <td>{{ $member->phone }}</td>
                            <th class="bg-light text-center">생년월일</th>
                            <td>{{ $member->birthday }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-center">주소</th>
                            <td colspan="3">
                                [{{ $member->zipcode }}] {{ $member->address_street }} {{ $member->address_detail }}
                                @if($member->address) <br> (지번) {{ $member->address }} @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 30-Day Activity Summary -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> 고객 데이터 (최근 30일)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-sm">
                     <colgroup>
                        <col style="width: 20%">
                        <col style="width: 10%">
                        <col>
                    </colgroup>
                    <tbody>
                        <tr>
                            <td class="bg-light text-center">입금대기 (15)</td>
                            <td class="text-center">{{ $orderCounts[15] ?? 0 }}건</td>
                            <td>
                                @foreach($orderReady as $order)
                                    <a href="{{ route('admin.order.view', $order->order_seq) }}" class="badge badge-info">{{ $order->order_seq }}</a>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <td class="bg-light text-center">미처리 출고 (25~)</td>
                            <td class="text-center">{{ count($exportReady) }}건</td>
                            <td>
                                @foreach($exportReady as $order)
                                    <a href="{{ route('admin.order.view', $order->order_seq) }}" class="badge badge-warning">{{ $order->order_seq }}</a>
                                @endforeach
                            </td>
                        </tr>
                        <!-- Additional Rows (Returns/Refunds/QnA) can be added here fully later -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mb-3">
             <a href="{{ route('admin.member.catalog') }}" class="btn btn-secondary">목록으로</a>
        </div>
    </div>
</section>
@endsection
