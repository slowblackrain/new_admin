<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Member;
use App\Models\Goods;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderStockTest extends TestCase
{
    protected $goodsSeq = 999999;
    protected $optionSeq = 999999;
    protected $supplySeq = 999999;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup Test Data manually to ensure it exists in the dev DB
        $this->setupTestData();
    }

    protected function setupTestData()
    {
        // 1. Create or Update Goods
        DB::table('fm_goods')->updateOrInsert(
            ['goods_seq' => $this->goodsSeq],
            [
                'goods_name' => 'Stock Test Product',
                'goods_code' => '999999',
                'goods_view' => 'look',
                'tot_stock' => 100, // Reset to 100
                'regist_date' => now(),
                'provider_seq' => 1,
            ]
        );

        // 2. Create Option
        DB::table('fm_goods_option')->updateOrInsert(
            ['option_seq' => $this->optionSeq],
            [
                'goods_seq' => $this->goodsSeq,
                'price' => 1000,
                'option1' => 'Default',
            ]
        );

        // 3. Create Supply
        DB::table('fm_goods_supply')->updateOrInsert(
            ['supply_seq' => $this->supplySeq],
            [
                'goods_seq' => $this->goodsSeq,
                'option_seq' => $this->optionSeq,
                'stock' => 100, // Reset to 100
            ]
        );
        
        // 4. Create SCM Link
        DB::table('fm_scm_location_link')->updateOrInsert(
            ['option_seq' => $this->optionSeq, 'wh_seq' => 1],
            [
                'goods_seq' => $this->goodsSeq,
                'ea' => 100, // Reset to 100
            ]
        );
    }

    public function test_stock_deduction_on_order()
    {
        // 1. Create Member
        // Use insertGetId or create if model works. Model works.
        $member = Member::create([
             'userid' => 'testuser' . rand(1000,9999), 
             'password' => 'password',
             'user_name' => 'Test User',
             'regist_date' => now(), 
        ]);
        
        // 2. Create Cart Item
        $cartSeq = DB::table('fm_cart')->insertGetId([
            'member_seq' => $member->member_seq,
            'goods_seq' => $this->goodsSeq,
            'regist_date' => now(),
            'update_date' => now(),
            // item_seq removed
            'session_id' => 'test_session', 
            'distribution' => 'web',
            'fblike' => 'n',
            'provider' => 0,
            'agent' => 'web'
        ]);

        DB::table('fm_cart_option')->insert([
            'cart_seq' => $cartSeq,
            'ea' => 1,
            'option1' => 'Default',
            'title1' => 'Option',
        ]);

        // 3. Prepare Request Data
        $data = [
            'cart_seq' => [$cartSeq],
            'order_user_name' => 'Test User',
            'order_cellphone' => '010-1234-5678',
            'order_email' => 'test@example.com',
            'recipient_user_name' => 'Test Recipient',
            'recipient_cellphone' => '010-9876-5432',
            'recipient_zipcode' => '12345',
            'recipient_address' => 'Test Address',
            'recipient_address_street' => 'Test Street',
            'recipient_address_detail' => '101',
            'payment' => 'bank',
            'bank_account' => 'Test Bank 123',
            'depositor' => 'Test User',
        ];

        // 4. Act
        $response = $this->actingAs($member)
                         ->post(route('order.store'), $data);

        // 5. Assert Redirect (Success)
        if ($response->status() !== 302) {
             dump($response->getContent());
        }
        $response->assertStatus(302);
        
        // 6. Verify Stock Deduction in Database
        // Total Stock
        $this->assertDatabaseHas('fm_goods', [
            'goods_seq' => $this->goodsSeq,
            'tot_stock' => 99, // 100 - 1
        ]);

        // Supply Stock
        $this->assertDatabaseHas('fm_goods_supply', [
            'supply_seq' => $this->supplySeq,
            'stock' => 99, // 100 - 1
        ]);

        // SCM Stock
        $this->assertDatabaseHas('fm_scm_location_link', [
            'option_seq' => $this->optionSeq,
            'wh_seq' => 1,
            'ea' => 99, // 100 - 1
        ]);
        
        // Clean up member
        $member->delete();
    }
}
