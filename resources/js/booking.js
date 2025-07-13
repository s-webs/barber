import './bootstrap';
import {Alpine} from "alpinejs";
import dayjs from "dayjs";
import Cleave from "cleave.js";
import 'cleave.js/dist/addons/cleave-phone.i18n';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('bookingForm', bookingForm);
});

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
        selected_service_ids: [],
        customer_name: '',
        customer_phone: '',

        async init() {
            this.loading = true;
            await this.loadBranches();

            if (this.branches.length === 1) {
                this.branch_id = this.branches[0].id;
                this.step = 2;
            }

            this.loading = false;
        },

        initPhoneMask() {
            this.$nextTick(() => {
                const input = document.getElementById('phone-input');
                if (input && !input.cleave) {
                    input.cleave = new Cleave(input, {
                        phone: true,
                        phoneRegionCode: 'KZ',
                        prefix: '+7',
                        noImmediatePrefix: false,
                        blocks: [2, 3, 3, 2, 2],
                        delimiters: [' ', '(', ') ', '-', '-'],
                        numericOnly: true
                    });
                }
            });
        },

        get minDate() {
            return new Date().toISOString().split('T')[0];
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
            if (this.selected_service_ids.length === 0 || !this.branch_id) return;
            this.loading = true;

            // Создаем параметр с несколькими service_ids
            const serviceIdsParam = this.selected_service_ids.map(id => `service_ids[]=${id}`).join('&');
            const res = await fetch(`/api/barbers/by-service?branch_id=${this.branch_id}&${serviceIdsParam}`);
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
            if (!this.selected_date || !this.barber_id || this.selected_service_ids.length === 0) return;
            this.loading = true;

            // Рассчитываем общую длительность
            const totalDuration = this.allServices
                .filter(s => this.selected_service_ids.includes(s.id))
                .reduce((sum, service) => sum + (service.duration || 60), 0);

            console.log(totalDuration)

            // Отправляем запрос с общей длительностью
            const res = await fetch(`/api/barbers/${this.barber_id}/available-times?duration=${totalDuration}&date=${this.selected_date}`);
            this.available_times = await res.json();
            this.loading = false;
        },

        async loadWorkingDays(barberId) {
            if (!barberId) return;
            const res = await fetch(`/api/barbers/${barberId}/working-days`);
            this.workingDays = await res.json();
        },

        toggleService(serviceId) {
            if (this.selected_service_ids.includes(serviceId)) {
                this.selected_service_ids = this.selected_service_ids.filter(id => id !== serviceId);
            } else {
                this.selected_service_ids.push(serviceId);
            }
        },

        canProceed() {
            switch (this.step) {
                case 1:
                    return this.branch_id !== null;
                case 2:
                    return this.selectionType !== null;
                case 3:
                    if (this.selectionType === 'barber') {
                        return this.barber_id !== null;
                    } else {
                        return this.selected_service_ids.length > 0;
                    }
                case 4:
                    if (this.selectionType === 'barber') {
                        return this.selected_service_ids.length > 0;
                    } else {
                        return this.barber_id !== null;
                    }
                case 5:
                    return this.selected_date !== null && this.selected_time !== null;
                case 6:
                    return this.customer_name.trim() !== '' &&
                        this.customer_phone.trim() !== '';
                default:
                    return true;
            }
        },

        async nextStep() {
            if (!this.canProceed()) return;

            this.loading = true;
            try {
                if (this.step === 1) {
                    // после выбора филиала, ничего не грузим — просто идём дальше
                }

                if (this.step === 2) {
                    // после выбора типа — грузим нужные данные
                    if (this.selectionType === 'barber') {
                        await this.loadBarbersForBranch(); // всех барберов в филиале
                    } else {
                        await this.loadServices(); // все услуги
                    }
                }

                if (this.step === 3) {
                    // после выбора первой сущности — загружаем вторую
                    if (this.selectionType === 'barber') {
                        await this.loadServicesForBarber();
                    } else {
                        await this.loadBarbersForService();
                    }
                }

                if (this.step === 4) {
                    await this.loadWorkingDays(this.barber_id);
                }

                if (this.step === 5) {
                    await this.loadAvailableTimes();
                }

                if (this.step === 6) {
                    console.log("step:", this.step); // DEBUG
                    this.initPhoneMask();
                }

                this.step++;
            } catch (error) {
                console.error("Ошибка перехода:", error);
            } finally {
                this.loading = false;
            }
        },

        async prevStep() {
            this.loading = true;
            try {
                this.step--;
                if (this.step === 1 && this.branch_id) {
                    await this.loadBarbersForBranch();
                }
            } finally {
                this.loading = false;
            }
        },

        get allServices() {
            return this.selectionType === 'barber'
                ? this.servicesForBarber
                : this.services;
        },

        getBranchName(id) {
            const branch = this.branches.find(b => b.id === id);
            return branch ? branch.name : '—';
        },

        getBarberName(id) {
            const barber = this.barbers.find(b => b.id === id) ||
                this.barbersForService.find(b => b.id === id);
            return barber ? barber.name : '—';
        },

        getServiceName(id) {
            const service = this.allServices.find(s => s.id === id);
            return service ? service.name : '—';
        },

        getServicePrice(id) {
            const service = this.allServices.find(s => s.id === id);
            return service ? service.price : 0;
        },

        getTotalPrice() {
            return this.selected_service_ids.reduce((total, id) => {
                return total + this.getServicePrice(id);
            }, 0);
        },

        async submitBooking() {
            if (!this.canProceed()) return;

            this.loading = true;
            try {
                const response = await fetch('/api/appointments/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        branch_id: this.branch_id, // ✅ ДОБАВЛЕНО
                        barber_id: this.barber_id,
                        service_ids: this.selected_service_ids,
                        date: this.selected_date,
                        time: this.selected_time,
                        customer_name: this.customer_name,
                        customer_phone: this.customer_phone
                    })
                });

                if (!response.ok) {
                    throw new Error('Ошибка записи');
                }

                const data = await response.json();
                window.location.href = '/booking/success';
            } catch (error) {
                console.error("Ошибка создания записи:", error);
                alert('Произошла ошибка при записи. Пожалуйста, попробуйте снова.');
            } finally {
                this.loading = false;
            }
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
                    return Array((firstDay + 6) % 7).fill(null);
                },
                get monthLabel() {
                    return this.currentDate.format('MMMM YYYY');
                },
                isBeforeToday(year, month, day) {
                    return dayjs().year(year).month(month).date(day).isBefore(dayjs(), 'day');
                },
                isWorkingDay(year, month, day) {
                    const dateObj = dayjs().year(year).month(month).date(day);
                    const weekday = dateObj.day();
                    return self.workingDays.includes(weekday);
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

Alpine.start();
