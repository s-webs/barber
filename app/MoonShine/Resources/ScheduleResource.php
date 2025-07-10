<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Model;
use App\Models\Schedule;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<Schedule>
 */
class ScheduleResource extends ModelResource
{
    protected string $model = Schedule::class;

    protected string $title = 'Расписание';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Мастер', 'barber', 'name', resource: BarberResource::class),
            Text::make('День недели', 'day_of_week'),
            Text::make('Начало', 'start_time')->setAttribute('type', 'time'),
            Text::make('Окончание', 'end_time')->setAttribute('type', 'time'),
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
                BelongsTo::make('Мастер', 'barber', 'name', resource: BarberResource::class),
                Select::make('День недели', 'day_of_week')->options([
                    'monday' => 'Понедельник',
                    'tuesday' => 'Вторник',
                    'wednesday' => 'Среда',
                    'thursday' => 'Четверг',
                    'friday' => 'Пятница',
                    'saturday' => 'Суббота',
                    'sunday' => 'Воскресенье',
                ]),
                Text::make('Начало', 'start_time')->setAttribute('type', 'time'),
                Text::make('Окончание', 'end_time')->setAttribute('type', 'time'),
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
     * @param Schedule $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
