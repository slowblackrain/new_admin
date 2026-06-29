<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Affiliate\DaehanScraperService;

class TestDaehanProductSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'affiliate:test-daehan-product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '대한판촉 스크래핑 상품 등록 테스트';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('대한판촉 스크래핑 상품 등록 테스트를 시작합니다...');

        $scraper = new DaehanScraperService();
        
        $goods = \App\Models\Goods::with(['images', 'defaultOption.supply'])->first();
        if (!$goods) {
            $this->error('상품을 찾을 수 없습니다.');
            return;
        }
        
        $this->info('테스트 상품 데이터를 전송합니다... (Dry-Run)');
        $this->line('상품명: ' . $goods->goods_name);
        $this->line('공급가: ' . number_format($goods->supply_price) . '원');
        $this->line('이미지: ' . ($goods->images->first()->image ?? '없음'));
        
        $result = $scraper->registerProduct($goods);

        if ($result['success']) {
            $this->info('상품 등록 스크래핑 폼 전송 성공!');
            $this->line('등록 요청된 상품코드: ' . $result['affiliate_goods_code']);
            $this->line('계산된 판매가(마진 적용): ' . number_format($result['selling_price']) . '원');
        } else {
            $this->error('상품 등록 스크래핑 전송 실패 (혹은 폼 구조 변경)');
            if (isset($result['message'])) {
                $this->error('사유: ' . $result['message']);
            }
        }
    }
}
