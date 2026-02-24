<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingExtraCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('shipping_extra_costs')->truncate();

        $extraCosts = [
            // 제주특별자치도 (전역 63000 ~ 63621) - 보통 3,000원 추가 (기본 배송비 3000 + 추가 3000 = 6000원)
            [
                'zipcode_start' => '63000',
                'zipcode_end' => '63621',
                'extra_cost' => 3000,
                'area_name' => '제주특별자치도 전역',
            ],
            // 경상북도 울릉군 (79900 ~ 79999) - 특수 산간벽지 보통 5,000원~8,000원 추가
            [
                'zipcode_start' => '40200', // 새 우편번호 체계 울릉군: 40200 ~ 40240
                'zipcode_end' => '40240',
                'extra_cost' => 5000,
                'area_name' => '경상북도 울릉군',
            ],
            // 인천광역시 옹진군, 백령도 등 특수 도서 (다양한 대역이 있으나, 예시로 추가)
            [
                'zipcode_start' => '23100', // 23100 ~ 23136 (강화군 서도/교동 등 일부), 옹진군은 23100~
                'zipcode_end' => '23136',
                'extra_cost' => 5000,
                'area_name' => '인천광역시 옹진군/강화 도서',
            ],
            // 전라남도 신안군 (58800 ~ 58866)
            [
                'zipcode_start' => '58800',
                'zipcode_end' => '58866',
                'extra_cost' => 4000,
                'area_name' => '전라남도 신안군 도서지역',
            ]
        ];

        DB::table('shipping_extra_costs')->insert($extraCosts);
    }
}
