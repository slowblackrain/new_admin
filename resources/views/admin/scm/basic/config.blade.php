@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">재고기초 설정 (Inventory Basics)</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('admin.scm_basic.save_config') }}" method="POST">
                @csrf
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">기본 설정 (SCM Config)</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">기초재고 기초일자</label>
                            <div class="col-sm-4">
                                <input type="date" name="scm_setting_default_date" class="form-control" value="{{ $scmData['set_default_date'] ?? '' }}">
                                <small class="text-muted">재고 수불부의 기준이 되는 일자입니다.</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">기초정산 기초일자</label>
                            <div class="col-sm-4">
                                <input type="date" name="scm_setting_account_date" class="form-control" value="{{ $scmData['set_account_date'] ?? '' }}">
                                <small class="text-muted">거래처 정산(채권/채무)의 기준이 되는 일자입니다.</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">단가 절사 단위</label>
                            <div class="col-sm-4">
                                <select name="truncation_unit" class="form-control">
                                    <option value="1" {{ ($scmData['truncation_unit'] ?? '') == '1' ? 'selected' : '' }}>1원 (절사 없음)</option>
                                    <option value="10" {{ ($scmData['truncation_unit'] ?? '') == '10' ? 'selected' : '' }}>10원 단위 절사</option>
                                    <option value="100" {{ ($scmData['truncation_unit'] ?? '') == '100' ? 'selected' : '' }}>100원 단위 절사</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">환율 및 외화 설정 (Exchange Rates)</h3>
                    </div>
                    <div class="card-body">
                        <!-- Legacy supported multiple currencies. For now, we list common ones. -->
                        @php
                            $currencies = ['USD' => '미국 달러', 'CNY' => '중국 위안', 'JPY' => '일본 엔', 'EUR' => '유로'];
                        @endphp
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>통화</th>
                                    <th>사용여부</th>
                                    <th>환율 (KRW)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($currencies as $code => $name)
                                <tr>
                                    <td>{{ $name }} ({{ $code }})</td>
                                    <td>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="use_{{ $code }}" name="exchange[{{ $code }}_use_status]" value="Y" {{ ($exchangeData[$code.'_use_status'] ?? '') == 'Y' ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="use_{{ $code }}"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="exchange[{{ $code }}_rate]" class="form-control" value="{{ $exchangeData[$code.'_rate'] ?? '' }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">저장 (Save)</button>
                    </div>
                </div>

            </form>
        </div>
    </section>
</div>
@endsection
