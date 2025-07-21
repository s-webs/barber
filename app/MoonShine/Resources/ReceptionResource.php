<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reception;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<Reception>
 */
class ReceptionResource extends ModelResource
{
    protected string $model = Reception::class;

    protected string $title = 'Ресепшен';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Филиал', 'branch', 'name', resource: BranchResource::class),
            Text::make('Название', 'name')->unescape(),
            Text::make('Token', 'auth_token'),
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
                BelongsTo::make('Филиал', 'branch', 'name', resource: BranchResource::class),
                Text::make('Название', 'name')->unescape(),
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
     * @param Reception $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
