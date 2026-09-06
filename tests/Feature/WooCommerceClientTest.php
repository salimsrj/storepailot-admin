<?php

namespace Tests\Feature;

use App\Exceptions\WooCommerceException;
use App\Models\Site;
use App\Services\WooCommerce\WordPressWooCommerceClient;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WooCommerceClientTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_search_returns_normalized_products(): void
    {
        Http::preventStrayRequests();
        $site = Site::factory()->withSecret($this->siteSecret())->create();

        Http::fake([
            $site->url.'/wp-json/commercepilot/v1/products/search' => Http::response([
                'products' => [[
                    'id' => 123,
                    'name' => 'Nike Running Shoe',
                    'price' => '89.00',
                    'currency' => 'USD',
                    'stock_status' => 'instock',
                    'image' => 'https://example.com/image.jpg',
                    'url' => 'https://example.com/product/nike',
                    'short_description' => 'Running shoe',
                    'categories' => ['Running'],
                    'has_variations' => true,
                ]],
            ]),
        ]);

        $products = app(WordPressWooCommerceClient::class)->searchProducts($site, ['query' => 'nike']);

        $this->assertSame('Nike Running Shoe', $products[0]->name);
        $this->assertSame('89.00', $products[0]->price);
        Http::assertSent(fn ($request): bool => $request->hasHeader('X-CommercePilot-Signature'));
    }

    public function test_get_product_and_variations_and_stock(): void
    {
        Http::preventStrayRequests();
        $site = Site::factory()->withSecret($this->siteSecret())->create();

        Http::fake([
            $site->url.'/wp-json/commercepilot/v1/products/10' => Http::response([
                'id' => 10,
                'name' => 'Tee',
                'price' => '12.00',
                'currency' => 'USD',
                'stock_status' => 'instock',
                'url' => 'https://example.com/tee',
                'categories' => [],
                'has_variations' => true,
            ]),
            $site->url.'/wp-json/commercepilot/v1/products/10/variations' => Http::response([
                'variations' => [['id' => 11, 'name' => 'Small']],
            ]),
            $site->url.'/wp-json/commercepilot/v1/stock' => Http::response([
                'in_stock' => true,
                'quantity' => 4,
            ]),
        ]);

        $client = app(WordPressWooCommerceClient::class);

        $this->assertSame('Tee', $client->getProduct($site, 10)->name);
        $this->assertSame(11, $client->getProductVariations($site, 10)[0]['id']);
        $this->assertTrue($client->checkStock($site, 10)['in_stock']);
    }

    public function test_cart_and_checkout_use_the_wordpress_contract(): void
    {
        Http::preventStrayRequests();
        $site = Site::factory()->withSecret($this->siteSecret())->create();

        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/checkout')) {
                return Http::response(['url' => 'https://example.com/checkout']);
            }

            if (str_contains($request->url(), '/cart/items')) {
                return Http::response([
                    'items' => [['key' => 'abc', 'quantity' => 1]],
                    'total' => '10.00',
                    'currency' => 'USD',
                    'item_count' => 1,
                ]);
            }

            return Http::response([
                'items' => [],
                'total' => '0.00',
                'currency' => 'USD',
                'item_count' => 0,
            ]);
        });

        $client = app(WordPressWooCommerceClient::class);
        $visitor = '11111111-1111-1111-1111-111111111111';

        $this->assertSame(0, $client->getCart($site, $visitor)->itemCount);
        $this->assertSame(1, $client->addToCart($site, $visitor, ['product_id' => 1, 'quantity' => 1])->itemCount);
        $this->assertSame(1, $client->updateCart($site, $visitor, ['item_key' => 'abc', 'quantity' => 2])->itemCount);
        $this->assertSame(1, $client->removeFromCart($site, $visitor, 'abc')->itemCount);
        $this->assertSame('https://example.com/checkout', $client->getCheckoutUrl($site, $visitor)['url']);
    }

    public function test_store_outage_is_normalized(): void
    {
        Http::preventStrayRequests();
        $site = Site::factory()->withSecret($this->siteSecret())->create();
        Http::fake([
            $site->url.'/*' => Http::response(['error' => 'down'], 503),
        ]);

        $this->expectException(WooCommerceException::class);

        app(WordPressWooCommerceClient::class)->getProduct($site, 1);
    }
}
