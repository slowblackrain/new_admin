<?php

namespace Tests\Feature\Front;

use Tests\TestCase;
use App\Models\Cart;
use App\Models\CartOption;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CartIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Session::start();
    }

    /** @test */
    public function test_cart_index_view_receives_base_shipping_cost()
    {
        $response = $this->get(route('cart.index'));
        $response->assertStatus(200);
        $response->assertViewHas('baseShipping');
        $this->assertEquals(config('shop.shipping.base_cost', 2500), $response->viewData('baseShipping'));
    }

    /** @test */
    public function test_guest_cart_items_are_merged_to_member_cart_on_login()
    {
        $sessionId = Session::getId();
        
        // 1. Insert dummy goods manually
        $goodsSeq = DB::table('fm_goods')->insertGetId([
            'goods_name' => '테스트 병합 상품 1',
            'goods_scode' => 'TEST_SH_01',
            'tax' => 'tax',
            'shipping_policy' => 'shop'
        ]);

        $goodsOption = DB::table('fm_goods_option')->insertGetId([
            'goods_seq' => $goodsSeq,
            'option1' => '빨강',
            'option_title' => '색상'
        ]);

        $goodsOptionRow = DB::table('fm_goods_option')->where('option_seq', $goodsOption)->first();

        // 2. Add item to guest cart
        $guestCart = Cart::create([
            'goods_seq' => $goodsSeq,
            'member_seq' => 0,
            'session_id' => $sessionId,
            'distribution' => 'cart',
            'regist_date' => now(),
            'update_date' => now()
        ]);

        CartOption::create([
            'cart_seq' => $guestCart->cart_seq,
            'ea' => 2,
            'option1' => $goodsOptionRow->option1,
            'title1' => $goodsOptionRow->option_title
        ]);

        // 3. Insert dummy member manually
        $memberSeq = DB::table('fm_member')->insertGetId([
            'userid' => 'test_merge_user_123',
            'password' => md5('password123'),
            'user_name' => '테스터1',
            'email' => 'test1@example.com',
            'regist_date' => now(),
            'status' => 'ok'
        ]);

        // Verify pre-merge status
        $this->assertEquals(1, Cart::where('session_id', $sessionId)->count());
        $this->assertEquals(0, Cart::where('member_seq', $memberSeq)->count());

        // 4. Trigger merge
        Cart::mergeForMember($memberSeq, $sessionId);

        // Verify post-merge status: Guest cart is migrated to member
        $this->assertEquals(0, Cart::where('session_id', $sessionId)->count());
        $memberCart = Cart::where('member_seq', $memberSeq)->first();
        $this->assertNotNull($memberCart);
        $this->assertEquals($goodsSeq, $memberCart->goods_seq);
        $this->assertEquals(2, $memberCart->options->first()->ea);
    }

    /** @test */
    public function test_guest_cart_merges_quantities_when_same_item_exists_in_member_cart()
    {
        $sessionId = Session::getId();
        
        // 1. Insert dummy goods manually
        $goodsSeq = DB::table('fm_goods')->insertGetId([
            'goods_name' => '테스트 병합 상품 2',
            'goods_scode' => 'TEST_SH_02',
            'tax' => 'tax',
            'shipping_policy' => 'shop'
        ]);

        $goodsOption = DB::table('fm_goods_option')->insertGetId([
            'goods_seq' => $goodsSeq,
            'option1' => '파랑',
            'option_title' => '색상'
        ]);

        $goodsOptionRow = DB::table('fm_goods_option')->where('option_seq', $goodsOption)->first();

        // 2. Insert dummy member manually
        $memberSeq = DB::table('fm_member')->insertGetId([
            'userid' => 'test_merge_user_456',
            'password' => md5('password123'),
            'user_name' => '테스터2',
            'email' => 'test2@example.com',
            'regist_date' => now(),
            'status' => 'ok'
        ]);

        // 3. Add item to member's cart
        $memberCart = Cart::create([
            'goods_seq' => $goodsSeq,
            'member_seq' => $memberSeq,
            'session_id' => '',
            'distribution' => 'cart',
            'regist_date' => now(),
            'update_date' => now()
        ]);

        CartOption::create([
            'cart_seq' => $memberCart->cart_seq,
            'ea' => 3,
            'option1' => $goodsOptionRow->option1,
            'title1' => $goodsOptionRow->option_title
        ]);

        // 4. Add same item to guest cart (session)
        $guestCart = Cart::create([
            'goods_seq' => $goodsSeq,
            'member_seq' => 0,
            'session_id' => $sessionId,
            'distribution' => 'cart',
            'regist_date' => now(),
            'update_date' => now()
        ]);

        CartOption::create([
            'cart_seq' => $guestCart->cart_seq,
            'ea' => 4,
            'option1' => $goodsOptionRow->option1,
            'title1' => $goodsOptionRow->option_title
        ]);

        // 5. Trigger merge
        Cart::mergeForMember($memberSeq, $sessionId);

        // Verify: Guest cart is deleted, member cart quantity is now 7 (3 + 4)
        $this->assertEquals(0, Cart::where('session_id', $sessionId)->count());
        $finalMemberCart = Cart::where('member_seq', $memberSeq)->first();
        $this->assertNotNull($finalMemberCart);
        $this->assertEquals(7, $finalMemberCart->options->first()->ea);
    }
}
