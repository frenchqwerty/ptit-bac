<?php

namespace App\Providers;

use App\Domain\Score\Services\ScoreEngine;
use App\Services\Contracts\CategoryValidatorInterface;
use App\Services\Validators\BrandValidator;
use App\Services\Validators\CityValidator;
use App\Services\Validators\CountryValidator;
use App\Services\Validators\FirstNameValidator;
use App\Services\Validators\MovieValidator;
use App\Services\Validators\PokemonValidator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register all category validators
        $this->app->tag([
            CityValidator::class,
            CountryValidator::class,
            FirstNameValidator::class,
            PokemonValidator::class,
            BrandValidator::class,
            MovieValidator::class,
        ], CategoryValidatorInterface::class);

        // Register ScoreEngine with all validators injected
        $this->app->singleton(ScoreEngine::class, function ($app): ScoreEngine {
            /** @var array<CategoryValidatorInterface> $validators */
            $validators = $app->tagged(CategoryValidatorInterface::class);

            return new ScoreEngine(iterator_to_array($validators));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
