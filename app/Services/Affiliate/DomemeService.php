<?php

namespace App\Services\Affiliate;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\Affiliate\DomemeInfoHelper;
use Illuminate\Support\Facades\Log;

class DomemeService
{
    protected $apiUrl;
    protected $apiKey;
    protected $apiId;
    protected $apiPw;

    public function __construct($apiKey = null)
    {
        $this->apiUrl = config('services.domeme.url', 'https://domeggook.com/ssl/api/');
        $this->apiKey = $apiKey ?: 'bc286764b63f54ff4ab856a60b245669'; // Fallback to hardcoded if null
        $this->apiId = config('services.domeme.id', 'dometopiab2b');
        $this->apiPw = config('services.domeme.pw', 'dometopia05*');
    }

    /**
     * 세션 발급 (Laravel Cache 사용)
     */
    protected function getSession()
    {
        return Cache::remember('domeme_session_id', 3600, function () {
            $response = Http::withoutVerifying()->asForm()->post($this->apiUrl, [
                'ver' => '1.1',
                'mode' => 'setLogin',
                'aid' => $this->apiKey,
                'id' => $this->apiId,
                'pw' => $this->apiPw,
                'om' => 'json',
                'loginKeep' => 'off',
                'ip' => request()->ip() ?? '115.68.216.162',
                'device' => 'Thrid Party'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['domeggook']['sId'])) {
                    return $data['domeggook']['sId'];
                }
            }
            
            try {
                Log::error('Domeme Login Failed', ['response' => $response->body()]);
            } catch (\Throwable $logException) {}
            throw new \Exception("도매매 로그인 세션 발급 실패: " . $response->body());
        });
    }

    /**
     * 상품 등록 API 연동
     */
    public function registerProduct($goods)
    {
        try {
            $sId = $this->getSession();
            
            $key = $goods->goods_code;
            $goodsName = $goods->goods_name_linkage ?: $goods->goods_name;
            
            // 카테고리
            $mappedCategory = DB::table('fm_category_link as cl')
                ->join('affiliate_category_mappings as acm', 'cl.category_code', '=', 'acm.dometopia_category_code')
                ->join('affiliate_sites as s', 'acm.affiliate_site_id', '=', 's.id')
                ->where('cl.goods_seq', $goods->goods_seq)
                ->where('s.name', '도매매')
                ->select('acm.affiliate_category_code')
                ->first();
                
            if (!$mappedCategory) {
                return ['success' => false, 'message' => '매핑된 카테고리가 없습니다.'];
            }
            
            // 검색어 (각 키워드 최대 10글자, 언더바 및 특수문자 제거, 최대 10개)
            $rawKeywords = explode(",", str_replace(" ", "", $goods->keyword ?? ''));
            $validKeywords = [];
            foreach ($rawKeywords as $k) {
                $k = trim(preg_replace("/[^\x{AC00}-\x{D7A3}a-zA-Z0-9]/u", "", $k));
                if (!empty($k)) {
                    $validKeywords[] = mb_substr($k, 0, 10);
                }
            }
            if (empty($validKeywords)) {
                // 키워드가 없으면 상품명 기반 추출
                $nameWords = explode(" ", $goodsName);
                foreach ($nameWords as $w) {
                    $w = trim(preg_replace("/[^\x{AC00}-\x{D7A3}a-zA-Z0-9]/u", "", $w));
                    if (mb_strlen($w) >= 2) {
                        $validKeywords[] = mb_substr($w, 0, 10);
                    }
                }
            }
            $validKeywords = array_values(array_unique($validKeywords));
            if (count($validKeywords) > 10) {
                $validKeywords = array_slice($validKeywords, 0, 10);
            }
            $keyword = implode(",", $validKeywords);
            
            // 사이즈, 무게 (기본값 필수 보장 - itemSize는 영문, 숫자, 쉼표, 공백만 허용)
            $tmp_size = explode('|', $goods->goods_contents2 ?? '');
            $rawSize = $tmp_size[2] ?? '';
            $rawSize = str_replace(['-', '/', '*', 'x', 'X', 'cm', 'mm'], [' ', ' ', ' ', ' ', ' ', '', ''], $rawSize);
            $itemSize = preg_replace("/[^A-Za-z0-9, ]/", "", $rawSize);
            $itemSize = trim(preg_replace('/\s+/', ' ', $itemSize));
            
            $rawWeight = $tmp_size[9] ?? '';
            $itemWeight = preg_replace("/[^A-Za-z0-9gG]/", "", $rawWeight);
            
            if (empty($itemSize)) {
                $itemSize = 'FREE';
            }
            if (empty($itemWeight)) {
                $itemWeight = '100g';
            }
            
            // 대표이미지
            $img = DB::table('fm_goods_image')
                ->where('goods_seq', $goods->goods_seq)
                ->where('cut_number', 1)
                ->where('image_type', 'large')
                ->first();
            $img_url = $img ? str_replace("/data/goods", "https://dmtusr.vipweb.kr", $img->image) : '';
            
            // 상품상세
            if (substr($goods->goods_scode, 0, 3) == "GDF" || substr($goods->goods_scode, 0, 3) == "GDH") {
                $detail = "<img src='https://dometopia.com/data/goods/goods_img/gtd_title.jpg' border='0'><br><img src='".$goods->img_contents."' border=0>";
            } else {
                $detail = "<img src='".$goods->img_contents."' border=0>";
            }
            
            // 정보고시
            $infoDutyData = DomemeInfoHelper::getInfoDuty($goods);
            
            // 가격 정보
            $supply_price_step1 = round((($goods->price - $goods->mtype_discount) * 1.1 / 10) * 10, -1);
            $supply_price_step2 = round((($goods->price - $goods->fifty_discount) * 1.1 / 10) * 10, -1);
            $supply_price_step3 = round((($goods->price - $goods->hundred_discount) * 1.1 / 10) * 10, -1);
            
            $ea_step1 = ceil(100000 / ($goods->price ?: 1));
            $ea_step2 = $goods->fifty_discount_ea;
            $ea_step3 = $goods->hundred_discount_ea;
            
            $itemPrice = $ea_step1 . ":" . $supply_price_step1 . PHP_EOL;
            if ($ea_step2 > 1) $itemPrice .= $ea_step2 . ":" . $supply_price_step2 . PHP_EOL;
            if ($ea_step3 > 1) $itemPrice .= $ea_step3 . ":" . $supply_price_step3;
            
            $supplyAmt = "1:" . $supply_price_step1;
            
            // 기존 등록 여부 체크
            $existingSync = DB::table('affiliate_goods_syncs')
                ->join('affiliate_sites as s', 'affiliate_goods_syncs.affiliate_site_id', '=', 's.id')
                ->where('s.name', '도매매')
                ->where('goods_seq', $goods->goods_seq)
                ->where('sync_status', 'success')
                ->first();
                
            $dome_method = $existingSync ? "update" : "insert";
            $itemNo = $existingSync ? $existingSync->affiliate_goods_code : '';
            if ($existingSync && empty($itemNo)) {
                $dome_method = "insert";
            }
            
            $item = [
                'itemNo' => $itemNo,
                'market' => '도매꾹,도매매',
                'itemSection' => '직접판매',
                'secretItem' => 'N',
                'itemTitle' => $goodsName,
                'itemKeyword' => $keyword,
                'itemCategoryInt' => $mappedCategory->affiliate_category_code,
                'itemCountry' => '상세정보별도표기',
                'itemCode' => $goods->goods_scode,
                'itemCompany' => '(주)트리',
                'itemSafetyCert' => 'A99:상세상품설명참조',
                'onlyForAdult' => 'N',
                'itemSize' => $itemSize,
                'itemWeight' => $itemWeight,
                'itemCustomCode' => $key,
                'itemImage' => $img_url,
                'itemMemoItem' => $detail,
                'imageAllow' => 'Y',
                'imageMemo' => '이미지 사용 가능합니다',
                'infoDutyType' => $infoDutyData['infoDutyType'],
                'infoDuty' => $infoDutyData['infoDuty'],
                'infoDutyCommon' => '전체상세정보별도표시',
                'comOnly' => 'N',
                'itemPrice' => $itemPrice,
                'byUnitQty' => 'N',
                'useNego' => 'Y',
                'supplyAmt' => $supplyAmt,
                'itemOptUse' => 'N',
                'totalQty' => '999',
                'taxAdded' => '과세',
                'deliveryMethod' => '택배',
                'deliveryPeriod' => 1,
                'deliveryWho' => '선결제:고정배송비',
                'deliBuyerAmt' => '2800',
                'deliveryWhoSupply' => '선결제:고정배송비',
                'deliBuyerAmtSupply' => '2800',
                'deliMerge' => 'SA0058070',
                'returnShippingArea' => 'SA0058069',
                'returnDeliAmt' => '2800',
                'returnDeliAmtDouble' => 'Y',
                'periodReg' => 365,
                'useDisplay' => 'Y'
            ];
            
            $itemKey = "item[".$key."]";
            $output = json_encode($item, JSON_UNESCAPED_UNICODE);
            
            $response = Http::withoutVerifying()->asForm()->post($this->apiUrl, [
                'mode' => 'setItemBatch',
                'ver' => '4.1',
                'id' => $this->apiId,
                'aid' => $this->apiKey,
                'sId' => $sId,
                $itemKey => $output,
                'model' => $dome_method,
                'oe' => 'utf-8',
                'om' => 'json'
            ]);
            
            if (!$response->successful()) {
                return ['success' => false, 'message' => 'API 요청 실패: ' . $response->status()];
            }
            
            $data = $response->json();
            if (!is_array($data) || !isset($data['domeggook'])) {
                return ['success' => false, 'message' => 'API 응답 파싱 실패 또는 유효하지 않은 응답 형식'];
            }
            $resultItems = $data['domeggook']['items'] ?? [];
            
            foreach ($resultItems as $resItem) {
                if ($resItem['key'] == $key) {
                    if ($resItem['result'] === 'SUCCESS') {
                        return [
                            'success' => true,
                            'affiliate_goods_code' => $resItem['no'],
                            'message' => '성공'
                        ];
                    } else {
                        return [
                            'success' => false,
                            'message' => $resItem['msg'] ?? '오류 발생'
                        ];
                    }
                }
            }
            
            return ['success' => false, 'message' => '응답 데이터 매칭 실패'];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')'
            ];
        }
    }

    /**
     * 카테고리 목록 조회
     */
    public function fetchCategories()
    {
        $url = 'https://domeggook.com/ssl/api/?ver=1.0&mode=getCategoryList&aid=' . $this->apiKey . '&om=json';
        
        $response = Http::withoutVerifying()->get($url);
        
        if (!$response->successful()) {
            return [];
        }
        
        $data = $response->json();
        
        if (!isset($data['domeggook']['category']['item'])) {
            return [];
        }

        $items = $data['domeggook']['category']['item'];
        $leafCategories = [];

        $traverse = function($nodes, $parentName = '') use (&$traverse, &$leafCategories) {
            foreach ($nodes as $node) {
                $currentName = $parentName === '' ? $node['name'] : $parentName . ' > ' . $node['name'];
                
                if (isset($node['child']) && is_array($node['child']) && count($node['child']) > 0) {
                    $traverse($node['child'], $currentName);
                } else {
                    if (isset($node['locked']) && $node['locked'] === 'FALSE' && !empty($node['int'])) {
                        $leafCategories[] = [
                            'code' => (string)$node['int'],
                            'name' => $currentName
                        ];
                    }
                }
            }
        };

        $traverse($items);

        usort($leafCategories, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $leafCategories;
    }
}
