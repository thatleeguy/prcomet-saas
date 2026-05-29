<?php

namespace App\Providers;

use App\Listeners\NotifyAdminsOfTeamSignup;
use App\Services\Llm\AnthropicLlmClient;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\LlmClient;
use App\Services\PriceData\PriceProvider;
use App\Services\PriceData\StubPriceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Events\TeamCreated;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // LLM client — real Anthropic if an API key is configured, otherwise
        // the in-memory fake so dev/test environments don't accidentally hit
        // the network. Tests can also override via $this->app->instance().
        $this->app->singleton(LlmClient::class, function ($app) {
            $apiKey = (string) config('services.anthropic.api_key', '');

            if ($apiKey === '') {
                return new FakeLlmClient;
            }

            return new AnthropicLlmClient(
                apiKey: $apiKey,
                tracker: $app->make(\App\Services\Llm\LlmUsageTracker::class),
                defaultModel: config('services.anthropic.default_model'),
            );
        });

        // Commodity price provider — v0 ships with a stub. Production will
        // swap a real source (LBMA / Kitco / paid API) via container override.
        $this->app->singleton(PriceProvider::class, fn () => new StubPriceProvider);
    }

    public function boot(): void
    {
        // Explicit event wiring — clearer than auto-discovery and the listener
        // count is small. Easy to find when chasing "who handles this event?"
        Event::listen(TeamCreated::class, NotifyAdminsOfTeamSignup::class);
    }
}
