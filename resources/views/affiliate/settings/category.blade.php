@extends('affiliate.layouts.app')

@section('content')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    window.affiliateCategories = {!! $affiliateCategoriesJson ?? '{}' !!};
    window.serverMappings = {!! json_encode($mappings ?? []) !!};
    
    document.addEventListener('alpine:init', () => {
        Alpine.data('mappingGrid', () => ({
            sites: {!! $sites->toJson() !!}.map(s => ({ ...s, visible: true })),
            
            toggleSite(siteId) {
                const site = this.sites.find(s => s.id === siteId);
                if (site) {
                    site.visible = !site.visible;
                }
            },
            
            get visibleSites() {
                return this.sites.filter(s => s.visible);
            }
        }));

        window.categorySelectComponent = function(siteId, catCode, initialCode, initialName) {
            let options = window.affiliateCategories[siteId] || [];
            let resolvedName = initialName;
            
            if (initialCode && !initialName) {
                const found = options.find(o => String(o.code) === String(initialCode));
                if (found) {
                    resolvedName = found.name;
                } else {
                    resolvedName = '[매핑코드] ' + initialCode;
                }
            }

            return {
                siteId: siteId,
                editMode: false,
                open: false,
                search: resolvedName || '',
                selectedCode: initialCode || '',
                selectedName: resolvedName || '',
                
                get options() {
                    return window.affiliateCategories[this.siteId] || [];
                },
                
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
                
                enableEdit() {
                    this.editMode = true;
                    // setTimeout to wait for x-show to render
                    setTimeout(() => {
                        this.open = true;
                        if (this.$refs.searchInput) {
                            this.$refs.searchInput.focus();
                        }
                    }, 50);
                },
                
                selectItem(item) {
                    this.selectedCode = item.code;
                    this.selectedName = item.name;
                    this.search = item.name;
                    this.open = false;
                    this.editMode = false;
                    
                    this.saveMapping(item.code, item.name);
                },
                
                clearSelection() {
                    this.selectedCode = '';
                    this.selectedName = '';
                    this.search = '';
                    this.open = true;
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                    
                    this.saveMapping('', '');
                },
                
                async saveMapping(affCode, affName) {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content') || document.querySelector('input[name="_token"]').value;
                    
                    try {
                        const response = await fetch('{{ route('affiliate.settings.category.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                site_id: this.siteId,
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
            };
        };

        Alpine.data('initCell', (siteId, domCode) => {
            let initCode = '';
            let initName = '';
            
            if (window.serverMappings[domCode] && window.serverMappings[domCode][siteId]) {
                initCode = window.serverMappings[domCode][siteId].affiliate_category_code;
                initName = window.serverMappings[domCode][siteId].affiliate_category_name;
            }
            
            return window.categorySelectComponent(siteId, domCode, initCode, initName);
        });
    });
</script>
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="mappingGrid()">
    <style>
        .pagination { display: flex; list-style: none; padding: 0; justify-content: center; gap: 0.25rem; margin: 0; flex-wrap: wrap; }
        .pagination .page-item .page-link, .pagination .page-item span { display: block; padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; color: #475569; text-decoration: none; font-size: 0.875rem; }
        .pagination .page-item.active span { background: #3b82f6; color: #fff; border-color: #3b82f6; font-weight: bold; }
        .pagination .page-item.disabled span { color: #cbd5e1; background: #f8fafc; }
        .pagination .page-item .page-link:hover { background: #f1f5f9; }
        
        /* Sticky Column Styles */
        .sticky-col {
            position: sticky;
            left: 0;
            z-index: 10;
            background-color: #f8fafc; 
            border-right: 1px solid #e2e8f0;
            min-width: 350px;
        }
        tbody .sticky-col {
            background-color: #ffffff;
        }
        tbody tr:hover .sticky-col {
            background-color: #f8fafc;
        }
    </style>
    <div class="mb-8 flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('affiliate.settings.index') }}" class="text-slate-500 hover:text-slate-700 font-medium">
                    &larr; 돌아가기
                </a>
                <h1 class="text-2xl font-bold text-slate-900 ml-4">다중 카테고리 매핑 설정</h1>
            </div>
            <p class="mt-2 text-sm text-slate-500">도매토피아의 카테고리를 여러 제휴사(대한판촉, 오너클랜 등)와 한 번에 매핑합니다.</p>
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

    <!-- Column Visibility Toggles -->
    <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">노출할 제휴사 선택 (작업할 열 표시)</h3>
        <div class="flex flex-wrap gap-2">
            <template x-for="site in sites" :key="site.id">
                <button type="button" @click="toggleSite(site.id)" 
                        :class="site.visible ? 'bg-indigo-100 text-indigo-700 border-indigo-200 ring-2 ring-indigo-500' : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100'"
                        class="inline-flex items-center px-4 py-2 border rounded-full text-sm font-medium transition-all">
                    <span x-text="site.name"></span>
                    <svg x-show="site.visible" class="ml-1.5 h-4 w-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </template>
        </div>
    </div>

    <div class="mb-6 flex space-x-2 border-b border-gray-200 pb-4 justify-between items-center">
        <div class="flex space-x-2">
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'all', 'page' => 1]) }}" 
               class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-colors border {{ request('filter', 'all') === 'all' ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50' }}">
                전체 리스트
                <span class="ml-2 inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold {{ request('filter', 'all') === 'all' ? 'bg-white text-slate-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ number_format($counts['all'] ?? 0) }}
                </span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'mapped', 'page' => 1]) }}" 
               class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-colors border {{ request('filter') === 'mapped' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50' }}">
                매핑 완료
                <span class="ml-2 inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold {{ request('filter') === 'mapped' ? 'bg-white text-indigo-600' : 'bg-slate-100 text-slate-600' }}">
                    {{ number_format($counts['mapped'] ?? 0) }}
                </span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'unmapped', 'page' => 1]) }}" 
               class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-colors border {{ request('filter') === 'unmapped' ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50' }}">
                미매핑
                <span class="ml-2 inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold {{ request('filter') === 'unmapped' ? 'bg-white text-rose-600' : 'bg-slate-100 text-slate-600' }}">
                    {{ number_format($counts['unmapped'] ?? 0) }}
                </span>
            </a>
        </div>
        
        <div>
            <form id="categoryFilterForm" method="GET" action="{{ route('affiliate.settings.category') }}" class="flex items-center">
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
        <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="sticky-col py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-900 sm:pl-6">
                            도매토피아 카테고리 (기준)
                        </th>
                        <template x-for="site in visibleSites" :key="site.id">
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900 whitespace-nowrap w-[350px]">
                                <span x-text="site.name + ' 매핑'"></span>
                            </th>
                        </template>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($categories as $category)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="sticky-col py-4 pl-4 pr-3 text-sm sm:pl-6 align-top">
                            <div class="font-medium text-slate-900">{{ $category->full_name }}</div>
                            <div class="text-slate-500 mt-1 text-xs">Code: {{ $category->category_code }}</div>
                        </td>
                        
                        <template x-for="site in visibleSites" :key="site.id">
                            <td class="px-3 py-4 text-sm text-slate-500 align-top">
                                <!-- LAZY LOADED COMPONENT -->
                                <div x-data="initCell(site.id, '{{ $category->category_code }}')">
                                    <!-- Read Mode -->
                                    <div x-show="!editMode" @click="enableEdit()" class="p-2 border border-dashed border-slate-300 rounded-md cursor-pointer hover:bg-indigo-50 hover:border-indigo-300 transition-colors min-h-[42px] flex items-center justify-between group">
                                        <span x-text="selectedName ? selectedName : '클릭하여 매핑 선택...'" :class="selectedName ? 'text-indigo-700 font-medium' : 'text-slate-400 italic'"></span>
                                        <svg class="h-4 w-4 text-slate-300 group-hover:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </div>
                                    
                                    <!-- Edit Mode -->
                                    <div x-show="editMode" class="relative" @click.away="editMode = false; open = false">
                                        <div class="relative">
                                            <input type="text" x-model="search" @focus="open = true" x-ref="searchInput"
                                                class="block w-full rounded-md border-0 py-2 pl-3 pr-10 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 placeholder:text-slate-400" 
                                                placeholder="카테고리 검색..." autocomplete="off">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                                                <button type="button" @click="clearSelection()" x-show="selectedCode" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                                <svg x-show="!selectedCode" class="h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div x-show="open" class="absolute z-50 mt-1 w-full rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" style="max-height: 300px; overflow-y: auto;" x-transition>
                                            <template x-if="options.length === 0">
                                                <div class="relative cursor-default select-none py-2 pl-3 pr-9 text-slate-500">
                                                    이 제휴사는 검색 가능한 카테고리가 없습니다.
                                                </div>
                                            </template>
                                            <template x-for="item in filteredOptions" :key="item.code">
                                                <div @click="selectItem(item)" class="relative cursor-pointer select-none py-2 pl-3 pr-9 text-slate-900 hover:bg-indigo-600 hover:text-white group">
                                                    <div class="flex items-center">
                                                        <span class="font-normal block whitespace-normal break-words" x-text="item.name"></span>
                                                    </div>
                                                    <span x-show="item.code === selectedCode" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 group-hover:text-white">
                                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </div>
                                            </template>
                                            <template x-if="hasMore">
                                                <div class="relative cursor-default select-none py-2 px-3 text-xs text-slate-500 bg-slate-50 border-t border-slate-100 text-center">
                                                    검색 결과가 너무 많습니다. 검색어를 더 입력해주세요.
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </template>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="100%" class="px-6 py-12 text-center text-slate-500 bg-slate-50/50">
                            <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            해당하는 카테고리가 없습니다.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($categories->hasPages())
        <div class="bg-white px-4 py-3 border-t border-slate-200 sm:px-6">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
