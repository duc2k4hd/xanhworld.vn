<?php

namespace Tests\Unit;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheClearingTest extends TestCase
{
    public function test_product_clears_cache_logic()
    {
        $product = new Product(['slug' => 'test-product']);
        $product->id = 123;
        
        $cacheKey = 'product_detail_test-product';
        $globalKey = 'new_products';

        Cache::put($cacheKey, 'cached_data');
        Cache::put($globalKey, 'cached_list');

        $this->assertTrue(Cache::has($cacheKey));

        // Manually call clearCaches since we can't save to DB
        $product->clearCaches();

        $this->assertFalse(Cache::has($cacheKey), 'Product detail cache should be cleared');
        $this->assertFalse(Cache::has($globalKey), 'Global product list cache should be cleared');
    }

    public function test_setting_clears_cache_logic()
    {
        $setting = new Setting();
        $cacheKey = 'settings';

        Cache::put($cacheKey, 'cached_settings');
        $this->assertTrue(Cache::has($cacheKey));

        $setting->clearCaches();

        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_category_clears_cache_logic()
    {
        $category = new Category();
        $cacheKey = 'xanhworld_header_main_nav_category_lists';

        Cache::put($cacheKey, 'cached_menu');
        $this->assertTrue(Cache::has($cacheKey));

        $category->clearCaches();

        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_post_clears_cache_logic()
    {
        $post = new Post();
        $post->id = 999;
        $cacheKey = 'blog_related_posts_' . $post->id;

        Cache::put($cacheKey, 'cached_posts');
        $this->assertTrue(Cache::has($cacheKey));

        $post->clearCaches();

        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_banner_clears_cache_logic()
    {
        $banner = new Banner();
        $cacheKey = 'banners_home_parent';

        Cache::put($cacheKey, 'cached_banners');
        $this->assertTrue(Cache::has($cacheKey));

        $banner->clearCaches();

        $this->assertFalse(Cache::has($cacheKey));
    }
}
