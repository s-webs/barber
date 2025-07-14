<div>
    <div class="flex items-center flex-wrap">
        <div class="w-full lg:w-1/2 relative h-[200px] lg:h-[400px]">
            <img src="/assets/images/image-01.jpg" alt="Appointment" class="w-full h-full object-cover">
            <div class="bg-[var(--color-main)] absolute w-full h-full top-0 left-0 opacity-50"></div>
        </div>
        <div class="w-full lg:w-1/2 bg-[var(--color-secondary)] lg:h-[400px] relative">
            <img src="/assets/images/map-pattern.jpg" alt=""
                 class="w-full h-full z-[3] absolute top-0 left-0 object-cover opacity-10">
            <div class="relative z-[5] flex flex-col justify-center h-full p-[50px]">
                <div class="text-[var(--color-main)] font-semibold text-2xl lg:text-4xl text-center lg:text-end">
                    Забронируйте посещение, на удобное для вас время
                </div>
                <div class="text-end w-full mt-[70px]">
                    <a href="{{ route('booking.index') }}"
                       class="border-[3px] border-[var(--color-main)] hover:bg-[var(--color-main)] text-[var(--color-main)] hover:text-[var(--color-secondary)] px-[20px] py-[10px] text-lg font-semibold transition-colors duration-300">Перейти
                        к бронированию</a>
                </div>
            </div>
        </div>
    </div>
</div>
