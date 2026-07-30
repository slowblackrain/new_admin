<?php

namespace App\Services\Affiliate;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class DaehanScraperService
{
    protected $baseUrl;
    protected $loginId;
    protected $loginPassword;

    public function __construct($loginId = null, $loginPassword = null)
    {
        $this->baseUrl = 'https://daehan87.com';
        
        $site = \App\Models\AffiliateSite::firstOrCreate(['name' => '대한판촉']);
        $setting = \App\Models\AffiliateSetting::where('affiliate_site_id', $site->id)->first();
        
        $this->loginId = $loginId ?? ($setting->login_id ?? 'dotob2b');
        $this->loginPassword = $loginPassword ?? ($setting->login_password ?? '0000');
    }

    /**
     * 로그인 세션 반환
     */
    public function login()
    {
        $jar = new \GuzzleHttp\Cookie\CookieJar();
        $client = Http::withoutVerifying()->withOptions(['cookies' => $jar]);
        
        $client->asForm()->post($this->baseUrl . '/bbs/login_check2.php', [
            'mb_id' => $this->loginId,
            'mb_password' => $this->loginPassword
        ]);

        return ['client' => $client, 'jar' => $jar];
    }

    /**
     * 상품 스크래핑 등록 / 수정
     */
    public function registerProduct($goods)
    {
        $session = $this->login();
        $jar = $session['jar'];
        
        // 1. 설정값 (마진, 배송비) 및 매핑된 카테고리 불러오기
        $site = \App\Models\AffiliateSite::firstOrCreate(['name' => '대한판촉']);
        $setting = \App\Models\AffiliateSetting::where('affiliate_site_id', $site->id)->first();
        $marginRate = $setting ? $setting->margin_rate : 0;
        $shippingFee = $setting ? $setting->shipping_fee : 3000;
        
        // 카테고리 매핑 로직 (해당 상품의 카테고리 중 첫번째 매핑된 것 사용)
        $mappedCategory = \Illuminate\Support\Facades\DB::table('fm_category_link as cl')
            ->join('affiliate_category_mappings as acm', 'cl.category_code', '=', 'acm.dometopia_category_code')
            ->where('cl.goods_seq', $goods->goods_seq)
            ->where('acm.affiliate_site_id', $site->id)
            ->whereNotNull('acm.affiliate_category_code')
            ->value('acm.affiliate_category_code');
            
        $affiliateCategory = $mappedCategory ?: '001001'; // Default fallback

        // 2. 공급가 및 소비자가 산출 (도착가 * 1.15)
        $cbmArr = explode('|', $goods->multi_discount_cbm ?? '');
        $arrivalPrice = isset($cbmArr[6]) ? (float)$cbmArr[6] : 0;
        
        if ($arrivalPrice > 0) {
            $supplyPrice = round($arrivalPrice * 1.15, -1);
        } else {
            $price = (float)($goods->price ?? 0);
            $mtype_discount = (float)($goods->mtype_discount ?? 0);
            $wholesalePrice = $price - $mtype_discount;
            $supplyPrice = round($wholesalePrice > 0 ? $wholesalePrice : 1000, -1);
        }
        $sellingPrice = round($supplyPrice * (1 + ($marginRate / 100)), -1);
        $shippingFee = round((float)($goods->shipping_price ?: 3000), -1);
        
        // 2-1. 기본수량 (1박스 입수량) 산출: 30만원 / 공급가
        $basicQty = $supplyPrice > 0 ? floor(300000 / $supplyPrice) : 100;
        if ($basicQty < 1) $basicQty = 1;

        // 기존 동기화 이력 확인 (수정 전송 vs 최초 신규 전송 구분)
        $existingSync = \App\Models\AffiliateGoodsSync::where('affiliate_site_id', $site->id)
            ->where('goods_seq', $goods->goods_seq)
            ->where('sync_status', 'success')
            ->first();

        $w = '';
        $gsId = '';
        $formUrl = $this->baseUrl . '/mypage/page.php?code=seller_goods_form';

        if ($existingSync && !empty($existingSync->affiliate_goods_code)) {
            $savedCode = trim($existingSync->affiliate_goods_code);
            // 오직 K-코드로 정상 발급되어 등록된 이력이 있는 경우만 수정(u) 모드 진행
            // (기존 구버전 오류로 숫자 코드가 저장되어 있던 경우 신규 전송으로 K-코드 정상 발급)
            if (str_starts_with($savedCode, 'K')) {
                $foundGsId = $this->findGsIdByGcode($jar, $savedCode);
                if ($foundGsId) {
                    $gsId = $foundGsId;
                    $w = 'u';
                    $formUrl .= '&w=u&gs_id=' . $gsId;
                }
            }
        }

        // 3. 대한판촉 등록/수정 폼 페이지 요청 및 대한판촉 부여 K-코드 추출
        $formResponse = Http::withoutVerifying()->withOptions(['cookies' => $jar])
            ->get($formUrl);
        $formHtml = $formResponse->body();
        
        $autoGcode = '';
        if (preg_match('/<input[^>]*name=["\']gcode["\'][^>]*value=["\']?(K[0-9]{5,8})["\']?/i', $formHtml, $kMatches)) {
            $autoGcode = trim($kMatches[1]);
        } elseif (preg_match('/name=["\']gcode["\'][^>]*value=["\']?(K[0-9]{5,8})["\']?/i', $formHtml, $kMatches)) {
            $autoGcode = trim($kMatches[1]);
        } elseif (preg_match('/<input[^>]*name=["\']gcode["\'][^>]*>/i', $formHtml, $inputMatches)) {
            if (preg_match('/value=["\']([^"\'\s>]+)["\']/i', $inputMatches[0], $valMatches)) {
                $autoGcode = trim($valMatches[1]);
            }
        }
        
        // 최초 등록(w='') 시 대한판촉 K-코드가 파싱되지 않은 경우 에러 처리
        if (empty($w) && (empty($autoGcode) || !str_starts_with($autoGcode, 'K'))) {
            return [
                'success' => false,
                'message' => '대한판촉 공식 상품코드(K-코드) 발급 실패. (대한판촉 폼 응답 확인 필요)'
            ];
        }

        $tokenResponse = Http::withoutVerifying()->withOptions(['cookies' => $jar])
            ->withHeaders([
                'Referer' => $formUrl,
                'X-Requested-With' => 'XMLHttpRequest'
            ])
            ->post($this->baseUrl . '/admin/ajax.token.php');
            
        $token = $tokenResponse->json('token') ?? '';

        $sel_ca1 = substr($affiliateCategory, 0, 3);
        $sel_ca2 = strlen($affiliateCategory) >= 6 ? substr($affiliateCategory, 0, 6) : '';
        $sel_ca3 = strlen($affiliateCategory) >= 9 ? substr($affiliateCategory, 0, 9) : '';
        $sel_ca4 = strlen($affiliateCategory) >= 12 ? substr($affiliateCategory, 0, 12) : '';
        $sel_ca5 = strlen($affiliateCategory) >= 15 ? substr($affiliateCategory, 0, 15) : '';

        // 특수 컬럼 파싱
        $contents2 = explode('|', $goods->goods_contents2 ?? '');
        $material = $contents2[0] ?? '';
        $size = $contents2[2] ?? '';
        $subInfoDesc = json_decode($goods->sub_info_desc ?? '{}', true);
        $origin = $subInfoDesc['제조국 또는 원산지'] ?? '중국';
        $color = $goods->option()->where('option_title', 'like', '%색상%')->value('option1') ?? '';

        $printNotice = "<br><br>★ 인쇄 필독 안내 ★<br>☆★ 50만원 이상 구매 시 기본 1도 인쇄비, 판비 전액 무료 진행 ★☆<br>★★ 2도 인쇄 이상 별도 문의 ★★<br>1. 중국 1도 인쇄: 개당 (낱개 기준) 80원 / 판비 2만원 별도가<br>2. 중국 2도 인쇄: 개당 (낱개 기준) 150원 / 판비 4만원 별도가<br>1. 모든 인쇄는 담당자가 시안, 문구, 위치, 비용 등을 고객님과 협의하여 진행합니다.<br>2. 모든 인쇄는 도매토피아 중국 물류 창고에서 직접 작업합니다.<br>3. 중국에서 인쇄 후 납기까지는 약 15일 내외 소요됩니다.";

        ini_set('memory_limit', '512M');

        $cropBottomPx = 700; 
        $introImageUrl = "https://dotob2b.cache.iwinv.net/Image/topp.jpg";

        $memoHtml = '<div style="text-align: center; margin-bottom: 20px;">';
        $memoHtml .= '<img src="' . $introImageUrl . '" style="max-width: 100%; display: inline-block;" />';
        $memoHtml .= '</div>';
        $memoHtml .= '<div style="overflow: hidden; margin: 0 auto; text-align: center;">';
        $memoHtml .= '<div style="margin-bottom: -' . $cropBottomPx . 'px;">';
        $memoHtml .= '<img src="' . ($goods->img_contents ?? '') . '" style="max-width: 100%; display: inline-block;" />';
        $memoHtml .= '</div></div>';

        $multipartData = [
            ['name' => 'token', 'contents' => $token],
            ['name' => 'w', 'contents' => $w],
            ['name' => 'gs_id', 'contents' => $gsId],
            ['name' => 'mb_id', 'contents' => 'AP-10691'],
            ['name' => 'new_cate_str', 'contents' => $affiliateCategory],
            ['name' => 'sel_ca1', 'contents' => $sel_ca1],
            ['name' => 'sel_ca2', 'contents' => $sel_ca2],
            ['name' => 'sel_ca3', 'contents' => $sel_ca3],
            ['name' => 'sel_ca4', 'contents' => $sel_ca4],
            ['name' => 'sel_ca5', 'contents' => $sel_ca5],
            
            ['name' => 'cate', 'contents' => $affiliateCategory],
            ['name' => 'it_basic', 'contents' => strip_tags(html_entity_decode($goods->summary_info ?? ''))],
            ['name' => 'gname', 'contents' => $goods->goods_name ?? '테스트 상품'],
            
            ['name' => 'isopen', 'contents' => '1'],
            ['name' => 'notax', 'contents' => '1'],
            
            ['name' => 'keywords', 'contents' => $goods->keyword ?? ''],
            ['name' => 'gcode', 'contents' => $autoGcode],
            ['name' => 'memo', 'contents' => $memoHtml],
            
            ['name' => 'it_qty_set', 'contents' => $basicQty],
            ['name' => 'daccount', 'contents' => $supplyPrice],
            
            // 소비자가격은 빈 값 전송
            ['name' => 'p_qty1', 'contents' => $basicQty * 1],
            ['name' => 'p_spl1', 'contents' => $supplyPrice],
            ['name' => 'p_mny1', 'contents' => ''],
            
            ['name' => 'p_qty2', 'contents' => $basicQty * 2],
            ['name' => 'p_spl2', 'contents' => $supplyPrice],
            ['name' => 'p_mny2', 'contents' => ''],
            
            ['name' => 'p_qty3', 'contents' => $basicQty * 3],
            ['name' => 'p_spl3', 'contents' => $supplyPrice],
            ['name' => 'p_mny3', 'contents' => ''],
            
            ['name' => 'p_qty4', 'contents' => $basicQty * 5],
            ['name' => 'p_spl4', 'contents' => $supplyPrice],
            ['name' => 'p_mny4', 'contents' => ''],
            
            ['name' => 'p_qty5', 'contents' => $basicQty * 10],
            ['name' => 'p_spl5', 'contents' => $supplyPrice],
            ['name' => 'p_mny5', 'contents' => ''],
            
            ['name' => 'p_qty6', 'contents' => $basicQty * 20],
            ['name' => 'p_spl6', 'contents' => $supplyPrice],
            ['name' => 'p_mny6', 'contents' => ''],
            
            ['name' => 'p_qty7', 'contents' => $basicQty * 50],
            ['name' => 'p_spl7', 'contents' => $supplyPrice],
            ['name' => 'p_mny7', 'contents' => ''],
            ['name' => 'is_free', 'contents' => '0'],
            ['name' => 'sc_price', 'contents' => $shippingFee],
            ['name' => 'gd_baesong_price', 'contents' => $shippingFee],
            ['name' => 'opt_use', 'contents' => '0'],
            ['name' => 'image_use_yn', 'contents' => 'n'],
            ['name' => 'agree', 'contents' => 'on'],
            ['name' => 'it_point_type', 'contents' => '0'],
            ['name' => 'naver_shop_use', 'contents' => '1'],
            ['name' => 'daum_shop_use', 'contents' => '1'],
            ['name' => 'adm_gcode', 'contents' => $goods->goods_seq ?? $goods->goods_code ?? ''],
            
            ['name' => 'it_opt1_txt', 'contents' => $size ?: '기본옵션'],
            ['name' => 'it_opt2_txt', 'contents' => $color ?: '하단참조'],
            ['name' => 'it_opt4_txt', 'contents' => $material],
            ['name' => 'it_opt5_txt', 'contents' => 'OPP비닐포장'],
            ['name' => 'it_opt6_txt', 'contents' => '별도표기'],
            ['name' => 'it_opt7_txt', 'contents' => $origin],
            
            ['name' => 'it_opt10_txt', 'contents' => '가능'],
            ['name' => 'it_seonmul', 'contents' => '300'],
            
            ['name' => 'gd_baesong_ea', 'contents' => $basicQty],
            
            ['name' => 'it_inswae', 'contents' => '가능'],
            ['name' => 'it_opt3_txt[]', 'contents' => '실크인쇄'],
            ['name' => 'print1_min_qty', 'contents' => $basicQty],
            ['name' => 'print2_min_qty', 'contents' => $basicQty],
            ['name' => 'it_fee_qty', 'contents' => 0],
            
            ['name' => 'opt_4_yn', 'contents' => '1'],
            ['name' => 'opt_p4_sub1', 'contents' => '80'],
            ['name' => 'opt_p4_sub2', 'contents' => '150'],
            ['name' => 'opt_p4_sub3', 'contents' => '250'],

            ['name' => 'price_show', 'contents' => '1'],
            ['name' => 'buy_level', 'contents' => '10'],
            ['name' => 'stock_mod', 'contents' => '0'],
            ['name' => 'money_type', 'contents' => '0'],
            ['name' => 'money_yo', 'contents' => '%'],
            ['name' => 'img_mod', 'contents' => '0']
        ];

        // 대표 이미지 처리
        $imagesList = \Illuminate\Support\Facades\DB::table('fm_goods_image')
            ->where('goods_seq', $goods->goods_seq)
            ->get();
            
        $mainImages = $imagesList->where('image_type', 'main')->values();
        if ($mainImages->isEmpty()) {
            $mainImages = $imagesList->values();
        }
        
        $firstImage = $mainImages->first();
        $secondImage = $firstImage;
        
        $tempFiles = [];
        $processImage = function($imgObj, $fieldName) use (&$multipartData, &$tempFiles) {
            if ($imgObj && $imgObj->image) {
                $imageUrl = $imgObj->image;
                if (!str_starts_with($imageUrl, 'http')) {
                    $imageUrl = 'https://dometopia.com' . (str_starts_with($imageUrl, '/') ? '' : '/') . $imageUrl;
                }
                try {
                    $tempPath = storage_path('app/tmp_' . uniqid() . '_' . basename($imageUrl));
                    if (@copy($imageUrl, $tempPath) && file_exists($tempPath) && filesize($tempPath) > 0) {
                        $stream = @fopen($tempPath, 'r');
                        if (is_resource($stream)) {
                            $multipartData[] = [
                                'name' => $fieldName,
                                'contents' => $stream,
                                'filename' => basename($imageUrl)
                            ];
                            $tempFiles[] = $tempPath;
                        } else {
                            @unlink($tempPath);
                        }
                    }
                } catch (\Exception $e) {
                }
            }
        };
        
        $processImage($firstImage, 'simg1');
        $processImage($secondImage, 'simg2');

        // POST 전송
        try {
            $response = Http::withoutVerifying()->withOptions(['cookies' => $jar])
                ->asMultipart()->withHeaders([
                'Referer' => $formUrl,
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ])->post($this->baseUrl . '/mypage/seller_goods_form_update.php', $multipartData);
            
            unset($multipartData);
            gc_collect_cycles();
            
            if (isset($tempFiles) && is_array($tempFiles)) {
                foreach ($tempFiles as $tempPath) {
                    @unlink($tempPath);
                }
            }
            
            $body = $response->body();
            
            $isSuccess = strpos($body, '등록되었습니다') !== false 
                         || $response->status() == 302 
                         || strpos($body, 'location.replace') !== false;
                          
            $errorMessage = null;
            if (!$isSuccess) {
                if (strpos($body, '올바른 방법으로 이용해 주십시오') !== false) {
                    $errorMessage = '접근 거부됨 (올바른 방법으로 이용해 주십시오)';
                } else {
                    $errorMessage = '알 수 없는 응답: ' . substr(strip_tags($body), 0, 50);
                }
            }

            // 동기화 결과 코드 결정 (gs_id 또는 autoGcode)
            $savedCode = $gsId;
            if ($isSuccess && empty($savedCode)) {
                $savedCode = $this->findGsIdByGcode($jar, $autoGcode) ?: $autoGcode;
            }

            return [
                'success' => $isSuccess,
                'message' => $errorMessage ?? '성공',
                'affiliate_goods_code' => $savedCode ?: $autoGcode ?: ($goods->goods_seq ?? null),
                'selling_price' => $sellingPrice
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * gcode로 대한판촉 gs_id 조회
     */
    public function findGsIdByGcode($jar, $gcode)
    {
        try {
            $res = Http::withoutVerifying()->withOptions(['cookies' => $jar])
                ->get($this->baseUrl . '/mypage/page.php?code=seller_goods_list2&sfl=gcode&stx=' . urlencode($gcode));
            $html = $res->body();
            if (preg_match('/name=["\']gs_id\[\d+\]["\'][^>]*value=["\'](\d+)["\']/i', $html, $m)) {
                return $m[1];
            }
            if (preg_match('/gs_id=(\d+)/i', $html, $m)) {
                return $m[1];
            }
        } catch (\Exception $e) {
        }
        return null;
    }

    /**
     * 대한판촉의 상품 카테고리 목록을 스크래핑하여 반환
     */
    public function fetchCategories($force = false)
    {
        $filePath = storage_path('app/affiliate/daehan_categories.json');

        if (!$force && file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $data = json_decode($content, true);
            if (is_array($data)) {
                return $data;
            }
        }

        $session = $this->login();
        $jar = $session['jar'];
        
        $response = Http::withoutVerifying()->withOptions(['cookies' => $jar])
                        ->get($this->baseUrl . '/mypage/page.php?code=seller_goods_form');
        $html = $response->body();
        
        preg_match_all("/multi_select\['(.*?)'\] \+= '(.*?)';/", $html, $matches, PREG_SET_ORDER);
        
        $tree = [];
        $names = [];
        foreach ($matches as $match) {
            $parent = $match[1];
            $itemsRaw = ltrim($match[2], ',');
            $items = explode(',', $itemsRaw);
            foreach ($items as $item) {
                if (strpos($item, '|') !== false) {
                    list($code, $name) = explode('|', $item);
                    $tree[$parent][] = $code;
                    $names[$code] = trim($name);
                }
            }
        }

        $categories = [];
        $buildPath = function($code, $path) use (&$buildPath, &$categories, $tree, $names) {
            $fullPath = $path ? $path . ' > ' . $names[$code] : $names[$code];
            $categories[$code] = $fullPath;
            
            if (isset($tree[$code])) {
                foreach ($tree[$code] as $childCode) {
                    $buildPath($childCode, $fullPath);
                }
            }
        };
        
        if (isset($tree['first'])) {
            foreach ($tree['first'] as $rootCode) {
                $buildPath($rootCode, '');
            }
        }
        
        if (!empty($categories)) {
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }
            file_put_contents($filePath, json_encode($categories, JSON_UNESCAPED_UNICODE));
        }

        return $categories;
    }

    /**
     * 주문 수집 스크래핑
     */
    public function fetchOrders($date = null)
    {
        $session = $this->login();
        $jar = $session['jar'];
        
        $response = Http::withoutVerifying()->withOptions(['cookies' => $jar])
                        ->get($this->baseUrl . '/mypage/page.php?code=seller_itemorder_sel');
        $html = $response->body();
        $crawler = new Crawler($html);
        
        $orders = [];
        
        $crawler->filter('table')->each(function (Crawler $node, $i) use (&$orders) {
            $text = $node->text();
            if (strpos($text, '주문번호') !== false && preg_match('/주문번호.*?(\d{14})/', $text, $matches)) {
                $orderId = $matches[1];
                preg_match('/(\d{2}-\d{2}-\d{2})/', $text, $dateMatches);
                
                $orders[] = [
                    'affiliate_order_id' => $orderId,
                    'date' => $dateMatches[1] ?? null,
                    'raw_text' => mb_substr(trim(preg_replace('/\s+/', ' ', $text)), 0, 100)
                ];
            }
        });

        return [
            'success' => true,
            'orders' => $orders
        ];
    }
}
