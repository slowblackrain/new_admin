<?php

namespace App\Services\Search;

use App\Contracts\SearchIntentionInterface;

class MockSearchIntentionService implements SearchIntentionInterface
{
    public function analyze(string $query): array
    {
        $result = [
            'keywords' => [],
            'filters' => [],
            'sort' => null
        ];

        // --- Mock Logic for Demonstration ---

        // 1. Price Range Pattern: "1만원대" -> 10000 ~ 19999
        if (preg_match('/(\d+)만원대/', $query, $matches)) {
            $base = (int)$matches[1] * 10000;
            $result['filters']['price_min'] = $base;
            $result['filters']['price_max'] = $base + 9999;
            $query = str_replace($matches[0], '', $query); // Remove from query
        }

        // 2. Max Price Pattern: "5000원 이하", "3만원 이하"
        if (preg_match('/(\d+)원\s*이하/', $query, $matches)) {
            $result['filters']['price_max'] = (int)$matches[1];
            $query = str_replace($matches[0], '', $query);
        }
        if (preg_match('/(\d+)만원\s*이하/', $query, $matches)) {
            $result['filters']['price_max'] = (int)$matches[1] * 10000;
            $query = str_replace($matches[0], '', $query);
        }

        // 3. Sort Patterns
        if (strpos($query, '저렴한') !== false || strpos($query, '싼') !== false) {
            $result['sort'] = 'price_asc';
            $query = str_replace(['저렴한', '싼'], '', $query);
        }
        elseif (strpos($query, '비싼') !== false || strpos($query, '고급') !== false) {
            $result['sort'] = 'price_desc';
            $query = str_replace(['비싼', '고급'], '', $query);
        }
        elseif (strpos($query, '인기') !== false) {
            $result['sort'] = 'popular';
            $query = str_replace(['인기'], '', $query);
        }

        // 4. Color/Pattern Expansion (Mock)
        $colors = [
            '빨간' => ['red'], '파란' => ['blue'], '검정' => ['black'], '흰' => ['white']
        ];
        foreach ($colors as $k => $vals) {
            if (strpos($query, $k) !== false) {
                // Add synonyms
                foreach ($vals as $v) $result['keywords'][] = $v;
            }
        }

        // 5. Remaining tokens are Keywords
        $tokens = explode(' ', trim($query));
        foreach ($tokens as $token) {
            if (empty($token)) continue;
            $result['keywords'][] = $token;
            
            // Mock Synonym Injection
            if ($token == '볼펜') {
                $result['keywords'][] = '펜';
                $result['keywords'][] = 'pen';
            }
            if ($token == '텀블러') {
                $result['keywords'][] = '물병';
                $result['keywords'][] = '보틀';
            }
            if ($token == '삼성') {
                 $result['keywords'][] = 'SAMSUNG';
            }
        }

        return $result;
    }
}
