<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Member;
use App\Models\Goods;
use App\Models\Provider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class SearchParityTest extends TestCase
{
    use DatabaseTransactions;

    protected $providerSeq;
    protected $memberSeq;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Active Provider
        $this->providerSeq = DB::table('fm_provider')->insertGetId([
            'provider_id' => 'test_prov_' . uniqid(),
            'provider_name' => 'Test Provider',
            'provider_status' => 'Y', // Active
            'regdate' => now(),
        ]);

        // 2. Create Member for ATS testing
        $this->memberSeq = DB::table('fm_member')->insertGetId([
            'userid' => 'test_mem_' . uniqid(),
            'password' => 'password',
            'user_name' => 'Test Member',
            'email' => 'test@example.com',
            'status' => 'active',
            'regist_date' => now(),
            'update_date' => now(),
        ]);
    }

    public function test_guest_sees_only_public_goods()
    {
        // Public Good
        $publicSeq = $this->createGoods([
            'goods_name' => 'Public Product',
            'ATS_member_seq' => 0,
        ]);

        // Private Good
        $privateSeq = $this->createGoods([
            'goods_name' => 'Private Product',
            'ATS_member_seq' => 99999,
        ]);

        $response = $this->get(route('goods.search', ['search_text' => 'Product']));

        $response->assertStatus(200);
        $response->assertSee('Public Product');
        $response->assertDontSee('Private Product');
    }

    public function test_member_sees_own_private_goods()
    {
        // Public Good
        $publicSeq = $this->createGoods([
            'goods_name' => 'Public Product',
            'ATS_member_seq' => 0,
        ]);

        // Own Private Good
        $ownPrivateSeq = $this->createGoods([
            'goods_name' => 'My Private Product',
            'ATS_member_seq' => $this->memberSeq,
        ]);

        // Other's Private Good
        $otherPrivateSeq = $this->createGoods([
            'goods_name' => 'Other Private Product',
            'ATS_member_seq' => $this->memberSeq + 1,
        ]);

        // Login as Member
        $member = Member::find($this->memberSeq);
        
        $response = $this->actingAs($member)->get(route('goods.search', ['search_text' => 'Product']));

        $response->assertStatus(200);
        $response->assertSee('Public Product');
        $response->assertSee('My Private Product');
        $response->assertDontSee('Other Private Product');
    }

    public function test_active_scope_excludes_hidden_or_stopped_goods()
    {
        // Normal Good
        $normalSeq = $this->createGoods(['goods_name' => 'Normal Good', 'goods_view' => 'look', 'goods_status' => 'normal']);
        
        // Hidden Good (view=no)
        $hiddenSeq = $this->createGoods(['goods_name' => 'Hidden Good', 'goods_view' => 'no', 'goods_status' => 'normal']);

        // Stopped Good (status=stop)
        $stoppedSeq = $this->createGoods(['goods_name' => 'Stopped Good', 'goods_view' => 'look', 'goods_status' => 'stop']);

        // Runout Good (status=runout) - Should be excluded by strict 'normal' check in active() scope
        $runoutSeq = $this->createGoods(['goods_name' => 'Runout Good', 'goods_view' => 'look', 'goods_status' => 'runout']);

        // Inactive Provider Good
        $inactiveProvSeq = DB::table('fm_provider')->insertGetId([
            'provider_id' => 'inactive_' . uniqid(),
            'provider_name' => 'Inactive Provider',
            'provider_status' => 'N',
            'regdate' => now(),
        ]);
        $inactiveGoodsSeq = $this->createGoods([
            'goods_name' => 'Inactive Provider Good',
            'provider_seq' => $inactiveProvSeq
        ]);


        $response = $this->get(route('goods.search', ['search_text' => 'Good']));

        $response->assertStatus(200);
        $response->assertSee('Normal Good');
        $response->assertDontSee('Hidden Good');
        $response->assertDontSee('Stopped Good');
        $response->assertDontSee('Runout Good'); // active() requires 'normal'
        $response->assertDontSee('Inactive Provider Good');
    }

    public function test_keyword_search_functionality()
    {
        $seq1 = $this->createGoods(['goods_name' => 'Apple Phone', 'goods_code' => 111111]);
        $seq2 = $this->createGoods(['goods_name' => 'Samsung Phone', 'goods_code' => 222222]);
        
        // Search by Name
        $response = $this->get(route('goods.search', ['search_text' => 'Apple']));
        $response->assertSee('Apple Phone');
        $response->assertDontSee('Samsung Phone');

        // Search by Code
        $response = $this->get(route('goods.search', ['search_text' => '222222']));
        $response->assertSee('Samsung Phone');
        $response->assertDontSee('Apple Phone');
    }

    public function test_sub_text_exclude_functionality()
    {
        $seq1 = $this->createGoods(['goods_name' => 'Red Apple']);
        $seq2 = $this->createGoods(['goods_name' => 'Green Apple']);
        $seq3 = $this->createGoods(['goods_name' => 'Red Banana']);

        // Search "Apple" but exclude "Green"
        $response = $this->get(route('goods.search', [
            'search_text' => 'Apple',
            'sub_text' => 'Green',
            'sub_search' => 'E' // Exclude
        ]));

        $response->assertSee('Red Apple');
        $response->assertDontSee('Green Apple');
        $response->assertDontSee('Red Banana'); // Should likely not be there anyway due to main keyword
    }

    private function createGoods($attributes = [])
    {
        $default = [
            'provider_seq' => $this->providerSeq,
            // 'category_code' => '0001', // Removed: Not in fm_goods
            'goods_name' => 'Test Goods',
            'goods_status' => 'normal',
            'goods_view' => 'look',
            // 'goods_price' => 10000, // Removed: Not in fm_goods
            'regist_date' => now(),
            'update_date' => now(),
            'ATS_member_seq' => 0,
            'goods_code' => rand(10000000, 99999999), 
        ];

        $goodsSeq = DB::table('fm_goods')->insertGetId(array_merge($default, $attributes));

        // Link to a default category
        DB::table('fm_category_link')->insert([
            'goods_seq' => $goodsSeq,
            'category_code' => '0001',
            'regist_date' => now(),
        ]);

        // Insert Default Option (Price)
        DB::table('fm_goods_option')->insert([
            'goods_seq' => $goodsSeq,
            'default_option' => 'y',
            'option_type' => 'S', // Simple?
            'price' => 10000,
            'consumer_price' => 12000,
            // 'regist_date' => now(), // Removed: Not in fm_goods_option
        ]);

        return $goodsSeq;
    }
}
