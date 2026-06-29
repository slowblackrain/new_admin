<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Affiliate\DaehanScraperService;

class TestDaehanScraping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'affiliate:test-daehan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '대한판촉 스크래핑 로그인 및 주문 수집 테스트';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('대한판촉 스크래핑 테스트를 시작합니다...');

        $scraper = new DaehanScraperService();
        
        $this->info('주문 목록 조회를 시도합니다...');
        $result = $scraper->fetchOrders();

        if ($result['success']) {
            $this->info('성공적으로 ' . count($result['orders']) . '건의 주문을 파싱했습니다.');
            foreach ($result['orders'] as $order) {
                $this->line('주문번호: ' . $order['affiliate_order_id'] . ' / 일자: ' . $order['date']);
                $this->line('텍스트: ' . $order['raw_text']);
                $this->line('---');
            }
        } else {
            $this->error('스크래핑 실패');
        }
    }
}
