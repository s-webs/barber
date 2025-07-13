@extends('layouts.booking')

@section('content')
    <style>
        html, body {
            height: 100%;
            overflow: hidden;
            margin: 0;
        }
        .full-screen-safe {
            height: 100svh;
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
        }
    </style>
    <div class="full-screen-safe flex flex-col">
        <div class="text-[12px] text-gray-400 text-center font-semibold border-b py-2">
            <a href="https://s-webs.kz" target="_blank">Готовое решение для барбершопа S-WEBS</a>
        </div>

        <div class="flex-grow flex justify-center items-center px-4">
            <div
                class="bg-[var(--color-halftone)] w-full max-w-3xl rounded-[15px] flex flex-col overflow-hidden">
                <div x-data="bookingForm()" x-init="init()" x-effect="step === 6 && initPhoneMask()"
                     class="flex flex-col flex-1 overflow-hidden relative">

                    <!-- Лоадер -->
                    <div x-show="loading"
                         class="absolute inset-0 z-50 bg-white/70 backdrop-blur-sm flex items-center justify-center">
                        <svg class="animate-spin h-10 w-10 text-black" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                    </div>

                    <div class="p-4 overflow-y-auto scrollbar-none">
                        <!-- Шаги -->
                        <div class="flex-1 space-y-6">

                            <!-- Шаг 1: Филиал -->
                            <template x-if="step === 1">
                                <div>
                                    <h2 class="text-xl font-bold mb-4">Выберите филиал</h2>
                                    <template x-if="branches.length === 1">
                                        <div x-text="'Выбран филиал: ' + branches[0].name"></div>
                                    </template>
                                    <div class="space-y-2">
                                        <template x-for="branch in branches" :key="branch.id">
                                            <div
                                                @click="branch_id = branch.id"
                                                :class="branch_id === branch.id ? 'border-2 border-black shadow-lg' : 'border border-gray-300'"
                                                class="cursor-pointer rounded-lg overflow-hidden shadow-sm hover:shadow-md transition bg-white"
                                            >
                                                <img :src="branch.image" alt="" class="w-full h-40 object-cover">
                                                <div class="p-4">
                                                    <p class="text-sm text-gray-500" x-text="branch.address"></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Шаг 2: Что сначала -->
                            <template x-if="step === 2">
                                <div>
                                    <h2 class="text-xl font-bold mb-4">Что хотите выбрать сначала?</h2>
                                    <div class="space-y-2">
                                        <button @click="selectionType = 'service'"
                                                :class="selectionType === 'service'
                                                    ? 'bg-black text-white'
                                                    : 'bg-white text-black hover:bg-gray-100'"
                                                class="block w-full text-left px-4 py-2 border border-gray-300 rounded">
                                            Услуги
                                        </button>
                                        <button @click="selectionType = 'barber'"
                                                :class="selectionType === 'barber'
                                                    ? 'bg-black text-white'
                                                    : 'bg-white text-black hover:bg-gray-100'"
                                                class="block w-full text-left px-4 py-2 border border-gray-300 rounded">
                                            Мастера
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- Шаг 3 -->
                            <div x-show="step === 3">
                                <!-- Выбор мастера -->
                                <template x-if="selectionType === 'barber'">
                                    <div>
                                        <h2 class="text-xl font-bold mb-4">Выберите мастера</h2>
                                        <div class="space-y-2">
                                            <template x-for="barber in barbers" :key="barber.id">
                                                <button
                                                    @click="barber_id = barber.id"
                                                    :class="barber_id === barber.id
                                ? 'bg-black text-white'
                                : 'bg-white text-black hover:bg-gray-100'"
                                                    class="block w-full text-left px-4 py-2 border border-gray-300 rounded"
                                                >
                                                    <div class="flex items-center">
                                                        <img :src="barber.photo" class="w-10 h-10 rounded-full mr-3">
                                                        <span x-text="barber.name"></span>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <!-- Выбор услуг (множественный выбор) -->
                                <template x-if="selectionType === 'service'">
                                    <div>
                                        <h2 class="text-xl font-bold mb-4">Выберите услуги</h2>
                                        <div class="space-y-2">
                                            <template x-for="service in services" :key="service.id">
                                                <button
                                                    @click="toggleService(service.id)"
                                                    :class="selected_service_ids.includes(service.id)
                                ? 'bg-black text-white'
                                : 'bg-white text-black hover:bg-gray-100'"
                                                    class="block w-full text-left px-4 py-2 border border-gray-300 rounded"
                                                >
                                                    <div class="flex justify-between items-center">
                                                        <span x-text="service.name"></span>
                                                        <span class="font-semibold"
                                                              x-text="service.price + ' ₸'"></span>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Шаг 4 -->
                            <template x-if="step === 4">
                                <div>
                                    <!-- Если сначала выбрали услугу - показываем мастеров -->
                                    <template x-if="selectionType === 'service'">
                                        <div>
                                            <h2 class="text-xl font-bold mb-4">Выберите мастера</h2>
                                            <div class="space-y-2">
                                                <template x-for="barber in barbersForService" :key="barber.id">
                                                    <button
                                                        @click="barber_id = barber.id"
                                                        :class="barber_id === barber.id
                                    ? 'bg-black text-white'
                                    : 'bg-white text-black hover:bg-gray-100'"
                                                        class="block w-full text-left px-4 py-2 border border-gray-300 rounded"
                                                    >
                                                        <div class="flex items-center">
                                                            <img :src="barber.photo"
                                                                 class="w-10 h-10 rounded-full mr-3">
                                                            <span x-text="barber.name"></span>
                                                        </div>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Если сначала выбрали мастера - показываем услуги -->
                                    <template x-if="selectionType === 'barber'">
                                        <div>
                                            <h2 class="text-xl font-bold mb-4">Выберите услуги</h2>
                                            <div class="space-y-2">
                                                <template x-for="service in servicesForBarber" :key="service.id">
                                                    <button
                                                        @click="toggleService(service.id)"
                                                        :class="selected_service_ids.includes(service.id)
                                    ? 'bg-black text-white'
                                    : 'bg-white text-black hover:bg-gray-100'"
                                                        class="block w-full text-left px-4 py-2 border border-gray-300 rounded"
                                                    >
                                                        <div class="flex justify-between items-center">
                                                            <span x-text="service.name"></span>
                                                            <span class="font-semibold"
                                                                  x-text="service.price + ' ₸'"></span>
                                                        </div>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Шаг 5: Дата и время -->
                            <template x-if="step === 5">
                                <div x-data="calendar()" x-init="init()">
                                    <h2 class="text-xl font-bold mb-4">Выберите дату и время</h2>

                                    <!-- Навигация по месяцу -->
                                    <div class="flex items-center justify-between mb-4">
                                        <button @click="prevMonth()" class="text-sm text-gray-500">&larr; Пред</button>
                                        <span class="font-semibold" x-text="monthLabel"></span>
                                        <button @click="nextMonth()" class="text-sm text-gray-500">След &rarr;</button>
                                    </div>

                                    <!-- Сетка дней -->
                                    <div class="grid grid-cols-7 gap-1 text-center text-sm mb-4">
                                        <template x-for="dayName in ['Пн','Вт','Ср','Чт','Пт','Сб','Вс']">
                                            <div class="font-semibold text-gray-500" x-text="dayName"></div>
                                        </template>

                                        <template x-for="blank in blanks">
                                            <div></div>
                                        </template>

                                        <template x-for="day in daysInMonth" :key="day">
                                            <button
                                                :class="[
                                                        'px-2 py-1 rounded',
                                                        isBeforeToday(currentYear, currentMonth, day) || !isWorkingDay(currentYear, currentMonth, day)
                                                            ? 'text-gray-400 bg-gray-100 cursor-not-allowed'
                                                            : 'hover:bg-gray-100 text-black cursor-pointer',
                                                        selected === formatDate(currentYear, currentMonth, day)
                                                            ? 'bg-black text-white'
                                                            : ''
                                                    ]"
                                                :disabled="isBeforeToday(currentYear, currentMonth, day) || !isWorkingDay(currentYear, currentMonth, day)"
                                                @click="selectDate(day)"
                                                x-text="day"
                                            ></button>
                                        </template>
                                    </div>

                                    <!-- Время -->
                                    <div x-show="available_times.length" class="mt-4">
                                        <h3 class="font-semibold mb-2">Выберите время:</h3>
                                        <div class="grid grid-cols-3 gap-2">
                                            <template x-for="time in available_times" :key="time">
                                                <button
                                                    @click="selected_time = time; step = 6"
                                                    :class="[
                                'px-2 py-1 rounded border',
                                selected_time === time ? 'bg-black text-white' : 'bg-white text-black hover:bg-gray-100'
                            ]"
                                                    x-text="time"
                                                ></button>
                                            </template>
                                        </div>
                                    </div>

                                    <div x-show="!available_times.length && selected_date"
                                         class="text-sm text-gray-500 mt-4">
                                        Нет свободного времени на выбранную дату
                                    </div>
                                </div>
                            </template>


                            <!-- Шаг 6: Подтверждение -->
                            <template x-if="step === 6">
                                <div>
                                    <h2 class="text-xl font-bold mb-4">Подтвердите запись</h2>
                                    <p><strong>Филиал:</strong> <span x-text="getBranchName(branch_id)"></span></p>
                                    <p><strong>Мастер:</strong> <span x-text="getBarberName(barber_id)"></span></p>
                                    <p><strong>Услуги:</strong></p>
                                    <ul class="ml-4 list-disc">
                                        <template x-for="serviceId in selected_service_ids" :key="serviceId">
                                            <li x-text="getServiceName(serviceId) + ' - ' + getServicePrice(serviceId) + ' ₸'"></li>
                                        </template>
                                    </ul>
                                    <p class="mt-2"><strong>Общая стоимость:</strong> <span
                                            x-text="getTotalPrice() + ' ₸'"></span></p>
                                    <p><strong>Дата:</strong> <span x-text="selected_date"></span></p>
                                    <p><strong>Время:</strong> <span x-text="selected_time"></span></p>

                                    <div class="mt-4">
                                        <label class="block mb-2 font-medium">Ваше имя</label>
                                        <input type="text" x-model="customer_name"
                                               class="w-full px-3 py-2 border rounded"
                                               placeholder="Введите ваше имя">
                                    </div>

                                    <div class="mt-3">
                                        <label class="block mb-2 font-medium">Телефон</label>
                                        <input
                                            id="phone-input"
                                            type="tel"
                                            x-model="customer_phone"
                                            class="w-full px-3 py-2 border rounded"
                                            placeholder="+7 (___) ___-__-__"
                                        >
                                    </div>

                                </div>
                            </template>
                        </div>

                        <!-- Кнопки -->
                        <div class="mt-4 pt-4 border-t flex justify-between gap-4">
                            <button @click="prevStep()"
                                    class="bg-gray-200 text-black px-4 py-2 rounded w-1/2"
                                    x-show="step > 1">
                                Назад
                            </button>

                            <template x-if="step < 6">
                                <button @click="nextStep()"
                                        :disabled="!canProceed()"
                                        :class="canProceed() ? 'bg-black text-white' : 'bg-gray-400 text-gray-200 cursor-not-allowed'"
                                        class="px-4 py-2 rounded w-full">
                                    Далее
                                </button>
                            </template>

                            <template x-if="step === 6">
                                <button @click="submitBooking()"
                                        :disabled="!canProceed()"
                                        :class="canProceed() ? 'bg-green-600 text-white' : 'bg-gray-400 text-gray-200 cursor-not-allowed'"
                                        class="px-4 py-2 rounded w-full">
                                    Записаться
                                </button>
                            </template>
                        </div>
                        <div class="text-sm text-gray-500 mt-2">
                            Можно ли идти дальше? <span x-text="canProceed() ? 'Да' : 'Нет'"></span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
