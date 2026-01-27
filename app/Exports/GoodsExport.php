<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class GoodsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function query()
    {
        // Reuse Filter Logic from GoodsController
        // Since this is a temporary export class, we duplicate logic strictly for export scope.
        // Or inject a Repository. For MVP, we inline the query build.
        
        $params = $this->params;
        $keyword = $params['keyword'] ?? null;

        $query = DB::table('fm_goods as g')
            ->select(
                'g.goods_seq', 
                'g.goods_code',
                'g.goods_name',
                
                // Optimized Subqueries to avoid Cartesian Product
                DB::raw('(SELECT consumer_price FROM fm_goods_option WHERE goods_seq = g.goods_seq AND default_option = "y" LIMIT 1) as consumer_price'),
                DB::raw('(SELECT price FROM fm_goods_option WHERE goods_seq = g.goods_seq AND default_option = "y" LIMIT 1) as sale_price'),
                DB::raw('(SELECT provider_price FROM fm_goods_option WHERE goods_seq = g.goods_seq AND default_option = "y" LIMIT 1) as provider_price'),
                DB::raw('(SELECT SUM(stock) FROM fm_goods_supply WHERE goods_seq = g.goods_seq) as stock'),
                
                // Category Subquery
                DB::raw('(SELECT c.title FROM fm_category_link cl JOIN fm_category c ON cl.category_code = c.category_code WHERE cl.goods_seq = g.goods_seq AND cl.link = 1 LIMIT 1) as category_title'),

                'g.goods_status',
                'g.goods_view',
                'g.tax',
                'g.runout_policy',
                'g.shipping_policy',
                'g.keyword',
                'g.goods_scode'
            )
            ->orderBy('g.goods_seq', 'desc');
    
        // ... filters ...
        return $query;
    }

    public function headings(): array
    {
        return [
            'SEQ',
            '상품코드',
            '상품명',
            '소비자가',
            '판매가',
            '공급가',
            '재고',
            '카테고리',
            '상태',
            '노출',
            '과세여부',
            '품절정책',
            '배송비정책',
            '키워드',
            '자체코드'
        ];
    }

    public function map($row): array
    {
        return [
            $row->goods_seq,
            $row->goods_code,
            $row->goods_name,
            $row->consumer_price,
            $row->sale_price,
            $row->provider_price,
            $row->stock,
            $row->category_title,
            $this->mapStatus($row->goods_status),
            $row->goods_view,
            $row->tax,
            $row->runout_policy,
            $row->shipping_policy,
            $row->keyword,
            $row->goods_scode
        ];
    }

    protected function mapStatus($status)
    {
        $map = [
            'normal' => '정상',
            'runout' => '품절',
            'stop' => '판매중지',
            'unsold' => '판매종료',
            'purchasing' => '사입중'
        ];
        return $map[$status] ?? $status;
    }
}
