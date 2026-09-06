<?php

namespace App\Providers;

use App\AI\Contracts\AIProviderInterface;
use App\AI\Providers\OpenAIProvider;
use App\AI\Tools\AddToCartTool;
use App\AI\Tools\CheckStockTool;
use App\AI\Tools\GetCartTool;
use App\AI\Tools\GetCheckoutUrlTool;
use App\AI\Tools\GetProductTool;
use App\AI\Tools\GetProductVariationsTool;
use App\AI\Tools\RemoveFromCartTool;
use App\AI\Tools\SearchProductsTool;
use App\AI\Tools\ToolRegistry;
use App\AI\Tools\UpdateCartTool;
use App\Billing\Contracts\PaymentProviderInterface;
use App\Billing\Providers\ManualPaymentProvider;
use App\Services\Ai\AiSettingsService;
use App\Services\WooCommerce\Contracts\WooCommerceClientInterface;
use App\Services\WooCommerce\WordPressWooCommerceClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiSettingsService::class);
        $this->app->singleton(AIProviderInterface::class, OpenAIProvider::class);
        $this->app->singleton(WooCommerceClientInterface::class, WordPressWooCommerceClient::class);
        $this->app->singleton(PaymentProviderInterface::class, ManualPaymentProvider::class);

        $this->app->singleton(ToolRegistry::class, function ($app): ToolRegistry {
            return new ToolRegistry([
                $app->make(SearchProductsTool::class),
                $app->make(GetProductTool::class),
                $app->make(GetProductVariationsTool::class),
                $app->make(CheckStockTool::class),
                $app->make(GetCartTool::class),
                $app->make(AddToCartTool::class),
                $app->make(RemoveFromCartTool::class),
                $app->make(UpdateCartTool::class),
                $app->make(GetCheckoutUrlTool::class),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
        Paginator::useBootstrapFour();

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by(Str::transliterate(
                Str::lower($request->string('email')->toString()).'|'.$request->ip(),
            ));
        });
    }
}
