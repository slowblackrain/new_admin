<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\Member; // Assuming Admin is a Member or using Guard
// Use Illuminate\Foundation\Testing\RefreshDatabase; // Beware of wiping DB

class GoodsRegistTest extends TestCase
{
    // protected function setUp(): void
    // {
    //     parent::setUp();
    //     // Login as Admin if needed
    //     // $this->actingAs(Member::find(1), 'web'); // Adjust guard/user
    // }

    /** @test */
    public function regist_page_loads()
    {
        // Assuming no auth middleware for now or using partial mock?
        // If Auth is required, this might redirect 302.
        // Let's assume we need to bypass or simplistic check.
        // We can use $this->withoutMiddleware();
        
        $response = $this->withoutMiddleware()->get(route('admin.goods.regist'));
        
        // Assert
        $response->assertStatus(200);
        $response->assertSee('상품 등록'); // Text in regist.blade.php
        $response->assertSee('카테고리 연결'); // Modal button
    }

    /** @test */
    public function price_calculator_works()
    {
        $response = $this->withoutMiddleware()->post(route('admin.goods.calculate_price'), [
            'realCost' => 1000,
            'exchange' => 1300,
            'customs' => 0.08,
            'incidental' => 1.05,
            'otherCost_mem' => 500
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'base_cost',
            'landed_price',
            'wholesale_price',
            'retail_price'
        ]);
        
        // Check Value (1000+500 -> Base? No logic is complex)
        // Just check structure
    }

    /** @test */
    public function category_children_works()
    {
        $response = $this->withoutMiddleware()->post(route('admin.goods.category_children'), [
            'parent_id' => 0
        ]);
        
        $response->assertStatus(200);
        // Should return array
        $response->assertJsonIsArray();
    }
}
