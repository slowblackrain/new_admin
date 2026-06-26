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

        $scraper = new DaehanScraperService('dotob2b', '0000');
        
        // Mock Dometopia Goods Object
        $mockGoods = new \stdClass();
        $mockGoods->goods_seq = 'TEST_' . date('His');
        $mockGoods->goods_name = '[테스트] 도매토피아 자동 연동 상품 ' . date('His');
        $mockGoods->goods_explan = '<p>자동 연동 테스트 상세설명입니다.</p>';
        $mockGoods->p_spl1 = 5000; // Supply Price
        
        $this->info('테스트 상품 데이터를 전송합니다...');
        $this->line('상품명: ' . $mockGoods->goods_name);
        $this->line('공급가: ' . number_format($mockGoods->p_spl1) . '원');
        
        $result = $scraper->registerProduct($mockGoods);

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
