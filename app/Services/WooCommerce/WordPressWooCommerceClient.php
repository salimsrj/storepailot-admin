<?php

namespace App\Services\WooCommerce;

use App\Exceptions\WooCommerceException;
use App\Models\Site;
use App\Services\Security\HmacSigner;
use App\Services\Security\SiteSecretService;
use App\Services\WooCommerce\Contracts\WooCommerceClientInterface;
use App\Services\WooCommerce\DTOs\CartData;
use App\Services\WooCommerce\DTOs\ProductData;
use App\Support\RequestId;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WordPressWooCommerceClient implements WooCommerceClientInterface
{
    public function __construct(
        private HmacSigner $signer,
        private SiteSecretService $secrets,
    ) {}

    public function searchProducts(Site $site, array $filters): array
    {
        $payload = $this->request($site, 'POST', '/wp-json/commercepilot/v1/products/search', $filters);
        $products = $payload['products'] ?? $payload;

        if (! is_array($products)) {
            throw WooCommerceException::invalidResponse();
        }

        return array_values(array_map(
            fn (array $product): ProductData => ProductData::fromArray($product),
            array_filter($products, 'is_array'),
        ));
    }

    public function getProduct(Site $site, int $productId): ProductData
    {
        $payload = $this->request($site, 'GET', '/wp-json/commercepilot/v1/products/'.$productId);

        return ProductData::fromArray($payload);
    }

    public function getProductVariations(Site $site, int $productId): array
    {
        $payload = $this->request($site, 'GET', '/wp-json/commercepilot/v1/products/'.$productId.'/variations');

        return array_values($payload['variations'] ?? $payload);
    }

    public function checkStock(Site $site, int $productId, ?int $variationId = null): array
    {
        return $this->request($site, 'POST', '/wp-json/commercepilot/v1/stock', [
            'product_id' => $productId,
            'variation_id' => $variationId,
        ]);
    }

    public function getCart(Site $site, string $visitorUuid): CartData
    {
        return CartData::fromArray($this->request($site, 'GET', '/wp-json/commercepilot/v1/cart', [
            'visitor_id' => $visitorUuid,
        ]));
    }

    public function addToCart(Site $site, string $visitorUuid, array $item): CartData
    {
        return CartData::fromArray($this->request($site, 'POST', '/wp-json/commercepilot/v1/cart/items', [
            'visitor_id' => $visitorUuid,
            ...$item,
        ]));
    }

    public function removeFromCart(Site $site, string $visitorUuid, string $itemKey): CartData
    {
        return CartData::fromArray($this->request($site, 'DELETE', '/wp-json/commercepilot/v1/cart/items', [
            'visitor_id' => $visitorUuid,
            'item_key' => $itemKey,
        ]));
    }

    public function updateCart(Site $site, string $visitorUuid, array $item): CartData
    {
        return CartData::fromArray($this->request($site, 'PATCH', '/wp-json/commercepilot/v1/cart/items', [
            'visitor_id' => $visitorUuid,
            ...$item,
        ]));
    }

    public function getCheckoutUrl(Site $site, string $visitorUuid): array
    {
        $payload = $this->request($site, 'GET', '/wp-json/commercepilot/v1/checkout', [
            'visitor_id' => $visitorUuid,
        ]);

        return [
            'url' => (string) ($payload['url'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function request(Site $site, string $method, string $path, array $payload = []): array
    {
        $started = microtime(true);
        $usesJsonBody = $method !== 'GET';
        $body = ($usesJsonBody && $payload !== []) ? (string) json_encode($payload, JSON_THROW_ON_ERROR) : '';
        $timestamp = (string) now()->timestamp;
        $signature = $this->signer->sign($timestamp, $body, $this->secrets->secretFor($site));

        try {
            $response = $this->http($timestamp, $signature)
                ->send($method, rtrim($site->url, '/').$path, $usesJsonBody
                    ? ($payload === [] ? [] : ['json' => $payload])
                    : ['query' => $payload])
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            Log::channel('api')->warning('woocommerce.request_failed', [
                'request_id' => RequestId::current(),
                'site_id' => $site->id,
                'path' => $path,
                'status' => 'error',
            ]);

            throw WooCommerceException::unavailable();
        } catch (Throwable $exception) {
            throw WooCommerceException::unavailable();
        }

        Log::channel('api')->info('woocommerce.request', [
            'request_id' => RequestId::current(),
            'site_id' => $site->id,
            'path' => $path,
            'latency_ms' => (int) round((microtime(true) - $started) * 1000),
            'status' => $response->status(),
        ]);

        $json = $response->json();

        if (! is_array($json)) {
            throw WooCommerceException::invalidResponse();
        }

        return $json;
    }

    private function http(string $timestamp, string $signature): PendingRequest
    {
        $timeout = (int) config('commercepilot.wordpress.timeout');

        return Http::acceptJson()
            ->connectTimeout(3)
            ->timeout($timeout)
            ->retry([100, 250], throw: false)
            ->withHeaders([
                'X-CommercePilot-Timestamp' => $timestamp,
                'X-CommercePilot-Signature' => $signature,
                'X-Request-ID' => RequestId::current(),
                'User-Agent' => 'CommercePilot/1.0',
            ]);
    }
}
