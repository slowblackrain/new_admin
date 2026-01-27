<?php

namespace Tests\Feature\Front;

use App\Models\Member;
use App\Models\Coupon;
use App\Models\CouponDownload;
use App\Models\Emoney;
use App\Models\Point;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MypageBenefitsTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test member manually as MemberFactory might not exist or be configured
        // Ensure member_seq is set if not auto-increment (usually legacy has AI, but let's see)
        // If fm_member has AI, we don't need to specify member_seq.
        // Let's try creating without member_seq first, or with a random one if AI fails.
        // Typically legacy fm_member has AI.
        
        $this->user = Member::create([
            'userid' => 'testuser_' . uniqid(),
            'password' => bcrypt('password'),
            'user_name' => 'Test User',
            'email' => 'test@example.com',
            'cellphone' => '010-1234-5678',
            'status' => 'active',
            'regist_date' => now(),
            // add other required fields if any (defaults usually handle checking)
        ]);
    }

    public function test_can_view_coupon_list()
    {
        // 1. Setup Data: Create a coupon and download it
        $coupon = Coupon::create([
            'coupon_name' => 'Test Coupon',
            'sale_type' => 'won',
            'won_goods_sale' => 1000,
            'issue_type' => 'manual',
            'issue_startdate' => now()->format('Y-m-d'),
            'issue_enddate' => now()->addDays(30)->format('Y-m-d'),
        ]);

        CouponDownload::create([
            'member_seq' => $this->user->member_seq,
            'coupon_seq' => $coupon->coupon_seq,
            'regist_date' => now(),
            'issue_startdate' => now()->format('Y-m-d'),
            'issue_enddate' => now()->addDays(30)->format('Y-m-d'),
            'use_status' => 'unused',
            'coupon_name' => 'Test Coupon',
        ]);

        // 2. Action
        $response = $this->actingAs($this->user)
            ->get(route('mypage.coupon'));

        // 3. Assertion
        $response->assertStatus(200);
        $response->assertViewIs('front.mypage.coupon');
        $response->assertSee('Test Coupon'); // Coupon name
        $response->assertSee('1,000원 할인'); // Benefit text logic in view
        $response->assertSee('사용가능');
    }

    public function test_can_view_emoney_list()
    {
        // 1. Setup Data
        Emoney::create([
            'member_seq' => $this->user->member_seq,
            'emoney' => 500,
            'remain' => 500, // Correct column name from schema
            'memo' => 'Test Emoney Reward',
            'regist_date' => now(),
        ]);

        // 2. Action
        $response = $this->actingAs($this->user)
            ->get(route('mypage.emoney'));

        // 3. Assertion
        $response->assertStatus(200);
        $response->assertViewIs('front.mypage.emoney');
        $response->assertSee('Test Emoney Reward');
        $response->assertSee('+500');
    }

    public function test_can_view_point_list()
    {
        // 1. Setup Data
        Point::create([
            'member_seq' => $this->user->member_seq,
            'point' => 100,
            'remain' => 100, // Correct column name
            'memo' => 'Test Point Reward',
            'regist_date' => now(),
        ]);

        // 2. Action
        $response = $this->actingAs($this->user)
            ->get(route('mypage.point'));

        // 3. Assertion
        $response->assertStatus(200);
        $response->assertViewIs('front.mypage.point');
        $response->assertSee('Test Point Reward');
        $response->assertSee('+100');
    }
}
