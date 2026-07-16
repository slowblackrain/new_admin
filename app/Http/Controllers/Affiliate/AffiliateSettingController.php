<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\AffiliateSite;
use App\Models\AffiliateCategoryMapping;
use App\Models\AffiliateSetting;
use App\Services\Affiliate\DaehanScraperService;
use Illuminate\Support\Facades\Cache;

class AffiliateSettingController extends Controller
{
    /**
     * 제휴처 기본 환경설정 화면 (마진율, 배송비 등)
     */
    public function index()
    {
        $site = AffiliateSite::firstOrCreate(['name' => '대한판촉']);
        $setting = AffiliateSetting::firstOrCreate(
            ['affiliate_site_id' => $site->id],
            ['margin_rate' => 0, 'shipping_fee' => 3000]
        );

        return view('affiliate.settings.index', compact('site', 'setting'));
    }

    /**
     * 제휴처 기본 환경설정 저장
     */
    public function store(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:affiliate_sites,id',
            'login_id' => 'nullable|string|max:255',
            'login_password' => 'nullable|string|max:255',
            'margin_rate' => 'required|numeric|min:0|max:1000',
            'shipping_fee' => 'required|numeric|min:0',
        ]);

        AffiliateSetting::updateOrCreate(
            ['affiliate_site_id' => $request->site_id],
            [
                'login_id' => $request->login_id,
                'login_password' => $request->login_password,
                'margin_rate' => $request->margin_rate,
                'shipping_fee' => $request->shipping_fee,
            ]
        );

        return back()->with('success', '설정이 성공적으로 저장되었습니다.');
    }

    /**
     * 카테고리 매핑 설정 화면
     */
    public function categoryMapping(Request $request)
    {
        // 1. 모든 활성화된 제휴사 로드
        $sites = AffiliateSite::where('is_active', 1)->get();
        if ($sites->isEmpty()) {
            $site = AffiliateSite::firstOrCreate(['name' => '대한판촉', 'is_active' => 1]);
            $sites = collect([$site]);
        }
        
        // 2. 제휴사별 카테고리 목록 로드
        $affiliateCategoriesList = [];
        $serviceMap = [
            '대한판촉' => \App\Services\Affiliate\DaehanScraperService::class,
            '오너클랜' => \App\Services\Affiliate\OwnerclanService::class,
            '도매매'   => \App\Services\Affiliate\DomemeService::class,
        ];

        foreach ($sites as $site) {
            if (isset($serviceMap[$site->name])) {
                $serviceClass = $serviceMap[$site->name];
                
                if ($site->name === '도매매') {
                    $scraper = new $serviceClass($site->api_key);
                } else {
                    $scraper = new $serviceClass();
                }
                
                $categories = $scraper->fetchCategories();
                
                $list = [];
                if ($site->name === '대한판촉') {
                    // 대한판촉은 leaf 노드 검사 필요 (key => name 형태 반환)
                    $daehanCategoryCodes = array_keys($categories);
                    foreach ($categories as $code => $name) {
                        $isLeaf = true;
                        foreach ($daehanCategoryCodes as $otherCode) {
                            if ($code !== $otherCode && str_starts_with($otherCode, $code)) {
                                $isLeaf = false;
                                break;
                            }
                        }
                        if ($isLeaf) {
                            $list[] = ['code' => $code, 'name' => $name];
                        }
                    }
                } else if ($site->name === '오너클랜') {
                    // 오너클랜 리프 카테고리 필터링 (이름 기반 O(N log N) 알고리즘)
                    usort($categories, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    $count = count($categories);
                    for ($i = 0; $i < $count; $i++) {
                        // 정렬된 상태이므로, 바로 다음 항목이 '현재항목>' 으로 시작하면 부모 노드임
                        if ($i < $count - 1 && str_starts_with($categories[$i+1]['name'], $categories[$i]['name'] . '>')) {
                            continue;
                        }
                        $list[] = $categories[$i];
                    }
                } else if ($site->name === '도매매') {
                    $list = $categories;
                }
                
                $affiliateCategoriesList[$site->id] = $list;
            } else {
                $affiliateCategoriesList[$site->id] = [];
            }
        }
        
        // 3. 도매토피아 리프 카테고리 추출
        $allCategories = Category::all()->keyBy('id');
        $selectedCategory = $request->input('category_code');
        
        $syncCategories = \App\Models\Category::where('level', 2)
            ->where('hide', '0')
            ->orderBy('position')
            ->get();
            
        $leavesQuery = Category::where('hide', '0')
            ->where('list_use', 'y')
            ->where('level', '>=', 2)
            ->whereDoesntHave('children', function($query) {
                $query->where('hide', '0')
                      ->where('list_use', 'y');
            });
            
        // 판매중 상품이 존재하는 대표 카테고리만 기본 조건으로 필터링
        $activeCategoryCodes = \Illuminate\Support\Facades\DB::table('fm_category_link as cl')
            ->join('fm_goods as g', 'cl.goods_seq', '=', 'g.goods_seq')
            ->where('cl.link', 1) // 대표 카테고리만 체크
            ->where('g.goods_view', 'look')
            ->select('cl.category_code')
            ->distinct()
            ->pluck('category_code')
            ->toArray();
            
        $leavesQuery->whereIn('category_code', $activeCategoryCodes);
            
        if ($selectedCategory) {
            $leavesQuery->where('category_code', 'like', $selectedCategory . '%');
        }
            
        $leaves = $leavesQuery->get();

        // 선택된 사이트 필터링 로직 추가 (세션 저장으로 새로고침 유지)
        $selectedSites = $request->input('sites');
        if ($selectedSites === null) {
            $selectedSites = session('category_visible_sites');
        }
        
        if (empty($selectedSites)) {
            $selectedSites = $sites->pluck('id')->toArray();
        }
        session(['category_visible_sites' => $selectedSites]);
        
        // $sites 객체에 visible 속성 추가
        $sites->map(function($site) use ($selectedSites) {
            $site->visible = in_array($site->id, $selectedSites);
            return $site;
        });

        // 4. 선택된 제휴사 매핑 데이터 먼저 조회 (필터링에 사용)
        $allMappings = AffiliateCategoryMapping::whereIn('affiliate_site_id', $selectedSites)->get();
        $mappingsByDomCode = [];
        $mappedCodesAllSites = [];
        foreach ($allMappings as $mapping) {
            $mappingsByDomCode[$mapping->dometopia_category_code][$mapping->affiliate_site_id] = $mapping;
            $mappedCodesAllSites[] = $mapping->dometopia_category_code;
        }
        $mappedCodesAllSites = array_unique($mappedCodesAllSites);

        $filter = $request->input('filter', 'all');
        $leafCategories = collect();
        $mappedCount = 0;
        $unmappedCount = 0;
        
        foreach ($leaves as $cat) {
            $fullName = $cat->title;
            $curr = $cat;
            $isValidChain = true;
            $rootPosition = ($cat->level == 2) ? $cat->position : 999999;
            
            while ($curr->parent_id && $allCategories->has($curr->parent_id)) {
                $curr = $allCategories->get($curr->parent_id);
                if ($curr->level < 2) break;
                if ($curr->level == 2) $rootPosition = $curr->position;
                if ($curr->hide !== '0' || $curr->list_use !== 'y') {
                    $isValidChain = false;
                    break;
                }
                $fullName = $curr->title . ' > ' . $fullName;
            }
            
            if ($curr->level > 2 || !$isValidChain) continue;

            $isMapped = true;
            foreach ($selectedSites as $siteId) {
                if (!isset($mappingsByDomCode[$cat->category_code][$siteId])) {
                    $isMapped = false;
                    break;
                }
            }
            
            if ($isMapped) {
                $mappedCount++;
            } else {
                $unmappedCount++;
            }

            if ($filter === 'mapped' && !$isMapped) continue;
            if ($filter === 'unmapped' && $isMapped) continue;

            $cat->full_name = $fullName;
            $cat->root_position = $rootPosition;
            $leafCategories->push($cat);
        }
        
        $counts = [
            'all' => $mappedCount + $unmappedCount,
            'mapped' => $mappedCount,
            'unmapped' => $unmappedCount,
        ];

        $leafCategories = $leafCategories->sortBy([
            ['root_position', 'asc'],
            ['category_code', 'asc']
        ]);

        $perPage = 50;
        $page = $request->input('page', 1);
        $paginatedCategories = new \Illuminate\Pagination\LengthAwarePaginator(
            $leafCategories->forPage($page, $perPage),
            $leafCategories->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // 5. 페이지네이션 객체 생성

            
        return view('affiliate.settings.category', [
            'sites' => $sites,
            'categories' => $paginatedCategories, 
            'mappings' => $mappingsByDomCode,
            'counts' => $counts,
            'affiliateCategoriesJson' => json_encode($affiliateCategoriesList),
            'syncCategories' => $syncCategories,
            'selectedCategory' => $selectedCategory
        ]);
    }

    /**
     * 카테고리 매핑 저장
     */
    public function storeCategoryMapping(Request $request)
    {
        $site_id = $request->input('site_id');
        $mappings = $request->input('mappings', []); // array of dometopia_code => affiliate_code

        foreach ($mappings as $domCode => $affCode) {
            if (empty($affCode)) {
                AffiliateCategoryMapping::where('affiliate_site_id', $site_id)
                    ->where('dometopia_category_code', $domCode)
                    ->delete();
                continue;
            }
            
            AffiliateCategoryMapping::updateOrCreate(
                [
                    'affiliate_site_id' => $site_id,
                    'dometopia_category_code' => $domCode
                ],
                [
                    'affiliate_category_code' => $affCode,
                    'affiliate_category_name' => $request->input("mapping_names.{$domCode}")
                ]
            );
        }

        if ($request->wantsJson() || $request->isJson() || $request->ajax()) {
            return response()->json(['status' => 'success']);
        }

        return back()->with('success', '카테고리 매핑이 저장되었습니다.');
    }

    public function autoMapCategories(Request $request)
    {
        // 1. 제휴사 목록 로드
        $sites = AffiliateSite::where('is_active', 1)->get();
        $serviceMap = [
            '대한판촉' => \App\Services\Affiliate\DaehanScraperService::class,
            '오너클랜' => \App\Services\Affiliate\OwnerclanService::class,
        ];
        
        $affiliateCategoriesList = [];
        foreach ($sites as $site) {
            if (isset($serviceMap[$site->name])) {
                $serviceClass = $serviceMap[$site->name];
                $scraper = new $serviceClass();
                $categories = $scraper->fetchCategories();
                
                $list = [];
                if ($site->name === '대한판촉') {
                    // 대한판촉은 leaf 노드 검사 필요
                    $daehanCategoryCodes = array_keys($categories);
                    foreach ($categories as $code => $name) {
                        $isLeaf = true;
                        foreach ($daehanCategoryCodes as $otherCode) {
                            if ($code !== $otherCode && str_starts_with($otherCode, $code)) {
                                $isLeaf = false;
                                break;
                            }
                        }
                        if ($isLeaf) {
                            $list[] = ['code' => $code, 'name' => $name];
                        }
                    }
                } else if ($site->name === '오너클랜') {
                    // 오너클랜 리프 카테고리 필터링
                    usort($categories, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    $count = count($categories);
                    for ($i = 0; $i < $count; $i++) {
                        if ($i < $count - 1 && str_starts_with($categories[$i+1]['name'], $categories[$i]['name'] . '>')) {
                            continue;
                        }
                        $list[] = $categories[$i];
                    }
                }
                
                $affiliateCategoriesList[$site->id] = $list;
            }
        }
        
        // 판매중 상품이 존재하는 대표 카테고리만 필터링
        $activeCategoryCodes = \Illuminate\Support\Facades\DB::table('fm_category_link as cl')
            ->join('fm_goods as g', 'cl.goods_seq', '=', 'g.goods_seq')
            ->where('cl.link', 1) // 대표 카테고리만 체크
            ->where('g.goods_view', 'look')
            ->select('cl.category_code')
            ->distinct()
            ->pluck('category_code')
            ->toArray();
            
        // 2. 도매토피아 유효 리프 카테고리 추출
        $allCategories = Category::all()->keyBy('id');
        $leaves = Category::where('hide', '0')
            ->where('list_use', 'y')
            ->where('level', '>=', 2)
            ->whereIn('category_code', $activeCategoryCodes)
            ->whereDoesntHave('children', function($query) {
                $query->where('hide', '0')->where('list_use', 'y');
            })->get();
            
        $validLeaves = [];
        foreach ($leaves as $cat) {
            $curr = $cat;
            $isValidChain = true;
            while ($curr->parent_id && $allCategories->has($curr->parent_id)) {
                $curr = $allCategories->get($curr->parent_id);
                if ($curr->level < 2) break;
                if ($curr->hide !== '0' || $curr->list_use !== 'y') {
                    $isValidChain = false;
                    break;
                }
            }
            if ($curr->level <= 2 && $isValidChain) {
                $validLeaves[] = $cat;
            }
        }
        
        // 3. 기존 매핑 조회
        $allMappings = AffiliateCategoryMapping::all()->groupBy('dometopia_category_code');
        
        // 유틸리티 함수: 텍스트 정규화
        $normalize = function($string) {
            $string = preg_replace("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>\[\]\{\}]/i", " ", $string);
            $string = preg_replace('/\s+/', ' ', $string);
            return trim($string);
        };
        
        // 유틸리티 함수: 토큰화
        $tokenize = function($string) use ($normalize) {
            $parts = explode('>', $string);
            $leaf = trim(end($parts));
            $leafTokens = explode('/', $leaf);
            $leafNormalized = [];
            foreach ($leafTokens as $t) {
                $norm = $normalize($t);
                if (mb_strlen($norm) >= 2) {
                    $leafNormalized[] = $norm;
                }
            }
            
            $tokens = [];
            foreach ($parts as $p) {
                $p = $normalize($p);
                $words = explode(' ', $p);
                foreach ($words as $w) {
                    if (mb_strlen($w) >= 2) {
                        $tokens[] = $w;
                    }
                }
            }
            return [
                'leaf' => $leafNormalized,
                'tokens' => array_unique($tokens),
                'raw' => $string
            ];
        };
        
        // 유틸리티 함수: 점수 산출
        $calculateScore = function($domTokens, $affTokens) {
            $score = 0;
            $leafMatched = false;
            
            // 1. Leaf exact match (가장 강력한 가중치)
            foreach ($domTokens['leaf'] as $dLeaf) {
                foreach ($affTokens['leaf'] as $aLeaf) {
                    if ($dLeaf && $aLeaf && $dLeaf === $aLeaf) {
                        $score += 50;
                        $leafMatched = true;
                        break 2;
                    } else if ($dLeaf && $aLeaf && (str_contains($dLeaf, $aLeaf) || str_contains($aLeaf, $dLeaf))) {
                        $score += 20;
                        $leafMatched = true;
                    }
                }
            }
            
            // 2. Token intersections (경로 내 단어 매칭)
            $intersect = array_intersect($domTokens['tokens'], $affTokens['tokens']);
            $score += count($intersect) * 10;
            
            // 3. 완전히 다른 경로인데 리프만 같은 경우 페널티 방어 (문맥 검증)
            if ($leafMatched && count($intersect) <= 1) {
                $score -= 10; // 우연히 마지막 단어만 겹칠 확률 패널티
            }
            
            return $score;
        };

        // 4. 미리 제휴사 카테고리를 토큰화 해둔다 (루프 성능 최적화)
        $tokenizedAffiliates = [];
        foreach ($sites as $site) {
            if (!isset($affiliateCategoriesList[$site->id])) continue;
            foreach ($affiliateCategoriesList[$site->id] as $affCat) {
                $tokenizedAffiliates[$site->id][] = [
                    'code' => $affCat['code'],
                    'name' => $affCat['name'],
                    'tokens' => $tokenize($affCat['name'])
                ];
            }
        }

        $mappedCount = 0;
        $threshold = 30; // 30점 이상일 때만 매핑 (오매핑 방지 임계치)

        foreach ($validLeaves as $cat) {
            $domCode = $cat->category_code;
            
            $fullName = $cat->title;
            $curr = $cat;
            while ($curr->parent_id && $allCategories->has($curr->parent_id)) {
                $curr = $allCategories->get($curr->parent_id);
                if ($curr->level < 2) break;
                $fullName = $curr->title . ' > ' . $fullName;
            }
            
            $domTokens = $tokenize($fullName);
            
            $existingMappings = $allMappings->has($domCode) ? $allMappings->get($domCode)->keyBy('affiliate_site_id') : collect();
            
            foreach ($sites as $site) {
                if (!isset($tokenizedAffiliates[$site->id])) continue;
                
                // 이미 정상적으로 매핑되어 있으면 건너뜀 (코드는 있는데 이름이 비어있는 과거 찌꺼기 데이터는 덮어씌움)
                $existingMapping = $existingMappings->get($site->id);
                if ($existingMapping && 
                    !empty($existingMapping->affiliate_category_code) && 
                    !empty($existingMapping->affiliate_category_name)
                ) {
                    continue;
                }
                
                $bestMatch = null;
                $highestScore = -1;
                
                // 해당 제휴사의 모든 카테고리를 순회하여 가장 높은 점수를 찾음
                foreach ($tokenizedAffiliates[$site->id] as $affData) {
                    $score = $calculateScore($domTokens, $affData['tokens']);
                    if ($score > $highestScore) {
                        $highestScore = $score;
                        $bestMatch = $affData;
                    }
                }
                
                // 베스트 매치가 임계치를 넘으면 저장
                if ($domCode === '002000260010') {
                    \Log::info("Testing $domCode for Ownerclan: Best Match: " . json_encode($bestMatch, JSON_UNESCAPED_UNICODE) . " Score: " . $highestScore);
                }

                if ($highestScore >= $threshold && $bestMatch) {
                    AffiliateCategoryMapping::updateOrCreate(
                        [
                            'affiliate_site_id' => $site->id,
                            'dometopia_category_code' => $domCode
                        ],
                        [
                            'affiliate_category_code' => $bestMatch['code'],
                            'affiliate_category_name' => $bestMatch['name']
                        ]
                    );
                    $mappedCount++;
                }
            }
        }
        
        return back()->with('success', "가중치 기반 자동 매핑을 완료했습니다. 총 {$mappedCount}개가 다중 제휴사에 새롭게 매핑되었습니다.");
    }

    /**
     * 상품 동기화 대시보드 화면
     */
    public function syncIndex(Request $request)
    {
        $siteId = $request->input('site_id');
        $sites = AffiliateSite::where('is_active', 1)->get();
        if ($siteId) {
            $site = $sites->where('id', $siteId)->first();
        } else {
            $site = $sites->first();
        }
        
        if (!$site) {
            // Fallback
            $site = AffiliateSite::firstOrCreate(['name' => '대한판촉', 'is_active' => 1]);
            $sites = collect([$site]);
        }
        $mappedCodes = AffiliateCategoryMapping::where('affiliate_site_id', $site->id)
            ->whereNotNull('affiliate_category_code')
            ->pluck('dometopia_category_code')
            ->toArray();
        $selectedCategory = $request->input('category_code');
        
        $syncCategories = \App\Models\Category::where('level', 2)
            ->where('hide', '0')
            ->orderBy('position')
            ->get();
            
        // 매핑된 카테고리에 속한 대상 상품의 고유 개수 (정상/노출/특정 scode 제외/link_yn='Y')
        $query = \Illuminate\Support\Facades\DB::table('fm_category_link')
            ->join('fm_goods', 'fm_category_link.goods_seq', '=', 'fm_goods.goods_seq')
            ->where('fm_goods.link_yn', 'Y')
            ->where('fm_goods.goods_view', 'Look')
            ->where('fm_goods.goods_status', 'normal')
            ->where(function($q) {
                $prefixes = ['AKS', 'ATS', 'GKM', 'GUS', 'TRO', 'GDF', 'MOD', 'MTS', 'GDH', 'GDR', 'MKS', 'MKD', 'BTB'];
                foreach ($prefixes as $prefix) {
                    $q->where('fm_goods.goods_scode', 'not like', $prefix . '%');
                }
                $q->orWhereNull('fm_goods.goods_scode'); // null인 경우도 포함 (조건에 따라)
            });
            
        if ($selectedCategory) {
            // 선택된 카테고리로 시작하는 카테고리 (하위 포함) 중 매핑된 것만
            $filteredMappedCodes = array_filter($mappedCodes, function($code) use ($selectedCategory) {
                return str_starts_with($code, $selectedCategory);
            });
            
            if (empty($filteredMappedCodes)) {
                $query->whereRaw('1 = 0'); // 결과 없도록 강제
            } else {
                $query->whereIn('fm_category_link.category_code', $filteredMappedCodes);
            }
        } else {
            $query->whereIn('fm_category_link.category_code', $mappedCodes);
        }
            
        $totalTargetCount = $query->distinct('fm_category_link.goods_seq')
            ->count('fm_category_link.goods_seq');
            
        // 서브쿼리로 대상 중 성공/실패 수 조회
        // 카테고리 필터가 있는 경우, 조인을 통해 필터링
        $successCount = (clone $query)
            ->join('affiliate_goods_syncs as s', function($join) use ($site) {
                $join->on('fm_category_link.goods_seq', '=', 's.goods_seq')
                     ->where('s.affiliate_site_id', '=', $site->id)
                     ->where('s.sync_status', '=', 'success');
            })
            ->distinct('fm_category_link.goods_seq')
            ->count('fm_category_link.goods_seq');
            
        $failedCount = (clone $query)
            ->join('affiliate_goods_syncs as s', function($join) use ($site) {
                $join->on('fm_category_link.goods_seq', '=', 's.goods_seq')
                     ->where('s.affiliate_site_id', '=', $site->id)
                     ->where('s.sync_status', '=', 'failed');
            })
            ->distinct('fm_category_link.goods_seq')
            ->count('fm_category_link.goods_seq');
            
        $pendingCount = $totalTargetCount - $successCount;
        if ($pendingCount < 0) $pendingCount = 0;

        // === 최적화된 수동 페이징 로직 (MySQL 옵티마이저 버그 우회 및 Set Difference 기법) ===
        $tab = $request->input('tab', 'all');
        $page = (int)$request->input('page', 1);
        $perPage = 50;

        // 1. 전체 대상 상품의 ID만 빠르게 추출 (약 0.5초 소요)
        $targetIdsQuery = \Illuminate\Support\Facades\DB::table('fm_goods as g')
            ->select('g.goods_seq')
            ->join('fm_category_link as cl', 'g.goods_seq', '=', 'cl.goods_seq');

        if ($selectedCategory) {
            if (empty($filteredMappedCodes)) {
                $targetIdsQuery->whereRaw('1 = 0');
            } else {
                $targetIdsQuery->whereIn('cl.category_code', $filteredMappedCodes);
            }
        } else {
            $targetIdsQuery->whereIn('cl.category_code', $mappedCodes);
        }

        $targetIdsQuery->where('g.link_yn', 'Y')
                       ->where('g.goods_view', 'Look')
                       ->where('g.goods_status', 'normal')
                       ->where(function($q) {
                           $prefixes = ['AKS', 'ATS', 'GKM', 'GUS', 'TRO', 'GDF', 'MOD', 'MTS', 'GDH', 'GDR', 'MKS', 'MKD', 'BTB'];
                           foreach ($prefixes as $prefix) {
                               $q->where('g.goods_scode', 'not like', $prefix . '%');
                           }
                           $q->orWhereNull('g.goods_scode');
                       });

        // 타겟 상품 고유 시퀀스 추출
        $targetIds = $targetIdsQuery->distinct()->pluck('g.goods_seq')->toArray();

        // 2. 탭 조건에 맞게 PHP 메모리 상에서 차집합/교집합 수행 (0.1초 내외)
        $finalIds = [];

        if ($tab === 'all') {
            $finalIds = $targetIds;
        } else {
            // 해당 제휴사의 성공 및 실패한 상품 번호만 빠르게 추출
            $successIds = \Illuminate\Support\Facades\DB::table('affiliate_goods_syncs')
                ->where('affiliate_site_id', $site->id)
                ->where('sync_status', 'success')
                ->pluck('goods_seq')->toArray();

            $failedIds = \Illuminate\Support\Facades\DB::table('affiliate_goods_syncs')
                ->where('affiliate_site_id', $site->id)
                ->where('sync_status', 'failed')
                ->pluck('goods_seq')->toArray();

            if ($tab === 'success') {
                $finalIds = array_values(array_intersect($targetIds, $successIds));
            } elseif ($tab === 'failed') {
                $finalIds = array_values(array_intersect($targetIds, $failedIds));
            } elseif ($tab === 'pending') {
                // 대기(미전송) = 타겟 전체 - (성공 + 실패)
                $syncedIds = array_merge($successIds, $failedIds);
                $finalIds = array_values(array_diff($targetIds, $syncedIds));
            }
        }

        // 3. 최신순으로 정렬 후 현재 페이지에 표출될 50개 항목만 자르기 (array_slice)
        rsort($finalIds);
        $totalItems = count($finalIds);
        $seqs = array_slice($finalIds, ($page - 1) * $perPage, $perPage);

        // 4. 수동 Paginator 객체 생성
        $paginatedGoods = new \Illuminate\Pagination\LengthAwarePaginator(
            [], // 컬렉션은 하단에서 맵핑 후 주입
            $totalItems,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->all()]
        );

        if (!empty($seqs)) {
            $fullItems = \Illuminate\Support\Facades\DB::table('fm_goods as g')
                ->select(
                    'g.goods_seq', 'g.goods_name', 'g.goods_scode', 'g.goods_code', 'go.price', 
                    's.sync_status', 's.error_message', 's.last_synced_at', 's.affiliate_goods_code'
                )
                ->leftJoin('fm_goods_option as go', function($join) {
                    $join->on('g.goods_seq', '=', 'go.goods_seq')
                         ->where('go.default_option', '=', 'y');
                })
                ->leftJoin('affiliate_goods_syncs as s', function($join) use ($site) {
                    $join->on('g.goods_seq', '=', 's.goods_seq')
                         ->where('s.affiliate_site_id', '=', $site->id);
                })
                ->whereIn('g.goods_seq', $seqs)
                ->get();
                
            $keyedItems = $fullItems->keyBy('goods_seq');
            $sortedItems = collect($seqs)->map(function($seq) use ($keyedItems) {
                return $keyedItems[$seq] ?? null;
            })->filter();
            
            $paginatedGoods->setCollection($sortedItems);
            
            // 매핑 정보 가져오기 (현재 페이지 상품 대상)
            $mappings = \Illuminate\Support\Facades\DB::table('fm_category_link as cl')
                ->join('affiliate_category_mappings as acm', 'cl.category_code', '=', 'acm.dometopia_category_code')
                ->where('acm.affiliate_site_id', $site->id)
                ->whereNotNull('acm.affiliate_category_code')
                ->whereIn('cl.goods_seq', $seqs)
                ->select('cl.goods_seq', 'acm.affiliate_category_name', 'acm.affiliate_category_code')
                ->get()
                ->groupBy('goods_seq');
        } else {
            $mappings = collect();
        }

        return view('affiliate.settings.sync', compact('sites', 'site', 'totalTargetCount', 'successCount', 'failedCount', 'pendingCount', 'syncCategories', 'selectedCategory', 'paginatedGoods', 'tab', 'mappings'));
    }

    /**
     * 상품 청크 단위 동기화 처리 (AJAX)
     */
    public function syncChunk(Request $request)
    {
        $siteId = $request->input('site_id');
        $site = AffiliateSite::find($siteId);
        if (!$site) {
            return response()->json(['status' => 'error', 'message' => '제휴처를 찾을 수 없습니다.']);
        }
        
        $chunkSize = 1; // 테스트 모드: 한 번에 1개씩만 전송
        
        $mappedCodes = AffiliateCategoryMapping::where('affiliate_site_id', $site->id)
            ->whereNotNull('affiliate_category_code')
            ->pluck('dometopia_category_code')
            ->toArray();
            
        if (empty($mappedCodes)) {
            return response()->json(['status' => 'done', 'message' => '매핑된 카테고리가 없어 동기화할 상품이 없습니다.']);
        }
            
        $alreadySuccessSeqs = \App\Models\AffiliateGoodsSync::where('affiliate_site_id', $site->id)
            ->where('sync_status', 'success')
            ->pluck('goods_seq')
            ->toArray();
            
        $selectedCategory = $request->input('category_code');
        
        // 미전송(또는 실패) 상품 추출 (정상/노출/특정 scode 제외/link_yn='Y')
        $query = \Illuminate\Support\Facades\DB::table('fm_category_link')
            ->join('fm_goods', 'fm_category_link.goods_seq', '=', 'fm_goods.goods_seq')
            ->where('fm_goods.link_yn', 'Y')
            ->where('fm_goods.goods_view', 'Look')
            ->where('fm_goods.goods_status', 'normal')
            ->where(function($q) {
                $prefixes = ['AKS', 'ATS', 'GKM', 'GUS', 'TRO', 'GDF', 'MOD', 'MTS', 'GDH', 'GDR', 'MKS', 'MKD', 'BTB'];
                foreach ($prefixes as $prefix) {
                    $q->where('fm_goods.goods_scode', 'not like', $prefix . '%');
                }
                $q->orWhereNull('fm_goods.goods_scode');
            });
            
        if ($selectedCategory) {
            $filteredMappedCodes = array_filter($mappedCodes, function($code) use ($selectedCategory) {
                return str_starts_with($code, $selectedCategory);
            });
            
            if (empty($filteredMappedCodes)) {
                return response()->json(['status' => 'done', 'message' => '해당 카테고리에 매핑된 상품이 없습니다.']);
            }
            $query->whereIn('fm_category_link.category_code', $filteredMappedCodes);
        } else {
            $query->whereIn('fm_category_link.category_code', $mappedCodes);
        }
            
        if (!empty($alreadySuccessSeqs)) {
            $query->whereNotIn('fm_category_link.goods_seq', $alreadySuccessSeqs);
        }
        
        $goodsSeqsToSync = $query->select('fm_category_link.goods_seq')
            ->distinct()
            ->limit($chunkSize)
            ->pluck('goods_seq')
            ->toArray();
            
        if (empty($goodsSeqsToSync)) {
            return response()->json(['status' => 'done', 'message' => '모든 상품 동기화가 완료되었습니다.']);
        }
        
        $results = [];
        if ($site->name === '오너클랜') {
            $scraper = new \App\Services\Affiliate\OwnerclanService();
        } else {
            $scraper = new \App\Services\Affiliate\DaehanScraperService();
        }
        
        $goodsList = \App\Models\Goods::whereIn('goods_seq', $goodsSeqsToSync)
            ->get();
            
        foreach ($goodsList as $goods) {
            $res = $scraper->registerProduct($goods);
            
            \App\Models\AffiliateGoodsSync::updateOrCreate(
                ['affiliate_site_id' => $site->id, 'goods_seq' => $goods->goods_seq],
                [
                    'sync_status' => $res['success'] ? 'success' : 'failed',
                    'error_message' => $res['message'] ?? null,
                    'last_synced_at' => now(),
                    'affiliate_goods_code' => $res['affiliate_goods_code'] ?? null
                ]
            );
            
            $results[] = [
                'goods_seq' => $goods->goods_seq,
                'goods_name' => $goods->goods_name,
                'success' => $res['success'],
                'message' => $res['message'] ?? '전송 성공'
            ];
        }
        
        return response()->json(['status' => 'ok', 'results' => $results]);
    }

    /**
     * 선택된 상품 강제 동기화 (AJAX)
     */
    public function syncSelected(Request $request)
    {
        $siteId = $request->input('site_id');
        $site = AffiliateSite::find($siteId);
        if (!$site) {
            return response()->json(['status' => 'error', 'message' => '제휴처를 찾을 수 없습니다.']);
        }
        
        $goodsSeqs = $request->input('goods_seqs', []);
        
        if (empty($goodsSeqs)) {
            return response()->json(['status' => 'error', 'message' => '선택된 상품이 없습니다.']);
        }
        
        $results = [];
        if ($site->name === '오너클랜') {
            $scraper = new \App\Services\Affiliate\OwnerclanService();
        } else {
            $scraper = new \App\Services\Affiliate\DaehanScraperService();
        }
        
        $goodsList = \App\Models\Goods::whereIn('goods_seq', $goodsSeqs)
            ->get();
            
        foreach ($goodsList as $goods) {
            $res = $scraper->registerProduct($goods);
            
            \App\Models\AffiliateGoodsSync::updateOrCreate(
                ['affiliate_site_id' => $site->id, 'goods_seq' => $goods->goods_seq],
                [
                    'sync_status' => $res['success'] ? 'success' : 'failed',
                    'error_message' => $res['message'] ?? null,
                    'last_synced_at' => now(),
                    'affiliate_goods_code' => $res['affiliate_goods_code'] ?? null
                ]
            );
            
            $results[] = [
                'goods_seq' => $goods->goods_seq,
                'goods_name' => $goods->goods_name,
                'success' => $res['success'],
                'message' => $res['message'] ?? '전송 성공'
            ];
        }
        
        return response()->json(['status' => 'ok', 'results' => $results]);
    }
}