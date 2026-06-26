@extends('affiliate.layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center">
    <div class="absolute inset-0 z-0 overflow-hidden">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-blue-400 opacity-20 blur-3xl mix-blend-multiply"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-500 opacity-20 blur-3xl mix-blend-multiply"></div>
    </div>

    <div class="relative z-10 w-full max-w-md px-6">
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl overflow-hidden border border-white/40">
            <div class="px-8 py-10">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                        제휴처 관리
                    </h2>
                    <p class="text-sm text-slate-500 mt-2">Dometopia Affiliate Management</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span class="text-sm text-red-700 font-medium">로그인 정보가 올바르지 않습니다.</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('affiliate.login.post') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label for="manager_id" class="block text-sm font-semibold text-slate-700">아이디</label>
                        <div class="mt-2">
                            <input id="manager_id" name="manager_id" type="text" required value="{{ old('manager_id') }}"
                                class="appearance-none block w-full px-4 py-3 border border-slate-300 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                                placeholder="관리자 아이디를 입력하세요">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700">비밀번호</label>
                        <div class="mt-2">
                            <input id="password" name="password" type="password" required
                                class="appearance-none block w-full px-4 py-3 border border-slate-300 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                                placeholder="비밀번호를 입력하세요">
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded cursor-pointer">
                            <label for="remember" class="ml-2 block text-sm text-slate-600 cursor-pointer">
                                로그인 유지
                            </label>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform transition hover:-translate-y-0.5">
                            로그인
                        </button>
                    </div>
                </form>
            </div>
            <div class="px-8 py-4 bg-slate-50/50 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-400">&copy; {{ date('Y') }} Dometopia. All rights reserved.</p>
            </div>
        </div>
    </div>
</div>
@endsection
