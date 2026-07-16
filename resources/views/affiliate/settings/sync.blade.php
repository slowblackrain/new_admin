@extends('affiliate.layouts.app')

@section('content')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="syncManager()">
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-slate-900 sm:truncate sm:text-3xl sm:tracking-tight">
                상품 대량 동기화 (Batch)
            </h2>
            <p class="mt-2 text-sm text-slate-500">
                카테고리가 매핑된 도매토피아 상품들을 대한판촉으로 자동 전송합니다. 카테고리를 지정하여 특정 분류만 동기화할 수도 있습니다.
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0 items-center space-x-3">
            <form id="categoryFilterForm" method="GET" action="{{ route('affiliate.settings.sync') }}" class="flex items-center space-x-2">
                <select name="site_id" onchange="document.getElementById('categoryFilterForm').submit()" class="block w-32 rounded-md border-0 py-1.5 pl-3 pr-8 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    @foreach($sites as $s)
                        <option value="{{ $s->id }}" {{ $site->id == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
                <select name="category_code" onchange="document.getElementById('categoryFilterForm').submit()" class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    <option value="">전체 상품 동기화</option>
                    @foreach($syncCategories as $cat)
                    <option value="{{ $cat->category_code }}" {{ $selectedCategory == $cat->category_code ? 'selected' : '' }}>
                        {{ $cat->title }}
                    </option>
                    @endforeach
                </select>
            </form>
            <button @click="startSync()" :disabled="isSyncing || pendingCount === 0" type="button" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                <svg x-show="isSyncing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="isSyncing ? '동기화 진행 중...' : '동기화 일괄 시작'"></span>
            </button>
            
            <button @click="stopSync()" x-show="isSyncing" type="button" class="ml-3 inline-flex items-center rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 focus-visible:outline transition-all">
                중지
            </button>

            <!-- 기등록 상품 매핑 버튼 -->
            @if($site->name === '오너클랜')
            <button @click="matchExisting()" type="button" class="ml-3 inline-flex items-center rounded-md bg-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-700 focus-visible:outline transition-all">
                전체 연동 상태 갱신 (기등록 매핑)
            </button>
            @endif
        </div>
    </div>

    <!-- 대시보드 통계 카드 -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="relative overflow-hidden rounded-xl bg-white px-4 py-5 shadow-sm hover:shadow-md transition-all hover:-translate-y-1 sm:p-6 border border-slate-200">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-slate-400 to-slate-500"></div>
            <dt class="truncate text-sm font-medium text-slate-500">전체 대상 상품</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight text-slate-900" x-text="Number(totalCount).toLocaleString()">{{ number_format($totalTargetCount) }}</dd>
        </div>
        <div class="relative overflow-hidden rounded-xl bg-white px-4 py-5 shadow-sm hover:shadow-md transition-all hover:-translate-y-1 sm:p-6 border border-slate-200">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
            <dt class="truncate text-sm font-medium text-emerald-600">전송 성공</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight text-emerald-700" x-text="Number(successCount).toLocaleString()">{{ number_format($successCount) }}</dd>
        </div>
        <div class="relative overflow-hidden rounded-xl bg-white px-4 py-5 shadow-sm hover:shadow-md transition-all hover:-translate-y-1 sm:p-6 border border-slate-200">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-400 to-orange-400"></div>
            <dt class="truncate text-sm font-medium text-amber-600">미전송 (대기)</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight text-amber-700" x-text="Number(pendingCount).toLocaleString()">{{ number_format($pendingCount) }}</dd>
        </div>
        <div class="relative overflow-hidden rounded-xl bg-white px-4 py-5 shadow-sm hover:shadow-md transition-all hover:-translate-y-1 sm:p-6 border border-slate-200">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-rose-400 to-red-500"></div>
            <dt class="truncate text-sm font-medium text-rose-600">전송 실패</dt>
            <dd class="mt-2 text-3xl font-bold tracking-tight text-rose-700" x-text="Number(failedCount).toLocaleString()">{{ number_format($failedCount) }}</dd>
        </div>
    </div>

    <!-- 진행 상태 표시 (프로그레스 바) -->
    <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
        <h3 class="text-base font-semibold leading-6 text-slate-800 mb-4">동기화 진행 상태</h3>
        <div class="w-full bg-slate-100 rounded-full h-3 mb-3 overflow-hidden shadow-inner">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-3 rounded-full transition-all duration-500 ease-out" :style="'width: ' + progressPercent + '%'"></div>
        </div>
        <div class="flex justify-between text-sm text-slate-500 font-medium">
            <span x-text="progressPercent + '% 완료'">0% 완료</span>
            <span x-text="Number(successCount + failedCount).toLocaleString() + ' / ' + Number(totalCount).toLocaleString() + ' 처리됨'">0 / 0 처리됨</span>
        </div>
    </div>

    <!-- 실시간 로그 콘솔 -->
    <div class="mb-8 bg-slate-900 rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-800 flex justify-between items-center bg-slate-800">
            <h3 class="text-sm font-semibold text-slate-200 flex items-center">
                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M4 17h16a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                실시간 전송 로그
            </h3>
            <button @click="logs = []" class="text-xs text-slate-400 hover:text-white transition-colors">로그 지우기</button>
        </div>
        <div class="p-4 h-64 overflow-y-auto font-mono text-sm space-y-2" id="logConsole">
            <template x-for="(log, index) in logs" :key="index">
                <div class="flex items-start rounded px-2 py-1.5 transition-all duration-300 ease-out" 
                     :class="{
                         'bg-emerald-900/30 text-emerald-300 border-l-2 border-emerald-500': log.type === 'success', 
                         'bg-rose-900/30 text-rose-300 border-l-2 border-rose-500': log.type === 'error', 
                         'text-slate-300 hover:bg-slate-800/50': log.type === 'info'
                     }"
                     x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)" :style="show ? 'opacity: 1; transform: translateY(0)' : 'opacity: 0; transform: translateY(4px)'">
                    <span class="text-slate-500 mr-3 text-xs mt-0.5 shrink-0" x-text="log.time"></span>
                    
                    <!-- 아이콘 분기 -->
                    <span class="mr-2 shrink-0 mt-0.5">
                        <svg x-show="log.type === 'success'" class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <svg x-show="log.type === 'error'" class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <svg x-show="log.type === 'info'" class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>

                    <span class="break-all" x-html="log.message"></span>
                </div>
            </template>
            <div x-show="logs.length === 0" class="text-slate-500 italic flex items-center justify-center h-full">동기화를 시작하면 여기에 실시간 로그가 표시됩니다...</div>
        </div>
    </div>

    <!-- 상품 상세 리스트 뷰 -->
    <div class="bg-white rounded-lg shadow border border-slate-200 overflow-hidden">
        <div class="px-4 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between bg-slate-50">
            <div class="flex space-x-4">
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'all']) }}" class="text-sm font-medium px-3 py-1.5 rounded-md {{ $tab === 'all' ? 'bg-indigo-100 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">
                    전체 보기
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'pending']) }}" class="text-sm font-medium px-3 py-1.5 rounded-md {{ $tab === 'pending' ? 'bg-amber-100 text-amber-700' : 'text-slate-600 hover:bg-slate-100' }}">
                    미전송 (대기)
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'success']) }}" class="text-sm font-medium px-3 py-1.5 rounded-md {{ $tab === 'success' ? 'bg-emerald-100 text-emerald-700' : 'text-slate-600 hover:bg-slate-100' }}">
                    전송 성공
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'failed']) }}" class="text-sm font-medium px-3 py-1.5 rounded-md {{ $tab === 'failed' ? 'bg-rose-100 text-rose-700' : 'text-slate-600 hover:bg-slate-100' }}">
                    전송 실패
                </a>
            </div>
            <div class="mt-4 sm:mt-0">
                <button @click="syncSelectedItems()" :disabled="selectedItems.length === 0 || isSyncing" type="button" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 disabled:opacity-50">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                    </svg>
                    선택 항목 일괄 전송 (<span x-text="selectedItems.length">0</span>)
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-300">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="relative px-4 py-3.5 sm:px-6 w-12">
                            <input type="checkbox" @change="toggleAll($event)" class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                        </th>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-900 sm:pl-6">상품정보</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">판매가</th>
                        <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-slate-900">상태</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">상세 내용</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">최근 연동</th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right">
                            <span class="sr-only">동작</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($paginatedGoods as $item)
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="relative px-4 py-4 sm:px-6">
                            <input type="checkbox" value="{{ $item->goods_seq }}" x-model="selectedItems" class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                        </td>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                            <div class="font-semibold text-slate-900">{{ $item->goods_name }}</div>
                            <div class="text-slate-500 text-xs mt-0.5">
                                @if($item->goods_code)공급사코드: {{ $item->goods_code }}<span class="mx-1">|</span>@endif 상품코드: {{ $item->goods_scode ?? $item->goods_seq }}
                            </div>
                            <div class="mt-1.5">
                                @if(isset($mappings) && $mappings->has($item->goods_seq))
                                    @php $mapping = $mappings->get($item->goods_seq)->first(); @endphp
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">매핑됨: {{ $mapping->affiliate_category_name }}</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-600/10">매핑 안됨</span>
                                @endif
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-slate-700">
                            {{ number_format($item->price) }}원
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                            @if($item->sync_status === 'success')
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">성공</span>
                            @elseif($item->sync_status === 'failed')
                                <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-800">실패</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">대기</span>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-sm text-slate-500 max-w-xs relative">
                            @if($item->sync_status === 'success')
                                <span class="inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ $site->name }} 코드: <strong class="text-slate-900">{{ $item->affiliate_goods_code }}</strong></span>
                            @elseif($item->sync_status === 'failed')
                                <div class="truncate text-rose-600 cursor-help">{{ Str::limit($item->error_message, 40) }}</div>
                                <!-- 커스텀 툴팁 -->
                                <div class="pointer-events-none absolute left-0 bottom-full mb-2 w-max max-w-xs opacity-0 transition-opacity group-hover:opacity-100 z-10">
                                    <div class="rounded bg-slate-800 px-3 py-2 text-xs text-white shadow-lg break-words whitespace-normal leading-relaxed">
                                        {{ $item->error_message }}
                                    </div>
                                    <div class="absolute -bottom-1 left-4 h-2 w-2 rotate-45 bg-slate-800"></div>
                                </div>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                            {{ $item->last_synced_at ? \Carbon\Carbon::parse($item->last_synced_at)->format('y.m.d H:i') : '-' }}
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <button @click="syncSingleItem('{{ $item->goods_seq }}')" type="button" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md transition-colors">
                                @if($item->sync_status) 재전송 @else 전송 @endif
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">
                            조회된 상품이 없습니다.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($paginatedGoods->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 sm:px-6 bg-slate-50">
            {{ $paginatedGoods->links('pagination::tailwind') }}
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('syncManager', () => ({
            isSyncing: false,
            shouldStop: false,
            
            totalCount: {{ $totalTargetCount }},
            successCount: {{ $successCount }},
            failedCount: {{ $failedCount }},
            pendingCount: {{ $pendingCount }},
            categoryCode: '{{ $selectedCategory ?? '' }}',
            
            logs: [],
            selectedItems: [],
            
            toggleAll(e) {
                if (e.target.checked) {
                    const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
                    this.selectedItems = Array.from(checkboxes).map(cb => cb.value);
                } else {
                    this.selectedItems = [];
                }
            },
            
            async syncSingleItem(goodsSeq) {
                if (this.isSyncing) return;
                
                this.isSyncing = true;
                this.addLog(`단독 동기화 시작: 상품 번호 ${goodsSeq}`, 'info');
                
                await this.callSyncSelectedApi([goodsSeq]);
            },
            
            async syncSelectedItems() {
                if (this.isSyncing || this.selectedItems.length === 0) return;
                
                if (!confirm(`선택한 ${this.selectedItems.length}개 상품을 동기화 하시겠습니까?`)) return;
                
                this.isSyncing = true;
                this.addLog(`일괄 동기화 시작: ${this.selectedItems.length}개 상품`, 'info');
                
                await this.callSyncSelectedApi(this.selectedItems);
            },
            
            async callSyncSelectedApi(seqs) {
                const token = document.querySelector('meta[name="csrf-token"]') ? 
                              document.querySelector('meta[name="csrf-token"]').getAttribute('content') : 
                              '{{ csrf_token() }}';
                              
                try {
                    const response = await fetch('{{ route('affiliate.settings.sync.selected') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            site_id: '{{ $site->id }}',
                            goods_seqs: seqs
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.status === 'ok' && data.results) {
                        data.results.forEach(item => {
                            if (item.success) {
                                this.addLog(`[성공] ${item.goods_name} (Seq: ${item.goods_seq})`, 'success');
                            } else {
                                this.addLog(`[실패] ${item.goods_name} - 사유: ${item.message}`, 'error');
                            }
                        });
                        
                        this.addLog('<b>선택 상품 동기화가 완료되었습니다. 페이지를 새로고침합니다.</b>', 'info');
                        setTimeout(() => {
                            let successCount = data.results.filter(i => i.success).length;
                            let failCount = data.results.length - successCount;
                            alert(`동기화가 완료되었습니다.\n성공: ${successCount}건\n실패: ${failCount}건\n\n확인을 누르면 페이지를 새로고침합니다.`);
                            window.location.reload();
                        }, 500);
                    } else {
                        let errorMsg = data.message ? data.message : '서버 응답 오류가 발생했습니다.';
                        this.addLog(errorMsg, 'error');
                        alert('전송 중 오류가 발생했습니다: ' + errorMsg);
                    }
                } catch (e) {
                    this.addLog(`네트워크 또는 서버 오류: ${e.message}`, 'error');
                    alert(`네트워크 또는 서버 오류: ${e.message}`);
                } finally {
                    this.isSyncing = false;
                }
            },
            
            get progressPercent() {
                if (this.totalCount === 0) return 0;
                let processed = this.successCount + this.failedCount;
                return Math.min(100, Math.round((processed / this.totalCount) * 100));
            },
            
            addLog(message, type = 'info') {
                const now = new Date();
                const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                                now.getMinutes().toString().padStart(2, '0') + ':' + 
                                now.getSeconds().toString().padStart(2, '0');
                
                this.logs.push({ time: `[${timeStr}]`, message, type });
                
                // 스크롤 맨 아래로 이동
                setTimeout(() => {
                    const consoleEl = document.getElementById('logConsole');
                    consoleEl.scrollTop = consoleEl.scrollHeight;
                }, 50);
            },
            
            stopSync() {
                this.shouldStop = true;
                this.addLog('동기화 중지 요청이 접수되었습니다. 현재 청크 완료 후 중지됩니다.', 'info');
            },

            async matchExisting() {
                if (!confirm('실제 오너클랜에 등록된 상품 전체 목록을 가져와서 로컬 DB와 매핑합니다.\n시간이 오래 걸릴 수 있습니다. 진행하시겠습니까?')) return;
                
                this.isSyncing = true;
                this.addLog('전체 연동 상태 갱신 (기등록 매핑) 작업을 시작합니다... (최대 수 분 소요)', 'info');

                const token = document.querySelector('meta[name="csrf-token"]') ? 
                              document.querySelector('meta[name="csrf-token"]').getAttribute('content') : 
                              '{{ csrf_token() }}';

                try {
                    const response = await fetch('{{ route('affiliate.settings.sync.match_existing') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            site_id: '{{ $site->id }}'
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.status === 'ok') {
                        this.addLog(`기등록 매핑 완료! 새로 매핑된 상품 수: ${data.matched_count}개`, 'success');
                        setTimeout(() => {
                            alert(`기등록 매핑이 완료되었습니다.\n새로 연동(Success) 처리된 상품: ${data.matched_count}건\n\n확인을 누르면 페이지를 새로고침합니다.`);
                            window.location.reload();
                        }, 500);
                    } else {
                        let errorMsg = data.message ? data.message : '서버 응답 오류가 발생했습니다.';
                        this.addLog(errorMsg, 'error');
                        alert('매핑 중 오류가 발생했습니다: ' + errorMsg);
                    }
                } catch (e) {
                    this.addLog(`네트워크 또는 서버 오류: ${e.message}`, 'error');
                    alert(`네트워크 또는 서버 오류: ${e.message}`);
                } finally {
                    this.isSyncing = false;
                }
            },
            
            async startSync() {
                if (this.pendingCount <= 0) {
                    alert('전송할 상품이 없습니다.');
                    return;
                }
                
                this.isSyncing = true;
                this.shouldStop = false;
                this.addLog('동기화 배치를 시작합니다...', 'info');
                
                await this.processChunk();
            },
            
            async processChunk() {
                if (this.shouldStop) {
                    this.isSyncing = false;
                    this.addLog('동기화가 사용자에 의해 중지되었습니다.', 'error');
                    return;
                }
                
                const token = document.querySelector('meta[name="csrf-token"]') ? 
                              document.querySelector('meta[name="csrf-token"]').getAttribute('content') : 
                              '{{ csrf_token() }}';
                              
                try {
                    const response = await fetch('{{ route('affiliate.settings.sync.chunk') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            site_id: '{{ $site->id }}',
                            category_code: this.categoryCode
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.status === 'done') {
                        this.isSyncing = false;
                        this.addLog(`<b>완료:</b> ${data.message}`, 'success');
                        alert('모든 상품의 동기화가 완료되었습니다!');
                        return;
                    }
                    
                    if (data.status === 'ok' && data.results) {
                        let chunkSuccess = 0;
                        let chunkFail = 0;
                        
                        data.results.forEach(item => {
                            if (item.success) {
                                chunkSuccess++;
                                this.addLog(`[성공] ${item.goods_name} (Seq: ${item.goods_seq})`, 'success');
                            } else {
                                chunkFail++;
                                this.addLog(`[실패] ${item.goods_name} - 사유: ${item.message}`, 'error');
                            }
                        });
                        
                        this.successCount += chunkSuccess;
                        this.failedCount += chunkFail;
                        this.pendingCount = Math.max(0, this.totalCount - this.successCount - this.failedCount);
                        
                        // 테스트 모드 해제: 실제 동작을 위해 연속 호출 복원
                        if (this.pendingCount > 0) {
                            setTimeout(() => {
                                this.processChunk();
                            }, 1000);
                        } else {
                            this.isSyncing = false;
                            this.addLog('<b>모든 상품의 동기화가 완료되었습니다!</b>', 'success');
                            alert(`모든 동기화가 완료되었습니다.\n성공: ${this.successCount}건\n실패: ${this.failedCount}건`);
                        }
                        
                    } else {
                        this.isSyncing = false;
                        let errorMsg = data.message ? data.message : '서버 응답 오류가 발생했습니다.';
                        this.addLog(errorMsg, 'error');
                        alert('전송 중 오류가 발생했습니다: ' + errorMsg);
                    }
                } catch (e) {
                    this.isSyncing = false;
                    this.addLog(`네트워크 또는 서버 오류: ${e.message}`, 'error');
                    alert(`네트워크 또는 서버 오류: ${e.message}`);
                }
            }
        }));
    });
</script>
@endsection
