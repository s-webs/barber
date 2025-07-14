<div x-data="servicesComponent({{ $services->toJson() }})" x-init="init()">
    <div class="container mx-auto px-4 py-[50px]">
        <x-heading message="Наши услуги и цены"></x-heading>

        <div class="flex items-center justify-center flex-wrap">
            <template x-for="item in items" :key="item.id">
                <div
                    class="w-[350px] h-[530px] text-center mx-[15px] mt-[50px] p-[20px] shadow-[0px_0px_15px_0px_rgba(0,_0,_0,_0.1)] relative">
                    <div class="absolute left-0 top-[30px]">
                        <div
                            class="px-4 py-2 bg-[var(--color-main)] rounded-tr-[15px] rounded-br-[15px] font-semibold text-[var(--color-halftone)] text-lg"
                            x-text="item.price + ' ₸'">
                        </div>
                    </div>
                    <div>
                        <img :src="'/' + item.image" :alt="item.name"
                             class="w-[300px] h-[300px] rounded-full object-cover mx-auto">
                    </div>
                    <div class="text-[var(--color-secondary)] mt-[20px]">
                        <h4 class="text-xl" x-text="item.name"></h4>
                    </div>
                    <div class="text-[var(--color-dark)] text-sm mt-[20px]">
                        <p x-text="item.description"></p>
                    </div>
                </div>
            </template>
        </div>

        <div class="text-center mt-[50px]">
            <div
                class="block cursor-pointer max-w-[380px] mx-auto border-[4px] border-[var(--color-main)] hover:bg-[var(--color-main)] text-[var(--color-main)] hover:text-[var(--color-halftone)] text-lg font-semibold px-[30px] py-[5px] lg:py-[20px] transition-colors duration-300"
                @click="loadMore()"
                :class="{ 'opacity-50 pointer-events-none': loading }"
            >
                <template x-if="loading">
                    <svg class="animate-spin h-6 w-6 mx-auto text-[var(--color-main)]" xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </template>
                <template x-if="!loading">
                    <span>Загрузить еще</span>
                </template>
            </div>
        </div>

    </div>
</div>

<script>
    function servicesComponent(initialItems = []) {
        return {
            items: initialItems,
            offset: initialItems.length,
            limit: 3,
            loading: false,

            init() {
                // начальная загрузка уже есть
            },

            async loadMore() {
                if (this.loading) return;

                this.loading = true;

                const response = await fetch(`/services/load?offset=${this.offset}`);
                const newItems = await response.json();

                if (newItems.length > 0) {
                    newItems.forEach(item => {
                        if (!this.items.find(existing => existing.id === item.id)) {
                            this.items.push(item);
                        }
                    });
                    this.offset += newItems.length;
                }

                this.loading = false;
            }
        }
    }
</script>


