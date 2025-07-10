<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Model;
use App\Models\Barber;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Url;

/**
 * @extends ModelResource<Barber>
 */
class BarberResource extends ModelResource
{
    protected string $model = Barber::class;

    protected string $title = 'Мастера';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Image::make('Фото', 'photo'),
            BelongsTo::make('Салон', 'branch', 'name', resource: BranchResource::class),
            Text::make('Имя', 'name'),
            Text::make('Уровень', 'level'),
            BelongsToMany::make('Услуги', 'services', 'name', resource: ServiceResource::class)->onlyCount(),
            Switcher::make('Работает', 'is_enabled')
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                BelongsTo::make('Салон', 'branch', 'name', resource: BranchResource::class),
                Text::make('Имя', 'name'),
                Text::make('Уровень', 'level'),
                Image::make('Фото', 'photo')
                    ->disk('public')
                    ->dir('uploads/barbers'),
                Json::make('Социальные сети', 'socials')
                    ->fields([
                        Select::make('Социальная сеть', 'type')
                            ->options([
                                'facebook' => 'Facebook',
                                'instagram' => 'Instagram',
                                'youtube' => 'YouTube',
                                'tiktok' => 'TikTok',
                            ]),
                        Url::make('Ссылка', 'url')
                    ])
                    ->removable(),
                BelongsToMany::make('Услуги', 'services', 'name', resource: ServiceResource::class)
                    ->selectMode(),
                HasMany::make('Расписание', 'shedules', 'day_of_week', resource: ScheduleResource::class)
                    ->creatable(),
                Switcher::make('Работает', 'is_enabled')
            ])
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make(),
        ];
    }

    /**
     * @param Barber $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
