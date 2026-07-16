<?php

namespace App\Services\Affiliate;

use Illuminate\Support\Facades\Http;

class OwnerclanService
{
    protected $apiUrl;
    protected $authUrl;
    protected $venderConfig;
    protected $jwtToken = null;

    public function __construct()
    {
        $this->apiUrl = config('services.ownerclan.url', 'https://api.ownerclan.com/v1');
        
        // 데이터베이스 fm_config에서 오너클랜 인증 정보 조회
        $authUrlRow = \Illuminate\Support\Facades\DB::table('fm_config')
            ->where('groupcd', 'ownerclan')
            ->where('codecd', 'pro_url')
            ->first();
            
        $venderRow = \Illuminate\Support\Facades\DB::table('fm_config')
            ->where('groupcd', 'ownerclan')
            ->where('codecd', 'vender')
            ->first();

        $this->authUrl = $authUrlRow ? $authUrlRow->value : 'https://auth.ownerclan.com/auth';
        
        if ($venderRow && $venderRow->value) {
            $this->venderConfig = json_decode($venderRow->value, true);
        } else {
            $this->venderConfig = [
                'service' => 'ownerclan',
                'userType' => 'vendor',
                'username' => config('services.ownerclan.id', 'dummy_id'),
                'password' => config('services.ownerclan.password', 'dummy_pwd')
            ];
        }
    }

