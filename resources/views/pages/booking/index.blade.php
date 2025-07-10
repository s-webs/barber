@extends('layouts.booking')

@section('content')
    <div class="h-screen flex justify-center items-center container mx-auto p-4">
        <div class="bg-[var(--color-halftone)] w-full max-w-3xl h-[90%] rounded-[15px] flex flex-col overflow-hidden">
            <div x-data="bookingForm()" x-init="init()" class="flex flex-col flex-1 overflow-hidden relative">
                <!-- Лоадер -->
                <div x-show="loading"
                     class="absolute inset-0 z-50 bg-white/70 backdrop-blur-sm flex items-center justify-center">
                    <svg class="animate-spin h-10 w-10 text-black" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </div>
                <div class="p-4 overflow-y-auto scrollbar-none">
                    <!-- Шаги -->
                    <div class="flex-1 space-y-6">

                        <!-- Шаг 1: Выбор филиала -->
                        <template x-if="step === 1">
                            <div>
                                <h2 class="text-xl font-bold mb-4">Выберите филиал</h2>
                                <template x-if="branches.length === 1">
                                    <div x-text="'Выбран филиал: ' + branches[0].name"></div>
                                </template>
                                <div class="space-y-4">
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

                        <!-- Шаг 2: Что выбрать сначала -->
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
                                                    @click="barber_id = barber.id; loadServicesForBarber(); step = 4"
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
                                                        @click="barber_id = barber.id; loadAvailableTimes(); step = 5"
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
                                                        @click="service_id = service.id; loadAvailableTimes(); step = 5"
                                                        class="block w-full text-left px-4 py-2 border border-gray-300 rounded hover:bg-gray-100"
                                                        x-text="service.name"
                                                    ></button>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Шаг 5: Время -->
                        <template x-if="step === 5">
                            <div>
                                <h2 class="text-xl font-bold mb-4">Выберите время</h2>
                                <ul class="space-y-2">
                                    <template x-for="time in available_times" :key="time">
                                        <li>
                                            <button
                                                @click="selected_time = time; step = 6"
                                                class="block w-full text-left px-4 py-2 border border-gray-300 rounded hover:bg-gray-100"
                                                x-text="time"
                                            ></button>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        <!-- Шаг 6: Подтверждение -->
                        <template x-if="step === 6">
                            <div>
                                <h2 class="text-xl font-bold mb-4">Подтвердите запись</h2>
                                <p><strong>Филиал:</strong> <span x-text="getBranchName(branch_id)"></span></p>
                                <p><strong>Мастер:</strong> <span x-text="getBarberName(barber_id)"></span></p>
                                <p><strong>Услуга:</strong> <span x-text="getServiceName(service_id)"></span></p>
                                <p><strong>Время:</strong> <span x-text="selected_time"></span></p>
                            </div>
                        </template>
                    </div>

                    <!-- Кнопки управления -->
                    <div class="mt-4 pt-4 border-t flex justify-between gap-4">
                        <button
                            @click="prevStep()"
                            class="bg-gray-200 text-black px-4 py-2 rounded w-1/2"
                            x-show="step > 1"
                        >Назад</button>

                        <button
                            @click="nextStep()"
                            class="bg-black text-white px-4 py-2 rounded w-full"
                            :disabled="(step === 1 && !branch_id) || (step === 2 && !selectionType)"
                        >Далее</button>
                    </div>
                    <div class="text-sm text-gray-400 text-center mt-4 font-semibold border-t pt-2">
                        Готовое решение для барбершопа
                        <br />
                        S-WEBS
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                selected_barber: null,
                service_id: null,
                selected_time: null,
                available_times: [],
                loading: false, // флаг загрузки

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
                    await this.delay(400);
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
                    await this.delay(400);
                    this.loading = false;
                },

                async loadBarbersForService() {
                    if (!this.service_id || !this.branch_id) return;
                    this.loading = true;
                    const res = await fetch(`/api/barbers/by-service/${this.service_id}?branch_id=${this.branch_id}`);
                    this.barbersForService = await res.json();
                    await this.delay(400);
                    this.loading = false;
                },

                async loadServicesForBarber() {
                    if (!this.barber_id || !this.branch_id) return;
                    this.loading = true;
                    const res = await fetch(`/api/services/by-barber/${this.barber_id}?branch_id=${this.branch_id}`);
                    this.servicesForBarber = await res.json();
                    await this.delay(400);
                    this.loading = false;
                },

                async loadAvailableTimes() {
                    this.loading = true;
                    await this.delay(500); // имитация загрузки
                    this.available_times = [
                        '10:00', '11:00', '12:00', '13:00',
                        '14:00', '15:00', '16:00', '17:00'
                    ];
                    await this.delay(400);
                    this.loading = false;
                },

                async nextStep() {
                    this.loading = true;
                    if (this.step === 1) {
                        await this.loadBarbersForBranch();
                        this.step++;
                    } else if (this.step === 3 && this.selectionType === 'service') {
                        this.step = 4;
                    } else if (this.step === 3 && this.selectionType === 'barber') {
                        await this.loadServicesForBarber();
                        this.step = 4;
                    } else {
                        this.step++;
                    }
                    await this.delay(400);
                    this.loading = false;
                },

                async prevStep() {
                    this.loading = true;
                    this.step--;

                    // Если возвращаемся на шаг 1 — возможно, надо заново подгрузить барберов
                    if (this.step === 1 && this.branch_id) {
                        await this.loadBarbersForBranch();
                    }

                    await this.delay(400);
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

                delay(ms) {
                    return new Promise(resolve => setTimeout(resolve, ms));
                }
            }
        }
    </script>


@endsection
