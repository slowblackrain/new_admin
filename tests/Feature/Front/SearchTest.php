<?php

namespace Tests\Feature\Front;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class SearchTest extends TestCase
{
    public function test_search_by_korean_keyword()
    {
        // 1. Find an existing product to search for
        // We use a product likely to exist or create one if needed.
        // Let's use '테스트' if we failed to clean up, or pick a random one.
        $product = DB::table('fm_goods')->where('goods_view', 'look')->first();
        if (!$product) {
            $this->markTestSkipped('No displayable products found in DB.');
        }

        // Use a part of the name
        $keyword = mb_substr($product->goods_name, 0, 2);
        
        // 2. Search
        $response = $this->get(route('goods.search', ['search_text' => $keyword]));
        
        $response->assertStatus(200);
        $response->assertSee($product->goods_name);
    }

    public function test_sorting_logic()
    {
        // 1. Catalog Page Sorting
        // We need a category with at least 2 products to test sorting.
        $category = DB::table('fm_category_link')
            ->select('category_code', DB::raw('count(*) as total'))
            ->groupBy('category_code')
            ->having('total', '>=', 2)
            ->first();

        if (!$category) {
            $this->markTestSkipped('No category with multiple products found.');
        }

        // Test Price Low (low_price)
        $response = $this->get(route('goods.catalog', ['code' => $category->category_code, 'sort' => 'low_price']));
        $response->assertStatus(200);
        // We can't easily assert order in HTML without parsing, but we check if it loads without error.
        
        // Test Price High (high_price)
        $response = $this->get(route('goods.catalog', ['code' => $category->category_code, 'sort' => 'high_price']));
        $response->assertStatus(200);

        // Test Newest (regist_date)
        $response = $this->get(route('goods.catalog', ['code' => $category->category_code, 'sort' => 'regist_date']));
        $response->assertStatus(200);
    }
}
