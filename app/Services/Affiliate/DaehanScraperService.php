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
     * 상품 스크래핑 등록
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
            $supplyPrice = round($arrivalPrice * 1.15);
        } else {
            $supplyPrice = $goods->supply_price ?? ($goods->price ?? 1000);
        }
        $sellingPrice = round($supplyPrice * (1 + ($marginRate / 100)));
        
        // 2-1. 기본수량 (1박스 입수량) 산출: 30만원 / 공급가
        $basicQty = $supplyPrice > 0 ? floor(300000 / $supplyPrice) : 100;
        if ($basicQty < 1) $basicQty = 1;

        // 3. 토큰 발급 (보안 우회)
        $tokenResponse = Http::withoutVerifying()->withOptions(['cookies' => $jar])
            ->withHeaders([
                'Referer' => $this->baseUrl . '/mypage/page.php?code=seller_goods_form',
                'X-Requested-With' => 'XMLHttpRequest'
            ])
            ->post($this->baseUrl . '/admin/ajax.token.php');
            
        $token = $tokenResponse->json('token') ?? '';

        $sel_ca1 = substr($affiliateCategory, 0, 3);
        $sel_ca2 = strlen($affiliateCategory) >= 6 ? substr($affiliateCategory, 0, 6) : '';
        $sel_ca3 = strlen($affiliateCategory) >= 9 ? substr($affiliateCategory, 0, 9) : '';
        $sel_ca4 = strlen($affiliateCategory) >= 12 ? substr($affiliateCategory, 0, 12) : '';
        $sel_ca5 = strlen($affiliateCategory) >= 15 ? substr($affiliateCategory, 0, 15) : '';

        // 3-1. 도매토피아 특수 컬럼 파싱 (goods_contents2, sub_info_desc)
        $contents2 = explode('|', $goods->goods_contents2 ?? '');
        $material = $contents2[0] ?? '';
        $size = $contents2[2] ?? '';
        $boxQty = $basicQty; // 30만원 기준 자동계산 수량 적용

        $subInfoDesc = json_decode($goods->sub_info_desc ?? '{}', true);
        $origin = $subInfoDesc['제조국 또는 원산지'] ?? '중국';
        
        $color = $goods->option()->where('option_title', 'like', '%색상%')->value('option1') ?? '';

        // 기타사항(인쇄 필독 안내) 병합
        $printNotice = "<br><br>★ 인쇄 필독 안내 ★<br>☆★ 50만원 이상 구매 시 기본 1도 인쇄비, 판비 전액 무료 진행 ★☆<br>★★ 2도 인쇄 이상 별도 문의 ★★<br>1. 중국 1도 인쇄: 개당 (낱개 기준) 80원 / 판비 2만원 별도가<br>2. 중국 2도 인쇄: 개당 (낱개 기준) 150원 / 판비 4만원 별도가<br>1. 모든 인쇄는 담당자가 시안, 문구, 위치, 비용 등을 고객님과 협의하여 진행합니다.<br>2. 모든 인쇄는 도매토피아 중국 물류 창고에서 직접 작업합니다.<br>3. 중국에서 인쇄 후 납기까지는 약 15일 내외 소요됩니다.";
        $explan = ($goods->contents ?? '') . $printNotice;

        // 4. 전송 파라미터 매핑 (대한판촉 양식)
        $multipartData = [
            ['name' => 'token', 'contents' => $token],
            ['name' => 'w', 'contents' => ''],
            ['name' => 'gs_id', 'contents' => ''],
            ['name' => 'mb_id', 'contents' => 'AP-10691'], // Use correct internal ID
            ['name' => 'new_cate_str', 'contents' => $affiliateCategory],
            ['name' => 'sel_ca1', 'contents' => $sel_ca1],
            ['name' => 'sel_ca2', 'contents' => $sel_ca2],
            ['name' => 'sel_ca3', 'contents' => $sel_ca3],
            ['name' => 'sel_ca4', 'contents' => $sel_ca4],
            ['name' => 'sel_ca5', 'contents' => $sel_ca5],
            ['name' => 'isopen', 'contents' => ($goods->goods_view === 'look' ? '2' : '1')],
            ['name' => 'notax', 'contents' => ($goods->tax === 'exempt' ? '1' : '0')], // 0: 과세, 1: 비과세
            ['name' => 'gname', 'contents' => $goods->goods_name ?? '테스트 상품'],
            ['name' => 'keywords', 'contents' => $goods->keyword ?? ''], // 검색키워드
            ['name' => 'gcode', 'contents' => $goods->goods_seq ?? time()],
            ['name' => 'explan', 'contents' => $explan],
            
            ['name' => 'it_qty_set', 'contents' => $basicQty], // 기본수량 (7단계 자동계산용)
            ['name' => 'daccount', 'contents' => $supplyPrice], // 상단 공급가격 (7단계 자동계산용)
            ['name' => 'is_free', 'contents' => '0'], // 0: 조건부, 1: 무료
            ['name' => 'sc_price', 'contents' => $shippingFee],
            ['name' => 'gd_baesong_price', 'contents' => $shippingFee], // 실제 폼 배송비 필드
            ['name' => 'opt_use', 'contents' => '0'],
            ['name' => 'image_use_yn', 'contents' => 'n'], // 이미지사용 금지
            ['name' => 'agree', 'contents' => 'on'], // 약관 동의
            ['name' => 'naver_shop_use', 'contents' => 'N'],
            ['name' => 'daum_shop_use', 'contents' => 'N'],
            
            // 상세 옵션 (규격, 색상, 재질, 원산지 등)
            ['name' => 'it_opt1_txt', 'contents' => $size ?: '기본옵션'], // 규격
            ['name' => 'it_opt2_txt', 'contents' => $color ?: '하단참조'], // 색상
            ['name' => 'it_opt4_txt', 'contents' => $material], // 재질
            ['name' => 'it_opt5_txt', 'contents' => 'OPP비닐포장'], // 케이스
            ['name' => 'it_opt6_txt', 'contents' => '별도표기'], // 제작기간
            ['name' => 'it_opt7_txt', 'contents' => $origin], // 원산지
            
            ['name' => 'it_opt10_txt', 'contents' => '가능'], // 선물포장 가능여부 (라디오)
            ['name' => 'it_seonmul', 'contents' => '300'], // 선물포장 비용
            
            ['name' => 'gd_baesong_ea', 'contents' => $basicQty], // 1박스당 입수량
            
            ['name' => 'it_inswae', 'contents' => '가능'], // 인쇄가능여부
            ['name' => 'it_opt3_txt[]', 'contents' => '실크인쇄'], // 인쇄방법 (체크박스)
            ['name' => 'print1_min_qty', 'contents' => $basicQty], // 인쇄 최소 주문수량
            ['name' => 'print2_min_qty', 'contents' => $basicQty], // 인쇄 없는 경우 최소 주문수량
            ['name' => 'it_fee_qty', 'contents' => $basicQty], // 무료인쇄 기본수량
            
            // 인쇄 옵션 (80원 등 엑셀 가이드라인)
            ['name' => 'opt_p_yn', 'contents' => 'y'],
            ['name' => 'opt_p1_ck', 'contents' => '1'],
            ['name' => 'opt_p1_sub1', 'contents' => '1도인쇄'],
            ['name' => 'opt_p1_price', 'contents' => '80'],

            ['name' => 'price_show', 'contents' => '1'], // 가격노출여부
            ['name' => 'buy_level', 'contents' => '10'],
            ['name' => 'stock_mod', 'contents' => '0'],
            ['name' => 'money_type', 'contents' => '0'],
            ['name' => 'money_yo', 'contents' => '%'],
            ['name' => 'money_dan', 'contents' => '0'],
            ['name' => 'img_mod', 'contents' => '0']
        ];
        
        // 4-1. 대표 이미지 처리 (URL 다운로드 후 임시파일로 첨부)
        $firstImage = $goods->images->where('image_type', 'main')->first() ?? $goods->images->first();
        $secondImage = $goods->images->where('image_type', 'main')->skip(1)->first();
        
        $tempFiles = [];
        
        $processImage = function($imgObj, $fieldName) use (&$multipartData, &$tempFiles) {
            if ($imgObj && $imgObj->image) {
                $imageUrl = $imgObj->image;
                if (!str_starts_with($imageUrl, 'http')) {
                    $imageUrl = 'https://dometopia.com' . (str_starts_with($imageUrl, '/') ? '' : '/') . $imageUrl;
                }
                try {
                    $imageContent = file_get_contents($imageUrl);
                    if ($imageContent) {
                        $tempFile = tmpfile();
                        fwrite($tempFile, $imageContent);
                        fseek($tempFile, 0);
                        $dummyPath = stream_get_meta_data($tempFile)['uri'];
                        $tempFiles[] = $tempFile; // 파일 핸들 유지 (가비지 컬렉션 방지)
                        
                        $multipartData[] = [
                            'name' => $fieldName,
                            'contents' => fopen($dummyPath, 'r'),
                            'filename' => basename($imageUrl)
                        ];
                    }
                } catch (\Exception $e) {
                }
            }
        };
        
        $processImage($firstImage, 'simg1');
        $processImage($secondImage, 'simg2');

        // 5. POST 전송
        try {
            $response = Http::withoutVerifying()->withOptions(['cookies' => $jar])
                ->asMultipart()->withHeaders([
                'Referer' => $this->baseUrl . '/mypage/page.php?code=seller_goods_form',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ])->post($this->baseUrl . '/mypage/seller_goods_form_update.php', $multipartData);
            
            $body = $response->body();
            \Illuminate\Support\Facades\Log::info("Daehan87 Response Body: \n" . $body);
            
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

            return [
                'success' => $isSuccess,
                'message' => $errorMessage ?? '성공',
                'affiliate_goods_code' => $goods->goods_seq ?? null,
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
        
        // 대한판촉은 HTML 태그가 아니라 Javascript 변수(multi_select)로 카테고리를 가지고 있음
        preg_match_all("/multi_select\['(.*?)'\] \+= '(.*?)';/", $html, $matches, PREG_SET_ORDER);
        
        $tree = [];
        $names = [];
        foreach ($matches as $match) {
            $parent = $match[1];
            $itemsRaw = ltrim($match[2], ','); // 앞에 붙은 콤마 제거
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
        // 재귀적으로 전체 경로(Full Path) 생성
        $buildPath = function($code, $path) use (&$buildPath, &$categories, $tree, $names) {
            $fullPath = $path ? $path . ' > ' . $names[$code] : $names[$code];
            // 리프 노드뿐 아니라 모든 중간 노드도 매핑 대상이 될 수 있으므로 모두 저장
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
        
        // Find tables that contain "주문번호"
        $crawler->filter('table')->each(function (Crawler $node, $i) use (&$orders) {
            $text = $node->text();
            if (strpos($text, '주문번호') !== false && preg_match('/주문번호.*?(\d{14})/', $text, $matches)) {
                $orderId = $matches[1];
                // Try to extract more details if needed
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
