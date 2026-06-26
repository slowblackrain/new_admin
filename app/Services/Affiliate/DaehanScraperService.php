<?php

namespace App\Services\Affiliate;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class DaehanScraperService
{
    protected $baseUrl;
    protected $loginId;
    protected $loginPassword;

    public function __construct($loginId = 'dotob2b', $loginPassword = '0000')
    {
        $this->baseUrl = 'https://www.daehan87.com';
        $this->loginId = $loginId;
        $this->loginPassword = $loginPassword;
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
        $client = $session['client'];
        
        // 1. 설정값 (마진, 배송비) 및 매핑된 카테고리 불러오기
        $site = \App\Models\AffiliateSite::firstOrCreate(['name' => '대한판촉']);
        $setting = \App\Models\AffiliateSetting::where('affiliate_site_id', $site->id)->first();
        $marginRate = $setting ? $setting->margin_rate : 0;
        
        // 카테고리 매핑 로직 (단순화: 첫번째 매핑된 카테고리 사용)
        $mapping = \App\Models\AffiliateCategoryMapping::where('affiliate_site_id', $site->id)
            ->whereNotNull('affiliate_category_code')
            ->first();
        $affiliateCategory = $mapping ? $mapping->affiliate_category_code : '001001'; // Default fallback
        
        // 2. 소비자가 산출 (공급가 + 마진)
        $supplyPrice = $goods->p_spl1 ?? 1000;
        $sellingPrice = round($supplyPrice * (1 + ($marginRate / 100)));

        // 3. 폼 파라미터 매핑 (대한판촉 양식)
        $multipartData = [
            ['name' => 'w', 'contents' => ''],
            ['name' => 'sel_ca_id', 'contents' => $affiliateCategory],
            ['name' => 'gname', 'contents' => $goods->goods_name ?? '테스트 상품'],
            ['name' => 'gcode', 'contents' => $goods->goods_seq ?? time()],
            ['name' => 'explan', 'contents' => $goods->goods_explan ?? '<p>상세설명입니다.</p>'],
            ['name' => 'p_spl1', 'contents' => $supplyPrice],
            ['name' => 'p_mny1', 'contents' => $sellingPrice],
            ['name' => 'it_qty_set', 'contents' => '1'],
            ['name' => 'image_use_yn', 'contents' => 'y'],
            ['name' => 'agree', 'contents' => 'on'], // 약관 동의
        ];
        
        // 이미지 추가 로직 (생략 - 실제 구현 시 URL에서 다운로드 후 첨부)
        
        $dummyImage = tmpfile();
        fwrite($dummyImage, 'dummy content');
        fseek($dummyImage, 0);
        $dummyPath = stream_get_meta_data($dummyImage)['uri'];

        $multipartData[] = [
            'name' => 'simg1',
            'contents' => fopen($dummyPath, 'r'),
            'filename' => 'dummy.jpg'
        ];

        // 4. POST 전송
        try {
            $response = $client->asMultipart()->withHeaders([
                'Referer' => $this->baseUrl . '/mypage/page.php?code=seller_goods_form'
            ])->post($this->baseUrl . '/mypage/seller_goods_form_update.php', $multipartData);
            
            // 등록 성공 여부를 응답 HTML 기반으로 판단
            // update.php는 통상적으로 alert를 띄우고 리다이렉트 시키거나 바로 리다이렉트 합니다.
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

            return [
                'success' => $isSuccess,
                'message' => $errorMessage ?? '성공',
                'affiliate_goods_code' => $goods->goods_seq,
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
    public function fetchCategories()
    {
        $session = $this->login();
        $client = $session['client'];
        
        $response = $client->get($this->baseUrl . '/mypage/page.php?code=seller_goods_form');
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
        
        return $categories;
    }

    /**
     * 주문 수집 스크래핑
     */
    public function fetchOrders($date = null)
    {
        $session = $this->login();
        $client = $session['client'];
        
        $response = $client->get($this->baseUrl . '/mypage/page.php?code=seller_itemorder_sel');
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
