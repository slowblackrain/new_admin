@extends('affiliate.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">제휴처 환경설정 ({{ $site->name }})</h1>
            <p class="mt-1 text-sm text-slate-500">기본 마진율과 배송비 등 상품 연동 시 적용될 공통 정책을 설정합니다.</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('affiliate.settings.category') }}" class="inline-flex items-center justify-center px-4 py-2 border border-slate-300 shadow-sm text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                카테고리 매핑 관리 &rarr;
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-8 rounded-lg bg-emerald-50 p-4 border border-emerald-200">
        <div class="flex">
            <div class="ml-3">
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('affiliate.settings.store') }}" method="POST">
        @csrf
        <input type="hidden" name="site_id" value="{{ $site->id }}">
        
        <div class="bg-white/80 backdrop-blur-xl shadow-sm ring-1 ring-slate-900/5 sm:rounded-xl overflow-hidden mb-8">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold leading-6 text-slate-900 mb-5">계정 및 판매 정책 설정</h3>
                
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 mb-8 border-b border-slate-900/10 pb-8">
                    <div class="sm:col-span-3">
                        <label for="login_id" class="block text-sm font-medium leading-6 text-slate-900">대한판촉 로그인 아이디</label>
                        <div class="mt-2 relative rounded-md shadow-sm">
                            <input type="text" name="login_id" id="login_id" value="{{ old('login_id', $setting->login_id ?? 'dotob2b') }}" class="block w-full rounded-md border-0 py-2.5 pl-3 pr-4 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 transition-all" placeholder="아이디 입력">
                        </div>
                        @error('login_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="login_password" class="block text-sm font-medium leading-6 text-slate-900">대한판촉 로그인 비밀번호</label>
                        <div class="mt-2 relative rounded-md shadow-sm">
                            <input type="password" name="login_password" id="login_password" value="{{ old('login_password', $setting->login_password ?? '0000') }}" class="block w-full rounded-md border-0 py-2.5 pl-3 pr-4 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 transition-all" placeholder="비밀번호 입력">
                        </div>
                        @error('login_password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="margin_rate" class="block text-sm font-medium leading-6 text-slate-900">기본 마진율 (%)</label>
                        <div class="mt-2 relative rounded-md shadow-sm">
                            <input type="number" step="0.01" name="margin_rate" id="margin_rate" value="{{ old('margin_rate', $setting->margin_rate) }}" class="block w-full rounded-md border-0 py-2.5 pl-3 pr-12 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 transition-all" placeholder="0.00">
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="text-slate-500 sm:text-sm">%</span>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">도매토피아 공급가에 설정된 마진율을 가산하여 제휴처 소비자가로 등록됩니다.</p>
                        @error('margin_rate')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="shipping_fee" class="block text-sm font-medium leading-6 text-slate-900">기본 배송비 (원)</label>
                        <div class="mt-2 relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-slate-500 sm:text-sm">₩</span>
                            </div>
                            <input type="number" step="1" name="shipping_fee" id="shipping_fee" value="{{ old('shipping_fee', $setting->shipping_fee) }}" class="block w-full rounded-md border-0 py-2.5 pl-10 pr-4 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 transition-all" placeholder="3000">
                        </div>
                        <p class="mt-2 text-sm text-slate-500">상품 등록 시 기본으로 설정될 배송비입니다.</p>
                        @error('shipping_fee')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-x-6 border-t border-slate-900/10 px-4 py-4 sm:px-8 bg-slate-50/50">
                <button type="submit" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-all duration-200">
                    저장하기
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
