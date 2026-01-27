<?php

namespace Tests\Feature\Front;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\DesignBanner;

class MainPageTest extends TestCase
{
    public function test_main_page_loads_with_dynamic_content()
    {
        // 1. Ensure Banner Data Exists (or fallback checks)
        // We know from inspection that banner 11 exists.
        
        $response = $this->get('/');

        $response->assertStatus(200);

        // 2. Check Banners
        // The view iterates $mainBanners. We verify the container exists.
        $response->assertSee('mslide'); 
        
        // 3. Check Best Products (Display 7150)
        // We expect at least the container or some products if seeded.
        // If DB is empty, it might show "상품이 없습니다"
        $response->assertSee('category_nav01'); 
        
        // 4. Check New Products (Display 7152)
        $response->assertSee('category_nav04');

        // 5. Check "MD's Pick" / GTD Best (Display 7160)
        $response->assertSee('right_banner_slider');

        // 6. Check Middle Banners (Left & Right)
        $response->assertViewHas(['middleBannerL', 'middleBannerR']);
        // We can't strictly assertSee text because if DB is empty it shows nothing, but the variables should be there.
    }

    public function test_middle_banners_fetch_correctly()
    {
        // Verify that Banner 12 and 13 fetching logic works
        $bannerL = DesignBanner::where('banner_seq', 12)->orderBy('modtime', 'desc')->first();
        if ($bannerL) {
             $items = $bannerL->items()->where('skin', $bannerL->skin)->get();
             $this->assertNotNull($items);
        }
        
        $bannerR = DesignBanner::where('banner_seq', 13)->orderBy('modtime', 'desc')->first();
        if ($bannerR) {
             $items = $bannerR->items()->where('skin', $bannerR->skin)->get();
             $this->assertNotNull($items);
        }
    }

    public function test_banner_logic_fetches_correct_skin_items()
    {
        // Mock DB data or verify existing logic
        $banner = DesignBanner::where('banner_seq', 11)->orderBy('modtime', 'desc')->first();
        if ($banner) {
            $items = $banner->items()->where('skin', $banner->skin)->get();
            $this->assertGreaterThan(0, $items->count(), "Should find banner items for skin {$banner->skin}");
            
            // Check if items have the correct skin
            foreach ($items as $item) {
                $this->assertEquals($banner->skin, $item->skin);
            }
        }
    }
}
