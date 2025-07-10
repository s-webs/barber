<div>
    <div class="container mx-auto px-4 py-[50px]">
        <x-heading message="Наши услуги и цены"></x-heading>
        <div class="flex items-center justify-center flex-wrap">
            @foreach($services as $item)
                <div
                    class="w-[350px] h-[530px] text-center mx-[15px] mt-[50px] p-[20px] shadow-[0px_0px_15px_0px_rgba(0,_0,_0,_0.1)] relative">
                    <div class="absolute left-0 top-[30px]">
                        <div
                            class="px-4 py-2 bg-[var(--color-main)] rounded-tr-[15px] rounded-br-[15px] font-semibold text-[var(--color-halftone)] text-lg">
                            {{ $item->price  }} ₸
                        </div>
                    </div>
                    <div>
                        <img src="/{{ $item->image }}" alt="{{ $item->name }}"
                             class="w-[300px] h-[300px] rounded-full object-cover mx-auto">
                    </div>
                    <div class="text-[var(--color-secondary)] mt-[20px]">
                        <h4 class="text-xl">{{ $item->name }}</h4>
                    </div>
                    <div class="text-[var(--color-dark)] text-sm mt-[20px]">
                        <p>{{ $item->description }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-[50px]">
            <a href="{{ route('services.index') }}"
               class="block max-w-[380px] mx-auto border-[4px] border-[var(--color-main)] hover:bg-[var(--color-main)] text-[var(--color-main)] hover:text-[var(--color-halftone)] text-lg font-semibold px-[30px] py-[5px] lg:py-[20px] transition-colors duration-300">Посмотреть
                все</a>
        </div>
    </div>
</div>
