@extends('affiliate.layouts.app')

@section('content')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    window.daehanCategories = {!! $daehanCategoriesJson ?? '[]' !!};
    
    document.addEventListener('alpine:init', () => {
        Alpine.data('categorySelect', (catCode, initialCode, initialName) => ({
            open: false,
            search: initialName || '',
            selectedCode: initialCode || '',
            selectedName: initialName || '',
            options: window.daehanCategories,
            
            get fullFilteredOptions() {
                if (this.search === '') {
                    return this.options;
                }
                const q = this.search.toLowerCase();
                return this.options.filter(i => 
                    i.name.toLowerCase().includes(q) || 
                    i.code.includes(q)
                );
            },
            
            get filteredOptions() {
                return this.fullFilteredOptions.slice(0, 100);
            },
            
            get hasMore() {
                return this.fullFilteredOptions.length > 100;
            },
            
            selectItem(item) {
                this.selectedCode = item.code;
                this.selectedName = item.name;
                this.search = item.name;
                this.open = false;
                
                this.saveMapping(item.code, item.name);
            },
            
            clearSelection() {
                this.selectedCode = '';
                this.selectedName = '';
                this.search = '';
                this.open = true;
                this.$refs.searchInput.focus();
                
                this.saveMapping('', '');
            },
            
            async saveMapping(affCode, affName) {
                const token = document.querySelector('input[name="_token"]').value;
                const siteId = document.querySelector('input[name="site_id"]').value;
                
                try {
                    const response = await fetch('{{ route('affiliate.settings.category.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            site_id: siteId,
                            mappings: {
                                [catCode]: affCode
                            },
                            mapping_names: {
                                [catCode]: affName
                            }
                        })
                    });
                    
                    if (!response.ok) {
                        alert('자동 저장 중 서버 오류가 발생했습니다.');
                    }
                } catch (e) {
                    alert('서버와 통신할 수 없습니다.');
                }
            }
        }));
    });
