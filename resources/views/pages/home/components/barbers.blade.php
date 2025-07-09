<div>
    <div class="container mx-auto px-4 py-[50px]">
        <x-heading message="Мастера"></x-heading>
        <div class="flex items-center justify-center flex-wrap">
            @foreach($barbers as $item)
                <div
                    class="w-[350px] h-[450px] text-center mx-[15px] mt-[50px] p-[20px] shadow-[0px_0px_15px_0px_rgba(0,_0,_0,_0.1)] relative border border-[var(--color-main)] rounded-[8px]">
                    <div>
                        <img src="/{{ $item->photo }}" alt="{{ $item->name }}"
                             class="w-[300px] h-[300px] rounded-full object-cover mx-auto">
                    </div>
                    <div class="text-[var(--color-secondary)] mt-[20px]">
                        <h4 class="text-xl">{{ $item->name }}</h4>
                    </div>
                    <div>
                        <div class="mt-[10px]"><img src="/assets/images/heading-line.png" class="mx-auto"></div>
                    </div>
                    <div class="mt-[10px]">
                        @foreach($item->socials as $social)
                            <a href="{{ $social['url'] }}" class="text-xl hover:text-[var(--color-main)] mx-[3.5px]" target="_blank">
                                @switch($social['type'])
                                    @case('instagram')
                                        <i class="ph ph-instagram-logo"></i>
                                        @break

                                    @case('facebook')
                                        <i class="ph ph-facebook-logo"></i>
                                        @break

                                    @case('tiktok')
                                        <i class="ph ph-tiktok-logo"></i>
                                        @break

                                    @case('youtube')
                                        <i class="ph ph-youtube-logo"></i>
                                        @break
                                @endswitch
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
