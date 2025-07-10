@extends('layouts.booking')

@section('content')
    <div class="h-screen">
        <div class="text-[12px] text-gray-400 text-center font-semibold pt-2">
            <a href="https://s-webs.kz" target="_blank">Готовое решение для барбершопа S-WEBS</a>
        </div>

        <div class="flex justify-center items-center container mx-auto px-4 mt-4">
            <div
                class="bg-[var(--color-halftone)] w-full max-w-3xl h-[90%] rounded-[15px] flex flex-col overflow-hidden">
                <div x-data="bookingForm()" x-init="init()" class="flex flex-col flex-1 overflow-hidden relative">

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
                                        <button @click="selectionType = 'service'; step = 3"
                                                class="block w-full text-left px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
                                            Услуги
                                        </button>
                                        <button @click="selectionType = 'barber'; step = 3"
                                                class="block w-full text-left px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
                                            Мастера
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- Шаг 3 -->
                            <div x-show="step === 3">
                                <template x-if="selectionType === 'barber'">
                                    <div>
                                        <h2 class="text-xl font-bold mb-4">Выберите мастера</h2>
                                        <ul class="space-y-2">
                                            <template x-for="barber in barbers" :key="barber.id">
                                                <li>
                                                    <button
                                                        @click="barber_id = barber.id; loadWorkingDays(barber.id); loadServicesForBarber(); step = 4"
                                                        class="block w-full text-left px-4 py-2 border border-gray-300 rounded hover:bg-gray-100"
                                                        x-text="barber.name"
                                                    ></button>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>

                                <template x-if="selectionType === 'service'">
                                    <div>
                                        <h2 class="text-xl font-bold mb-4">Выберите услугу</h2>
                                        <ul class="space-y-2">
                                            <template x-for="service in services" :key="service.id">
                                                <li>
                                                    <button
                                                        @click="service_id = service.id; loadBarbersForService(); step = 4"
                                                        class="block w-full text-left px-4 py-2 border border-gray-300 rounded hover:bg-gray-100"
                                                        x-text="service.name"
                                                    ></button>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>

                            <!-- Шаг 4 -->
                            <template x-if="step === 4">
                                <div>
                                    <template x-if="selectionType === 'service'">
                                        <div>
                                            <h2 class="text-xl font-bold mb-4">Выберите мастера</h2>
                                            <ul class="space-y-2">
                                                <template x-for="barber in barbersForService" :key="barber.id">
                                                    <li>
                                                        <button
                                                            @click="barber_id = barber.id; loadWorkingDays(barber.id); step = 5"
                                                            class="block w-full text-left px-4 py-2 border border-gray-300 rounded hover:bg-gray-100"
                                                            :class="{ 'bg-gray-100': barber_id === barber.id }"
                                                            x-text="barber.name"
                                                        ></button>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </template>

                                    <template x-if="selectionType === 'barber'">
                                        <div>
                                            <h2 class="text-xl font-bold mb-4">Выберите услугу</h2>
                                            <ul class="space-y-2">
                                                <template x-for="service in servicesForBarber" :key="service.id">
                                                    <li>
                                                        <button
                                                            @click="service_id = service.id; step = 5"
                                                            class="block w-full text-left px-4 py-2 border border-gray-300 rounded hover:bg-gray-100"
                                                        >
                                                            <!-- Добавлено отображение цены -->
                                                            <div class="flex justify-between items-center">
                                                                <span x-text="service.name"></span>
                                                                <span class="font-semibold"
                                                                      x-text="service.price + ' ₸'"></span>
                                                            </div>
                                                        </button>
                                                    </li>
                                                </template>
                                            </ul>
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
                                    <p><strong>Услуга:</strong> <span x-text="getServiceName(service_id)"></span></p>
                                    <p><strong>Дата:</strong> <span x-text="selected_date"></span></p>
                                    <p><strong>Время:</strong> <span x-text="selected_time"></span></p>
                                </div>
                            </template>
                        </div>

                        <!-- Кнопки -->
                        <div class="mt-4 pt-4 border-t flex justify-between gap-4">
                            <button @click="prevStep()" class="bg-gray-200 text-black px-4 py-2 rounded w-1/2"
                                    x-show="step > 1">Назад
                            </button>

                            <button @click="nextStep()" class="bg-black text-white px-4 py-2 rounded w-full"
                                    :disabled="(step === 1 && !branch_id) || (step === 2 && !selectionType)">
                                Далее
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

    <script>
        function bookingForm() {
            return {
                step: 1,
                selectionType: null,
                branches: [],
                barbers: [],
                barbersForService: [],
                services: [],
                servicesForBarber: [],
                branch_id: null,
                barber_id: null,
                service_id: null,
                selected_date: null,
                selected_time: null,
                available_times: [],
                workingDays: [],
                loading: false,

                get minDate() {
                    return new Date().toISOString().split('T')[0];
                },

                async init() {
                    this.loading = true;
                    await Promise.all([
                        this.loadBranches(),
                        this.loadServices(),
                    ]);
                    if (this.branches.length === 1) {
                        this.branch_id = this.branches[0].id;
                        await this.loadBarbersForBranch();
                        this.step = 2;
                    }
                    this.loading = false;
                },

                async loadBranches() {
                    const res = await fetch('/api/branches');
                    this.branches = await res.json();
                },

                async loadServices() {
                    const res = await fetch('/api/services');
                    this.services = await res.json();
                },

                async loadBarbersForBranch() {
                    if (!this.branch_id) return;
                    this.loading = true;
                    const res = await fetch(`/api/barbers/by-branch/${this.branch_id}`);
                    this.barbers = await res.json();
                    this.loading = false;
                },

                async loadBarbersForService() {
                    if (!this.service_id || !this.branch_id) return;
                    this.loading = true;
                    const res = await fetch(`/api/barbers/by-service/${this.service_id}?branch_id=${this.branch_id}`);
                    this.barbersForService = await res.json();
                    this.loading = false;
                },

                async loadServicesForBarber() {
                    if (!this.barber_id || !this.branch_id) return;
                    this.loading = true;
                    const res = await fetch(`/api/services/by-barber/${this.barber_id}?branch_id=${this.branch_id}`);
                    this.servicesForBarber = await res.json();
                    this.loading = false;
                },

                async loadAvailableTimes() {
                    if (!this.selected_date || !this.barber_id || !this.service_id) return;
                    this.loading = true;
                    const res = await fetch(`/api/barbers/${this.barber_id}/available-times?service_id=${this.service_id}&date=${this.selected_date}`);
                    this.available_times = await res.json();
                    this.loading = false;
                },

                async loadWorkingDays(barberId) {
                    if (!barberId) return;
                    const res = await fetch(`/api/barbers/${barberId}/working-days`);
                    this.workingDays = await res.json(); // [1,2,3,4,5]
                },

                async nextStep() {
                    this.loading = true;
                    if (this.step === 1) {
                        await this.loadBarbersForBranch();
                    } else if (this.step === 2 && this.selectionType === 'barber') {
                        // Рабочие дни теперь загружаются при выборе барбера
                    } else if (this.step === 3 && this.selectionType === 'barber') {
                        await this.loadServicesForBarber();
                    } else if (this.step === 3 && this.selectionType === 'service') {
                        await this.loadBarbersForService();
                    }
                    this.step++;
                    this.loading = false;
                },

                async prevStep() {
                    this.loading = true;
                    this.step--;
                    if (this.step === 1 && this.branch_id) {
                        await this.loadBarbersForBranch();
                    }
                    this.loading = false;
                },

                getBranchName(id) {
                    const branch = this.branches.find(b => b.id === id);
                    return branch ? branch.name : '—';
                },

                getBarberName(id) {
                    const barber = this.barbers.find(b => b.id === id)
                        || this.barbersForService.find(b => b.id === id);
                    return barber ? barber.name : '—';
                },

                getServiceName(id) {
                    const service = this.services.find(s => s.id === id)
                        || this.servicesForBarber.find(s => s.id === id);
                    return service ? service.name : '—';
                },

                // calendar
                calendar() {
                    const self = this;

                    return {
                        currentDate: dayjs(),
                        selected: self.selected_date,

                        get currentMonth() {
                            return this.currentDate.month();
                        },
                        get currentYear() {
                            return this.currentDate.year();
                        },
                        get daysInMonth() {
                            return Array.from(
                                {length: dayjs().year(this.currentYear).month(this.currentMonth).daysInMonth()},
                                (_, i) => i + 1
                            );
                        },
                        get blanks() {
                            const firstDay = dayjs().year(this.currentYear).month(this.currentMonth).date(1).day();
                            return Array((firstDay + 6) % 7).fill(null); // Пн = 0
                        },
                        get monthLabel() {
                            return this.currentDate.format('MMMM YYYY');
                        },
                        isBeforeToday(year, month, day) {
                            return dayjs().year(year).month(month).date(day).isBefore(dayjs(), 'day');
                        },
                        // ИСПРАВЛЕННЫЙ МЕТОД: преобразование формата дней недели
                        isWorkingDay(year, month, day) {
                            const dateObj = dayjs().year(year).month(month).date(day);
                            const weekday = dateObj.day(); // 0 (вс) — 6 (сб)

                            // Преобразование в ISO формат (пн=1, вт=2,..., вс=7)
                            const isoWeekday = weekday === 0 ? 7 : weekday;

                            return self.workingDays.includes(isoWeekday);
                        },
                        formatDate(year, month, day) {
                            return dayjs().year(year).month(month).date(day).format('YYYY-MM-DD');
                        },
                        selectDate(day) {
                            const date = this.formatDate(this.currentYear, this.currentMonth, day);
                            this.selected = date;
                            self.selected_date = date;
                            self.loadAvailableTimes();
                        },
                        prevMonth() {
                            this.currentDate = this.currentDate.subtract(1, 'month');
                        },
                        nextMonth() {
                            this.currentDate = this.currentDate.add(1, 'month');
                        },
                        init() {
                            this.currentDate = dayjs();
                        }
                    };
                }
            }
        }
    </script>
@endsection