</script>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <style>
        /* 심플한 페이지네이션 커스텀 스타일 */
        .pagination { display: flex; list-style: none; padding: 0; justify-content: center; gap: 0.25rem; margin: 0; flex-wrap: wrap; }
        .pagination .page-item .page-link, .pagination .page-item span { display: block; padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; color: #475569; text-decoration: none; font-size: 0.875rem; }
        .pagination .page-item.active span { background: #3b82f6; color: #fff; border-color: #3b82f6; font-weight: bold; }
        .pagination .page-item.disabled span { color: #cbd5e1; background: #f8fafc; }
        .pagination .page-item .page-link:hover { background: #f1f5f9; }
    </style>
    <div class="mb-8 flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('affiliate.settings.index') }}" class="text-slate-500 hover:text-slate-700 font-medium">
                    &larr; 돌아가기
                </a>
                <h1 class="text-2xl font-bold text-slate-900 ml-4">카테고리 매핑 설정</h1>
            </div>
            <p class="mt-2 text-sm text-slate-500">도매토피아의 카테고리를 대한판촉 카테고리 코드와 수동 연결하거나, 자동 매핑을 관리합니다.</p>
        </div>
        
        <form action="{{ route('affiliate.settings.category.auto') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all cursor-pointer">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
                이름 기반 자동 매핑 실행
            </button>
        </form>
    </div>

    <div class="mb-6 flex space-x-2 border-b border-gray-200 pb-4 justify-between items-center">
        <div class="flex space-x-2">
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'all', 'page' => 1]) }}" 
               class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-colors border {{ request('filter', 'all') == 'all' ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' }}">
                전체 리스트
                <span class="ml-2 inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold {{ request('filter', 'all') == 'all' ? 'bg-white text-slate-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ number_format($counts['all'] ?? 0) }}
                </span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'mapped', 'page' => 1]) }}" 
               class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-colors border {{ request('filter') == 'mapped' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' }}">
                매핑 완료
                <span class="ml-2 inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold {{ request('filter') == 'mapped' ? 'bg-white text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                    {{ number_format($counts['mapped'] ?? 0) }}
                </span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'unmapped', 'page' => 1]) }}" 
               class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-colors border {{ request('filter') == 'unmapped' ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' }}">
                미매핑
                <span class="ml-2 inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold {{ request('filter') == 'unmapped' ? 'bg-white text-amber-700' : 'bg-slate-100 text-slate-600' }}">
                    {{ number_format($counts['unmapped'] ?? 0) }}
                </span>
            </a>
        </div>
        
        <div>
            <form id="categoryFilterForm" method="GET" action="{{ route('affiliate.settings.category') }}" class="flex items-center">
                <input type="hidden" name="filter" value="{{ request('filter', 'all') }}">
                <select name="category_code" onchange="document.getElementById('categoryFilterForm').submit()" class="block w-64 rounded-md border-0 py-1.5 pl-3 pr-10 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    <option value="">대분류 필터 (전체보기)</option>
                    @foreach($syncCategories as $cat)
                    <option value="{{ $cat->category_code }}" {{ $selectedCategory == $cat->category_code ? 'selected' : '' }}>
                        {{ $cat->title }}
                    </option>
                    @endforeach
                </select>
            </form>
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

    <div id="mappingForm">
        @csrf
        <input type="hidden" name="site_id" value="{{ $site->id }}">

        <div class="bg-white/80 backdrop-blur-xl shadow-sm ring-1 ring-slate-900/5 sm:rounded-xl overflow-hidden mb-8">
            <div class="px-4 py-5 border-b border-slate-200 sm:px-6 flex justify-between items-center bg-slate-50">
                <h3 class="text-base font-semibold leading-6 text-slate-900">카테고리 1:1 매핑 리스트</h3>
                <button type="button" onclick="autoMapCategories()" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    이름 기반 자동 매핑
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-300">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-900 sm:pl-6 w-1/2">도매토피아 카테고리</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900 w-1/2">제휴처 (대한판촉) 카테고리 매핑 검색</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach($categories as $category)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-slate-900 sm:pl-6">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 mr-2 border border-slate-200">
                                        최종 (리프)
                                    </span>
                                    <span class="cat-name">{{ $category->full_name }}</span>
                                    <span class="text-xs text-slate-400 ml-2">({{ $category->category_code }})</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500" colspan="2" 
                                x-data="categorySelect('{{ $category->category_code }}', '{{ $mappings[$category->category_code]->affiliate_category_code ?? '' }}', '{{ $mappings[$category->category_code]->affiliate_category_name ?? '' }}')">
                                
                                <div class="relative w-full max-w-xl">
                                    <input type="hidden" name="mappings[{{ $category->category_code }}]" x-model="selectedCode">
                                    <input type="hidden" name="mapping_names[{{ $category->category_code }}]" x-model="selectedName">
                                    
                                    <div class="relative">
                                        <input type="text" x-model="search" x-ref="searchInput"
                                            @focus="open = true; if(selectedCode) search=''" 
                                            @click.away="open = false; if(!selectedCode) search=''" 
                                            @keydown.escape="open = false" 
                                            @blur="if(selectedCode && search==='') search=selectedName"
                                            class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                                            placeholder="검색어 입력 (예: 모기장, 001001)">
                                            
                                        <!-- X 버튼 (선택 초기화) -->
                                        <button type="button" x-show="selectedCode" @click="clearSelection()" class="absolute inset-y-0 right-0 flex items-center pr-2 text-slate-400 hover:text-slate-600">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                            </svg>
                                        </button>
                                        <!-- 돋보기 아이콘 -->
                                        <div x-show="!selectedCode" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                            </svg>
                                        </div>
                                    </div>
                                        
                                    <div x-cloak x-show="open" class="absolute z-50 mt-1 max-h-60 min-w-full w-[400px] max-w-[90vw] overflow-y-auto overflow-x-hidden rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm border border-slate-200" style="white-space: normal;">
                                        <template x-for="item in filteredOptions" :key="item.code">
                                            <div @click="selectItem(item)" class="relative cursor-pointer select-none py-2 pl-3 pr-4 text-slate-900 hover:bg-indigo-600 hover:text-white group">
                                                <span class="block font-medium text-sm group-hover:text-white leading-relaxed" x-text="item.name" style="white-space: normal; word-break: keep-all; overflow-wrap: break-word;"></span>
                                                <span class="block mt-1 text-xs text-slate-500 group-hover:text-indigo-200" x-text="item.code"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0" class="relative cursor-default select-none py-3 pl-3 pr-9 text-slate-500 text-sm">
                                            검색 결과가 없습니다.
                                        </div>
                                        <div x-show="hasMore" class="relative cursor-default select-none py-2.5 pl-3 pr-4 text-indigo-600 text-sm bg-indigo-50 border-t border-indigo-100 font-medium text-center">
                                            결과가 100건이 넘습니다. 검색어를 더 구체적으로 입력해 주세요.
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 py-6 bg-white border-t border-slate-200 sm:px-6">
                {{ $categories->links('pagination::bootstrap-4') }}
            </div>
            
            <div class="flex items-center justify-end gap-x-6 border-t border-slate-900/10 px-4 py-4 sm:px-8 bg-slate-50/50">
                <span class="text-sm text-slate-500">※ 드롭다운에서 선택 시 실시간으로 즉시 자동 저장됩니다.</span>
            </div>
        </div>
</div>

<script>
function autoMapCategories() {
    // 실제 대한판촉 카테고리 데이터가 있을 경우 프론트엔드에서 매핑하거나,
    // 백엔드로 AJAX 요청을 보내 자동 매핑을 수행할 수 있습니다.
    // 임시로 이름 기반 데모를 보여주는 alert
    alert('이름과 매칭되는 대한판촉 카테고리를 서버에서 조회하여 자동으로 입력합니다. (실제 대한판촉 API/HTML 크롤링 연동 시 동작하게 됩니다.)');
}
</script>
@endsection
