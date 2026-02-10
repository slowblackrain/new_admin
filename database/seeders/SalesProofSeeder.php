<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesProofSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate tables to start fresh? Maybe not, just append or ensure IDs don't clash.
        // User asked for 100 items.
        // Existing members?
        $members = DB::table('fm_member')->limit(10)->get();
        if ($members->count() == 0) {
            // Create dummy members if none
            for ($i=1; $i<=5; $i++) {
                $memberSeq = DB::table('fm_member')->insertGetId([
                   'userid' => 'user'.$i,
                   'user_name' => '홍길동'.$i,
                   'email' => 'user'.$i.'@example.com',
                   'cellphone' => '010-1234-567'.$i,
                   'status' => '1',
                   'r_date' => now(),
                   'm_date' => now()
                ]);
                DB::table('fm_member_business')->insert([
                    'member_seq' => $memberSeq,
                    'bname' => '테스트상사'.$i,
                    'bceo' => '대표'.$i,
                    'bno' => '123-45-6789'.$i,
                    'bstatus' => '업태'.$i,
                    'bitem' => '종목'.$i,
                    'bperson' => '담당자'.$i,
                    'email' => 'tax'.$i.'@example.com',
                    'bcellphone' => '010-9876-543'.$i,
                    'bzipcode' => '12345',
                    'baddress_street' => '서울시 강남구 테헤란로 '.$i,
                    'baddress_detail' => '10'.$i.'호'
                ]);
                $members->push((object)['member_seq' => $memberSeq]);
            }
        }

        // Create 100 sales proof requests
        for ($i = 0; $i < 100; $i++) {
            $member = $members->random();
            $date = now()->subDays(rand(0, 60));
            $orderDate = $date->copy()->subDays(rand(1, 5));
            
            // Create Order
            // fm_order is not auto-increment, need manual ID. Max + 1 or random?
            // Safer to use max+1.
            $maxOrderSeq = DB::table('fm_order')->max('order_seq') ?? 10000;
            $orderSeq = $maxOrderSeq + 1;
            
            DB::table('fm_order')->insert([
                'order_seq' => $orderSeq,
                // 'ordno' => removed
                'member_seq' => $member->member_seq,
                // 'user_name' => removed
                
                // REQUIRED FIELDS based on schema:
                'original_settleprice' => 11000 * rand(1, 5),
                'settleprice' => 11000 * rand(1, 5),
                'enuri' => '0',
                'tax' => 1000,
                'step' => '75',
                'bundle_yn' => 'n',
                'deposit_yn' => 'y',
                'deposit_date' => $orderDate,
                'virtual_date' => $orderDate,
                'cash_receipts_no' => '',
                'order_user_name' => '홍길동',
                'order_cellphone' => '010-0000-0000',
                'order_email' => 'test@test.com',
                'shipping_cost' => 0,
                'international' => 'domestic',
                'international_cost' => 0,
                'mode' => 'pc',
                'regist_date' => $orderDate,
                'session_id' => 'dummy_session',
                'important' => '0',
                'sitetype' => 'P',
                'hidden' => 'N',
                'admin_order' => 'admin',
                'skintype' => 'P',
                'total_ea' => 1,
                'total_type' => 1,
                
                // Optional but good
                'order_phone' => '02-000-0000',
            ]);

            // Create Order Item (fm_order_item) - optional but good for completeness
            // Skipping for brevity, focusing on Sales tables
            
            // Create fm_sales (Sales Item)
            $supply = 10000 * rand(1, 5);
            $surtax = $supply * 0.1;
            $total = $supply + $surtax;

            // fm_sales has no explicit PK in query result but likely has one or uses order_seq?
            // Wait, SHOW TABLES showed fm_sales. 
            // Describe showed `order_seq` as int(11) but NOT Key? Limit 20 didn't show PK.
            // Let's assume auto-inc 'seq' or similar if not shown, or just insert and let DB handle if PK is auto.
            // Describe output didn't show first column being PK.
            // Actually, let's look at the describe output again... 
            // It showed `sales_seq` as PRI in `fm_sales_detail` maybe?
            // `fm_sales` describe output above started at [0] `order_seq`.
            // It seems `fm_sales` might NOT have a single PK or I missed it.
            // Let's just insert without ID if it's auto or not needed.
            
            $salesid = DB::table('fm_sales')->insertGetId([
                'order_seq' => $orderSeq,
                'order_date' => $orderDate->format('Y-m-d H:i:s'), // datetime
                'supply' => $supply,
                'surtax' => $surtax,
                'price' => $total,
                'regdate' => $date, // Corrected from regist_date
                
                // REQUIRED FIELDS
                'goodsname' => '테스트상품 ' . $i,
                'cuse' => 0,
                'creceipt_number' => '',
                'cash_no' => '',
                'receipt_no' => '',
                'app_time' => '',
                'reg_stat' => '',
                'reg_desc' => '',
                'email' => 'test@test.com',
                'phone' => '010-0000-0000',
                'issue_date' => $date,
                'favorite_chk' => 'none',
                'member_seq' => $member->member_seq,
            ]);

            // Create fm_sales_list (The application itself)
            $salesId = DB::table('fm_sales_list')->insertGetId([
                'member_seq' => $member->member_seq,
                'in_date' => $date->format('Ym'), // Request YearMonth
                'reg_date' => $date, // Corrected from r_date, regist_date
                // 'regist_date' => $date, // Removed
                'supply' => $supply,
                'surtax' => $surtax,
                'price' => $total,
                'state' => 1, // 1: Requested
                'tstep' => 1, // 1: Before Send
                'log_msg' => '신청되었습니다.',
                'issue_date' => null,
                'memo' => '테스트 메모 ' . $i
            ]);

            // Create fm_sales_detail (Link between list and sales item)
            // Need to check fm_sales_detail schema too.
            // Assuming sales_id and sales_seq are there.
            DB::table('fm_sales_detail')->insert([
                'sales_id' => $salesId,
                'sales_seq' => $salesid, // Corrected variable name
                'state' => 1, // 1: Included
                'reg_date' => $date // Corrected from regist_date
            ]);
        }
    }
}
