@extends('admin.layouts.admin')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">이벤트 {{ $event ? '수정' : '등록' }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.event.catalog') }}" class="btn btn-secondary">목록으로</a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <form action="{{ route('admin.event.process') }}" method="POST">
                @csrf
                <input type="hidden" name="event_seq" value="{{ $event ? $event->event_seq : '' }}">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">이벤트(기획전) 기본 정보</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-sm search-table">
                            <colgroup>
                                <col width="150" style="background-color: #f4f6f9;">
                                <col>
                            </colgroup>
                            <tbody>
                                <tr>
                                    <th>이벤트명 <span class="text-danger">*</span></th>
                                    <td>
                                        <input type="text" name="title" class="form-control form-control-sm" style="width: 400px;" required value="{{ $event ? $event->title : '' }}">
                                    </td>
                                </tr>
                                <tr>
                                    <th>진행기간 <span class="text-danger">*</span></th>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <input type="date" name="start_date" class="form-control form-control-sm" style="width: 150px;" required value="{{ $event ? substr($event->start_date, 0, 10) : '' }}">
                                            <span class="mx-2">~</span>
                                            <input type="date" name="end_date" class="form-control form-control-sm" style="width: 150px;" required value="{{ $event ? substr($event->end_date, 0, 10) : '' }}">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>간단소개글</th>
                                    <td>
                                        <input type="text" name="event_introduce" class="form-control form-control-sm" style="width: 500px;" value="{{ $event ? $event->event_introduce : '' }}">
                                    </td>
                                </tr>
                                <tr>
                                    <th>노출설정</th>
                                    <td>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="display" value="y" {{ ($event && $event->display == 'y') || !$event ? 'checked' : '' }}>
                                            <label class="form-check-label">노출</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="display" value="n" {{ ($event && $event->display == 'n') ? 'checked' : '' }}>
                                            <label class="form-check-label">미노출</label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary btn-lg px-5">저장</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
