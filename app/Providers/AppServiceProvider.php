<?php

namespace App\Providers;

use App\Models\Color;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $color_main = Color::query()->where('name', '=', 'color-main')->first();
        $color_secondary = Color::query()->where('name', '=', 'color-secondary')->first();
        $color_halftone = Color::query()->where('name', '=', 'color-halftone')->first();
        $color_dark = Color::query()->where('name', '=', 'color-dark')->first();

        view()->share('color_main', $color_main);
        view()->share('color_secondary', $color_secondary);
        view()->share('color_halftone', $color_halftone);
        view()->share('color_dark', $color_dark);
    }
}
