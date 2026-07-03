<?php

namespace App\Services\Affiliate;

use Illuminate\Support\Facades\Http;

class DomemeService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct($apiKey = null)
    {
        $this->apiUrl = config('services.domeme.url', 'https://api.domeme.com/v1');
        $this->apiKey = $apiKey;
    }

    /**
     * 상품 등록 API 연동 골격
     */
    public function registerProduct($goodsData)
    {
        // TODO: Implement actual API mapping
        return [
            'success' => true,
            'affiliate_goods_code' => 'DM_' . uniqid()
        ];
    }

    /**
     * 카테고리 목록 조회
     */
    public function fetchCategories()
    {
        // 도매매 카테고리 API는 도매꾹 API(ver=1.0)를 통해 getCategoryList 모드로 호출합니다.
        $url = 'https://domeggook.com/ssl/api/?ver=1.0&mode=getCategoryList&aid=' . $this->apiKey . '&om=json';
        
        $response = Http::withoutVerifying()->get($url);
        
        if (!$response->successful()) {
            return [];
        }
        
        $data = $response->json();
        
        if (!isset($data['domeggook']['items'])) {
            return [];
        }

        $items = $data['domeggook']['items'];
        $leafCategories = [];

        // 재귀함수를 통해 트리를 순회하며 리프 노드를 추출
        $traverse = function($nodes, $parentName = '') use (&$traverse, &$leafCategories) {
            foreach ($nodes as $node) {
                $currentName = $parentName === '' ? $node['name'] : $parentName . ' > ' . $node['name'];
                
                if (isset($node['child']) && is_array($node['child']) && count($node['child']) > 0) {
                    $traverse($node['child'], $currentName);
                } else {
                    // 자식이 없으면 리프 노드
                    if (isset($node['locked']) && $node['locked'] === 'FALSE') {
                        $leafCategories[] = [
                            'code' => $node['code'],
                            'name' => $currentName
                        ];
                    }
                }
            }
        };

        $traverse($items);

        // 카테고리 이름순으로 정렬
        usort($leafCategories, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $leafCategories;
    }
}
