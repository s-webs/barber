<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use App\MoonShine\Resources\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRoleResource;
use App\MoonShine\Resources\ServiceResource;
use App\MoonShine\Resources\BarberResource;
use App\MoonShine\Resources\ColorResource;
use App\MoonShine\Resources\BranchResource;
use App\MoonShine\Resources\ScheduleResource;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  MoonShine  $core
     * @param  MoonShineConfigurator  $config
     *
     */
    public function boot(CoreContract $core, ConfiguratorContract $config): void
    {
        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                ServiceResource::class,
                BarberResource::class,
                ColorResource::class,
                BranchResource::class,
                ScheduleResource::class,
            ])
            ->pages([
                ...$config->getPages(),
            ])
        ;
    }
}
