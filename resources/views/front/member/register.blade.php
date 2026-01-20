@extends('layouts.front')

@section('content')
    <div class="doto-member-bg" style="background: #fff; padding: 50px 0;">
        <div id="doto_join" class="container" style="width: 800px; margin: 0 auto; border: 1px solid #ddd; padding: 40px;">

            <div class="join_tit" style="text-align: center; margin-bottom: 30px;">
                <h2 style="font-size: 24px; font-weight: bold;">회원정보 입력</h2>
            </div>

            <form name="registFrm" method="post" action="{{ route('member.register_process') }}">
                @csrf

                <table class="form-table" style="width: 100%; border-collapse: collapse;">
                    <tr style="border-bottom: 1px solid #eee;">
                        <th style="padding: 15px; width: 150px; text-align: left; background: #f9f9f9;">아이디</th>
                        <td style="padding: 15px;">
                            <input type="text" name="userid" id="userid" style="padding: 5px; width: 200px;">
                            <button type="button" id="btn_check_id" style="padding: 5px 10px;">중복확인</button>
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <th style="padding: 15px; text-align: left; background: #f9f9f9;">비밀번호</th>
                        <td style="padding: 15px;">
                            <input type="password" name="password" style="padding: 5px; width: 200px;">
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <th style="padding: 15px; text-align: left; background: #f9f9f9;">이름</th>
                        <td style="padding: 15px;">
                            <input type="text" name="username" style="padding: 5px; width: 200px;">
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <th style="padding: 15px; text-align: left; background: #f9f9f9;">이메일</th>
                        <td style="padding: 15px;">
                            <input type="email" name="email" style="padding: 5px; width: 300px;">
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <th style="padding: 15px; text-align: left; background: #f9f9f9;">휴대폰</th>
                        <td style="padding: 15px;">
                            <input type="text" name="cellphone" style="padding: 5px; width: 200px;">
                        </td>
                    </tr>
                </table>

                <div class="btn_area" style="text-align: center; margin-top: 30px;">
                    <button type="submit"
                        style="padding: 15px 40px; background: #333; color: #fff; font-size: 16px; border: none; cursor: pointer;">회원가입완료</button>
                    <button type="button" onclick="history.back()"
                        style="padding: 15px 40px; background: #fff; border: 1px solid #ccc; cursor: pointer;">취소</button>
                </div>
            </form>

            @push('scripts')
                <script>
                    $(document).ready(function () {
                        $('#btn_check_id').click(function () {
                            var userid = $('#userid').val();
                            if (!userid) {
                                alert('아이디를 입력해주세요.');
                                return;
                            }

                            $.ajax({
                                url: '{{ route("member.check_id") }}',
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    userid: userid
                                },
                                success: function (response) {
                                    alert(response.msg);
                                },
                                error: function () {
                                    alert('서버 통신 오류가 발생했습니다.');
                                }
                            });
                        });
                    });
                </script>
            @endpush

        </div>
    </div>
@endsection