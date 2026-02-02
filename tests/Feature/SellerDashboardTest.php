<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Seller;

class SellerDashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_seller_dashboard_load_verified()
    {
        // 1. Create Seller & Member Link
        $memberId = 'test_seller_mem';
        $providerId = 'test_seller_prov';

        $memberSeq = DB::table('fm_member')->insertGetId([
            'userid' => $memberId,
            'emoney' => 50000,
            'cash' => 10000,
            'user_name' => 'Test Seller Member',
            'email' => 'test@seller.com',
            'status' => 'active',
            'regist_date' => now(),
            'update_date' => now(),
        ]);

        $providerSeq = DB::table('fm_provider')->insertGetId([
            'provider_id' => $providerId,
            'userid' => $memberId, // Link to member
            'provider_name' => 'Test Provider',
            'provider_status' => 'Y',
            'regdate' => now(),
        ]);
        
        // 2. Seed ATS Products (1 Normal, 1 Runout)
        DB::table('fm_goods')->insert([
            'provider_seq' => $providerSeq,
            'goods_name' => 'Normal Product',
            'goods_status' => 'normal',
            'regist_date' => now(),
        ]);

        DB::table('fm_goods')->insert([
            'provider_seq' => $providerSeq,
            'goods_name' => 'Runout Product',
            'goods_status' => 'runout',
            'regist_date' => now(),
        ]);

        // 3. Seed Purchase Orders (Fulfillment)
        // Order 1: Deposit Pending (Step 15)
        DB::table('fm_order')->insert([
            'order_seq' => 101,
            'member_seq' => $memberSeq, // IMPORTANT: Linking order to Seller's Member ID
            'step' => '15',
            'deposit_yn' => 'n',
            'settleprice' => 15000,
            'regist_date' => now(),
        ]);

        // Order 2: Shipping (Step 55) - Completed Purchase
        DB::table('fm_order')->insert([
            'order_seq' => 102,
            'member_seq' => $memberSeq,
            'step' => '55',
            'deposit_yn' => 'y',
            'deposit_date' => now(), // Count as purchase today
            'settleprice' => 30000,
            'regist_date' => now(),
        ]);

        // 4. Authenticate as Seller
        $seller = Seller::where('provider_seq', $providerSeq)->first();
        if (!$seller) {
            // Manually make model instance if not found via eloquent due to timestamps? 
            // Should be fine as we inserted to DB.
             // Provider model doesn't exist? Ah, 'App\Models\Seller' uses 'fm_provider'.
        }
        
        $response = $this->actingAs($seller, 'seller')->get(route('seller.dashboard'));

        // 5. Assertions
        $response->assertStatus(200);
        $response->assertSee('Test Provider');
        
        // Asset Assertions (View Data)
        $response->assertViewHas('assetSummary', function ($data) {
            return $data['emoney'] == 50000 && $data['cash'] == 10000;
        });

        // Fulfillment Assertions
        $response->assertViewHas('fulfillmentSummary', function ($data) {
            return $data['deposit_pending'] == 1 
                && $data['shipping'] == 1 
                && $data['preparing'] == 0;
        });

        // Product Assertions
        $response->assertViewHas('productSummary', function ($data) {
            return $data['normal'] == 1 && $data['runout'] == 1;
        });

        // Purchase Stats Assertions
        $response->assertViewHas('purchaseStats', function ($data) {
            // Should have 1 entry for today with 30000 amount
            // Since we fill 7 days, today is the last index
            $lastIndex = count($data['amounts']) - 1;
            return !empty($data['dates']) && $data['amounts'][$lastIndex] == 30000;
        });
    }
}