    /**
     * 오너클랜 인증 (JWT 획득)
     */
    protected function authenticate()
    {
        if ($this->jwtToken) return $this->jwtToken;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => request()->getHost()
            ])->withoutVerifying()->post($this->authUrl, $this->venderConfig);

            if ($response->successful()) {
                $token = trim($response->body());
                // JSON 형태가 아니고 순수 JWT 스트링으로 반환됨
                if (!empty($token) && str_starts_with($token, 'eyJ')) {
                    $this->jwtToken = $token;
                    return $this->jwtToken;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Ownerclan Auth Error: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * 상품 등록 API 연동 (createItem GraphQL)
     */
    public function registerProduct($goods)
    {
        $token = $this->authenticate();
        if (!$token) {
            return [
                'success' => false,
                'message' => '오너클랜 API 인증 실패'
            ];
        }

        $createQuery = 'mutation createItem($input: ItemInput!) {
          createItem(input: $input, autoTranslate: false) {
            key
            id
            status
          }
        }';

        // 1. 전체 상품 정보 로드
        $goodsRecord = \Illuminate\Support\Facades\DB::table('fm_goods')
            ->where('goods_seq', $goods->goods_seq)
            ->first();

        if (!$goodsRecord) {
            return ['success' => false, 'message' => '상품 정보를 찾을 수 없습니다.'];
        }

        // 2. 가격 계산 (옵션의 d_price 대신 price 와 할인금액으로 계산)
        $option = \Illuminate\Support\Facades\DB::table('fm_goods_option')
            ->where('goods_seq', $goods->goods_seq)
            ->where('default_option', 'y')
            ->first();
            
        $price = (float)($option ? $option->price : 0);
        $mtype_discount = (float)($goodsRecord->mtype_discount ?? 0);
        $d_price = $price - $mtype_discount; // 도매가
        $s_price = round((float)($option ? $option->consumer_price : 0), -1);
        
        $dds_price = 0;
        // GKM 포함: (도매가 * 0.97)에 부가세 10% 추가 (* 1.1)
        if ($goodsRecord->goods_scode && strpos($goodsRecord->goods_scode, 'GKM') !== false) {
            $dds_price = round(($d_price * 0.97) * 1.1, -1);
        } else {
            // 나머지 전체: 도매가에 부가세 10% 추가 (* 1.1)
            $dds_price = round($d_price * 1.1, -1);
        }

        // 3. 연동명 특수문자 제거 로직 (띄어쓰기는 유지하도록 수정)
        $g_name = preg_replace("/[#\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$<>\[\]\{\}]/i", "", $goodsRecord->goods_name_linkage);
        $g_name = str_replace("＆", "앤", $g_name);
        if (empty(trim($g_name))) {
            $g_name = preg_replace("/[#\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$<>\[\]\{\}]/i", "", $goodsRecord->goods_name); // fallback
        }
        
        // 오너클랜 상품명 중복 에러 방지를 위해 상품코드(goods_scode)를 상품명 뒤에 병합
        $scode = $goodsRecord->goods_scode ?? $goodsRecord->goods_seq;
        $g_name = trim($g_name) . ' ' . $scode;

        // 4. 검색 키워드 추출
        $keyword = [];
        $keywords = str_replace('_', '', $goodsRecord->keyword);
        $arr_keyword = explode(',', $keywords);
        foreach($arr_keyword as $key_keyword => $keyword_tmp) {
            if ($key_keyword < 5) {
                $keyword_tmp = trim($keyword_tmp);
                if ($keyword_tmp) {
                    $keyword[] = $keyword_tmp;
                }
            }
        }
        if (empty($keyword)) {
            $keyword = [$goodsRecord->goods_name]; // API 요구사항용 fallback
        }

        // 5. 카테고리 매칭
        $owner_cate = \Illuminate\Support\Facades\DB::table('fm_goods_owner')
            ->where('goods_code', $goodsRecord->goods_code)
            ->value('owner_cate');
        $cate = $owner_cate ? $owner_cate : "50003775";

        // 6. 이미지 URL 변경 (list1 이미지 사용하되 서버명 치환)
        $img = \Illuminate\Support\Facades\DB::table('fm_goods_image')
            ->where('goods_seq', $goods->goods_seq)
            ->where('image_type', 'list1')
            ->first();
        $imageUrl = $img 
            ? str_replace('/data/goods/goods_img', 'http://dmtusr.vipweb.kr/goods_img', $img->image)
            : 'https://via.placeholder.com/500';

        // 7. 상세설명 (img_contents 활용)
        $content = '<center><img src="'.$goodsRecord->img_contents.'" alt=""></center>';

        $inputVariables = [
            'input' => [
                'name' => [
                    'ko_KR' => $g_name
                ],
                'model' => $goodsRecord->goods_scode ?? '',
                'content' => $content,
                'production' => '(주)트리', // 기존 고정값
                'origin' => '해외|아시아|중국', // 기존 고정값
                'images' => [$imageUrl],
                'searchKeywords' => $keyword,
                'category' => $cate,
                'options' => [
                    [
                        'optionAttributes' => [],
                        'price' => [
                            'currency' => 'KRW',
                            'value' => (float)$dds_price // 산출된 오너클랜 공급가
                        ],
                        'productId' => '',
                        'quantity' => 9999,
                        'metadata' => []
                    ]
                ],
                'taxFree' => false,
                'adultOnly' => false,
                'openmarketSellable' => true,
                'returnable' => true,
                'shippingType' => 'inAdvance',
                'shippingFee' => 2800, // 기존 고정값
                'pricePolicy' => 'free',
                'returnCriteria' => 'vendor',
                'fixedPrice' => [
                    'currency' => 'KRW',
                    'value' => (float)$s_price // 소비자가
                ],
                'metadata' => [
                    'vcode' => (string)($goodsRecord->goods_code ?? ''), // 상품고유코드
                    'certificateInformation' => [],
                    'productNotificationInformation' => [
                        'code' => 35, // 기존 로직
                        'categorySpecific' => [
                            "상품 상세정보에 별도 표기",
                            "상품 상세정보에 별도 표기",
                            "상품 상세정보에 별도 표기",
                            "상품 상세정보에 별도 표기",
                            "상품 상세정보에 별도 표기",
                            "상품 상세정보에 별도 표기"
                        ],
                        'common' => [
                            "상품 상세정보에 별도 표기",
                            "상품 상세정보에 별도 표기",
                            "상품 상세정보에 별도 표기",
                            "상품 상세정보에 별도 표기"
                        ]
                    ],
                    'productnameDeli' => '-',
                    'returnAddressCode' => 0,
                    'returnShippingFee' => 2800
                ]
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->withoutVerifying()->post($this->apiUrl . '/graphql', [
                'operationName' => 'createItem',
                'query' => $createQuery,
                'variables' => $inputVariables
            ]);

            $result = $response->json();

            if (isset($result['errors'])) {
                return [
                    'success' => false,
                    'message' => $result['errors'][0]['message'] ?? 'GraphQL 오류'
                ];
            }

            if (isset($result['data']['createItem']['key'])) {
                return [
                    'success' => true,
                    'affiliate_goods_code' => $result['data']['createItem']['key'],
                    'message' => '전송 성공'
                ];
            }
            
            return [
                'success' => false,
                'message' => '응답 데이터 이상'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'API 통신 오류: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 주문 수집 API 연동 골격
     */
    public function fetchOrders($date = null)
    {
        return [
            'success' => true,
            'orders' => []
        ];
    }

    /**
     * 오너클랜 카테고리 목록 가져오기 (파일 캐싱)
     */
    public function fetchCategories($force = false)
    {
        $filePath = storage_path('app/affiliate/ownerclan_categories.json');

        if (!$force && file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $data = json_decode($content, true);
            if (is_array($data)) {
                return $data;
            }
        }

        $token = $this->authenticate();
        if (!$token) {
            return [];
        }

        $query = '
          query getCategories($after: String) {
            allCategories(first: 100, after: $after) {
              edges {
                node {
                  key
                  name
                  fullName
                }
              }
              pageInfo {
                hasNextPage
                endCursor
              }
            }
          }
        ';

        $categories = [];
        $hasNextPage = true;
        $endCursor = null;

        while ($hasNextPage) {
            $variables = [];
            if ($endCursor) {
                $variables['after'] = $endCursor;
            }

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ])->withoutVerifying()->post($this->apiUrl . '/graphql', [
                    'query' => $query,
                    'variables' => $variables
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    if (isset($result['data']['allCategories'])) {
                        $allCat = $result['data']['allCategories'];
                        foreach ($allCat['edges'] as $edge) {
                            $node = $edge['node'];
                            // fullName을 사용하되, 없으면 name 사용
                            $catName = !empty($node['fullName']) ? $node['fullName'] : $node['name'];
                            
                            $categories[] = [
                                'code' => (string)$node['key'],
                                'name' => $catName
                            ];
                        }
                        
                        $hasNextPage = $allCat['pageInfo']['hasNextPage'] ?? false;
                        $endCursor = $allCat['pageInfo']['endCursor'] ?? null;
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            } catch (\Exception $e) {
                \Log::error('Ownerclan fetchCategories Error: ' . $e->getMessage());
                break;
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
}
