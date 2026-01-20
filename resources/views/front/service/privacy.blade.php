@extends('layouts.front')

@section('content')
    <div class="container" style="padding: 50px 0;">
        <h3
            style="font-size: 24px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px;">
            개인정보 처리방침</h3>

        <div
            style="background: #f9f9f9; padding: 30px; border: 1px solid #ddd; min-height: 400px; white-space: pre-wrap; line-height: 1.6;">
            {!! $privacy !!}
        </div>
    </div>
@endsection