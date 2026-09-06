<?php

namespace App\Services\WooCommerce\Contracts;

use App\Models\Site;
use App\Services\WooCommerce\DTOs\CartData;
use App\Services\WooCommerce\DTOs\ProductData;

interface WooCommerceClientInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return list<ProductData>
     */
    public function searchProducts(Site $site, array $filters): array;

    public function getProduct(Site $site, int $productId): ProductData;

    /**
     * @return list<array<string, mixed>>
     */
    public function getProductVariations(Site $site, int $productId): array;

    /**
     * @return array<string, mixed>
     */
    public function checkStock(Site $site, int $productId, ?int $variationId = null): array;

    public function getCart(Site $site, string $visitorUuid): CartData;

    /**
     * @param  array<string, mixed>  $item
     */
    public function addToCart(Site $site, string $visitorUuid, array $item): CartData;

    public function removeFromCart(Site $site, string $visitorUuid, string $itemKey): CartData;

    /**
     * @param  array<string, mixed>  $item
     */
    public function updateCart(Site $site, string $visitorUuid, array $item): CartData;

    /**
     * @return array{url: string}
     */
    public function getCheckoutUrl(Site $site, string $visitorUuid): array;
}
