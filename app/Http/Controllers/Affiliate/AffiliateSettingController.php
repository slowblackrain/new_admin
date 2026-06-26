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
     * 카테고리 매핑 설정 화면
     */
    public function categoryMapping(Request $request)
    {
        $site = AffiliateSite::firstOrCreate(['name' => '대한판촉']);
        
        $daehanCategories = Cache::remember('daehan_categories', 86400, function () {
            $scraper = new DaehanScraperService('dotob2b', '0000');
            return $scraper->fetchCategories();
        });
        
        $daehanCategoriesList = [];
        foreach ($daehanCategories as $code => $name) {
            $daehanCategoriesList[] = [
                'code' => $code,
                'name' => $name
            ];
        }
        
        // 1. 부모 조회용 전체 카테고리 (해시맵)
        $allCategories = Category::all()->keyBy('id');
        
        // 2. 리프 카테고리 추출
        // 조건: hide = '0', list_use = 'y', level >= 2, 자식이 없는 카테고리(리프)
        $leaves = Category::where('hide', '0')
            ->where('list_use', 'y')
            ->where('level', '>=', 2)
            ->whereDoesntHave('children', function($query) {
                $query->where('hide', '0')
                      ->where('list_use', 'y');
            })
            ->get();

        $mappedCodes = AffiliateCategoryMapping::where('affiliate_site_id', $site->id)
            ->pluck('dometopia_category_code')
            ->toArray();
            
        $filter = $request->input('filter', 'all');
        $mappedCount = 0;
        $unmappedCount = 0;
            
        $leafCategories = collect();
        foreach ($leaves as $cat) {
            $fullName = $cat->title;
            $curr = $cat;
            $isValidChain = true;
            $rootPosition = ($cat->level == 2) ? $cat->position : 999999;
            
            // 상위 카테고리를 추적하여 정상적으로 최상단(level 1 이하)까지 도달하는지 확인
            while ($curr->parent_id && $allCategories->has($curr->parent_id)) {
                $curr = $allCategories->get($curr->parent_id);
                // 최상위 쇼핑몰(level 1) 이름은 경로에서 제외
                if ($curr->level < 2) {
                    break;
                }
                
                if ($curr->level == 2) {
                    $rootPosition = $curr->position;
                }
                
                // 부모 카테고리 중 하나라도 hide가 '0'이 아니거나 list_use가 'y'가 아니면 유효하지 않은 체인으로 간주
                if ($curr->hide !== '0' || $curr->list_use !== 'y') {
                    $isValidChain = false;
                    break;
                }
                
                $fullName = $curr->title . ' > ' . $fullName;
            }
            
            // 상위 카테고리가 삭제되어 추적이 중간에 끊겼거나, 숨김/미사용 부모가 포함되어 있다면 배제
            if ($curr->level > 2 || !$isValidChain) {
                continue;
            }

            // 필터 적용 전 카운트 집계
            $isMapped = in_array($cat->category_code, $mappedCodes);
            if ($isMapped) {
                $mappedCount++;
            } else {
                $unmappedCount++;
            }

            // 필터 적용
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

        // 대분류(Level 2)의 position 우선 정렬, 그다음 카테고리 코드 순 정렬
        $leafCategories = $leafCategories->sortBy([
            ['root_position', 'asc'],
            ['category_code', 'asc']
        ]);

        // 페이징 처리
        $perPage = 50;
        $page = $request->input('page', 1);
        $paginatedCategories = new \Illuminate\Pagination\LengthAwarePaginator(
            $leafCategories->forPage($page, $perPage),
            $leafCategories->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $mappings = AffiliateCategoryMapping::where('affiliate_site_id', $site->id)
            ->get()
            ->keyBy('dometopia_category_code');
            
        return view('affiliate.settings.category', [
            'site' => $site, 
            'categories' => $paginatedCategories, 
            'mappings' => $mappings,
            'counts' => $counts,
            'daehanCategoriesJson' => json_encode($daehanCategoriesList)
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

    /**
     * 카테고리 자동 매핑 (이름 유사도 기반)
     */
    public function autoMapCategories(Request $request)
    {
        $site = AffiliateSite::firstOrCreate(['name' => '대한판촉']);
        
        // 카테고리 스크래핑 및 캐싱 (1일 보관)
        $daehanCategories = Cache::remember('daehan_categories', 86400, function () {
            $scraper = new DaehanScraperService('dotob2b', '0000');
            return $scraper->fetchCategories();
        });

        // 1. 부모 조회용 전체 카테고리 (해시맵)
        $allCategories = Category::all()->keyBy('id');
        
        // 2. 리프 카테고리 추출
        $leaves = Category::where('hide', '0')
            ->where('list_use', 'y')
            ->where('level', '>=', 2)
            ->whereDoesntHave('children', function($query) {
                $query->where('hide', '0')
                      ->where('list_use', 'y');
            })
            ->get();

        $mappedCount = 0;
        $mappedCodes = AffiliateCategoryMapping::where('affiliate_site_id', $site->id)
            ->pluck('dometopia_category_code')
            ->toArray();
        
        foreach ($leaves as $cat) {
            $curr = $cat;
            $isValidChain = true;
            
            while ($curr->parent_id && $allCategories->has($curr->parent_id)) {
                $curr = $allCategories->get($curr->parent_id);
                if ($curr->level < 2) {
                    break;
                }
                
                if ($curr->hide !== '0' || $curr->list_use !== 'y') {
                    $isValidChain = false;
                    break;
                }
            }
            if ($curr->level > 2 || !$isValidChain) {
                continue;
            }

            // 미매핑 카테고리만 진행 (이미 매핑된 카테고리 건너뛰기)
            if (in_array($cat->category_code, $mappedCodes)) {
                continue;
            }

            $leafTitleRaw = str_replace(' ', '', $cat->title);
            $searchTerms = array_map('trim', explode('/', $leafTitleRaw));
            
            foreach ($daehanCategories as $dCode => $dName) {
                // 대한판촉 카테고리의 마지막 노드명 추출
                $dParts = explode('>', $dName);
                $dLeafTitleRaw = str_replace(' ', '', trim(end($dParts)));
                $dSearchTerms = array_map('trim', explode('/', $dLeafTitleRaw));
                
                $matched = false;
                
                // '/' 기준으로 분리된 단어들끼리 교차 검증
                foreach ($searchTerms as $term) {
                    if (empty($term) || mb_strlen($term) < 2) continue; // 너무 짧은 단어 제외
                    
                    foreach ($dSearchTerms as $dTerm) {
                        if (empty($dTerm) || mb_strlen($dTerm) < 2) continue;
                        
                        // 완벽히 일치하거나 포함되는 경우
                        if ($term === $dTerm || str_contains($term, $dTerm) || str_contains($dTerm, $term)) {
                            $matched = true;
                            break 2;
                        }
                    }
                }
                
                // 단어 교차로 매핑되지 않았을 경우, 전체 이름으로 다시 비교
                if (!$matched) {
                    if ($leafTitleRaw === $dLeafTitleRaw || 
                        (mb_strlen($leafTitleRaw) > 1 && mb_strlen($dLeafTitleRaw) > 1 && 
                         (strpos($leafTitleRaw, $dLeafTitleRaw) !== false || strpos($dLeafTitleRaw, $leafTitleRaw) !== false))
                    ) {
                        $matched = true;
                    }
                }
                
                if ($matched) {
                    AffiliateCategoryMapping::updateOrCreate(
                        [
                            'affiliate_site_id' => $site->id,
                            'dometopia_category_code' => $cat->category_code
                        ],
                        [
                            'affiliate_category_code' => $dCode,
                            'affiliate_category_name' => $dName
                        ]
                    );
                    $mappedCount++;
                    break;
                }
            }
        }
        
        return back()->with('success', "미매핑 카테고리 중 총 {$mappedCount}개가 추가로 자동 매핑되었습니다.");
    }

    /**
     * 상품 동기화 대시보드 화면
     */
    public function syncIndex()
    {
        $site = AffiliateSite::firstOrCreate(['name' => '대한판촉']);
        
        $mappedCodes = AffiliateCategoryMapping::where('affiliate_site_id', $site->id)
            ->whereNotNull('affiliate_category_code')
            ->pluck('dometopia_category_code')
            ->toArray();
            
        // 매핑된 카테고리에 속한 대상 상품의 고유 개수 (정상/노출/특정 scode 제외/link_yn='Y')
        $totalTargetCount = \Illuminate\Support\Facades\DB::table('fm_category_link')
            ->join('fm_goods', 'fm_category_link.goods_seq', '=', 'fm_goods.goods_seq')
            ->whereIn('fm_category_link.category_code', $mappedCodes)
            ->where('fm_goods.link_yn', 'Y')
            ->where('fm_goods.goods_view', 'Look')
            ->where('fm_goods.goods_status', 'normal')
            ->where(function($q) {
                $q->where('fm_goods.goods_scode', 'not like', 'AKS%')
                  ->where('fm_goods.goods_scode', 'not like', 'ATS%')
                  ->where('fm_goods.goods_scode', 'not like', 'GKM%')
                  ->where('fm_goods.goods_scode', 'not like', 'GUS%')
                  ->where('fm_goods.goods_scode', 'not like', 'TRO%')
                  ->orWhereNull('fm_goods.goods_scode'); // null인 경우도 포함 (조건에 따라)
            })
            ->distinct('fm_category_link.goods_seq')
            ->count('fm_category_link.goods_seq');
            
        // 서브쿼리로 대상 중 성공/실패 수 조회
        $successCount = \App\Models\AffiliateGoodsSync::where('affiliate_site_id', $site->id)
            ->where('sync_status', 'success')
            ->count();
            
        $failedCount = \App\Models\AffiliateGoodsSync::where('affiliate_site_id', $site->id)
            ->where('sync_status', 'failed')
            ->count();
            
        $pendingCount = $totalTargetCount - $successCount;
        if ($pendingCount < 0) $pendingCount = 0;

        return view('affiliate.settings.sync', compact('site', 'totalTargetCount', 'successCount', 'failedCount', 'pendingCount'));
    }

    /**
     * 상품 청크 단위 동기화 처리 (AJAX)
     */
    public function syncChunk(Request $request)
    {
        $site = AffiliateSite::firstOrCreate(['name' => '대한판촉']);
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
            
        // 미전송(또는 실패) 상품 추출 (정상/노출/특정 scode 제외/link_yn='Y')
        $query = \Illuminate\Support\Facades\DB::table('fm_category_link')
            ->join('fm_goods', 'fm_category_link.goods_seq', '=', 'fm_goods.goods_seq')
            ->whereIn('fm_category_link.category_code', $mappedCodes)
            ->where('fm_goods.link_yn', 'Y')
            ->where('fm_goods.goods_view', 'Look')
            ->where('fm_goods.goods_status', 'normal')
            ->where(function($q) {
                $q->where('fm_goods.goods_scode', 'not like', 'AKS%')
                  ->where('fm_goods.goods_scode', 'not like', 'ATS%')
                  ->where('fm_goods.goods_scode', 'not like', 'GKM%')
                  ->where('fm_goods.goods_scode', 'not like', 'GUS%')
                  ->where('fm_goods.goods_scode', 'not like', 'TRO%')
                  ->orWhereNull('fm_goods.goods_scode');
            });
            
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
        $scraper = new DaehanScraperService('dotob2b', '0000');
        
        $goodsList = \Illuminate\Support\Facades\DB::table('fm_goods')
            ->whereIn('goods_seq', $goodsSeqsToSync)
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