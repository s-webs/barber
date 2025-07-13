@extends('layouts.booking')

@section('content')
    <div class="h-screen">
        <div class="text-[12px] text-gray-400 text-center font-semibold pt-2">
            <a href="https://s-webs.kz" target="_blank">Готовое решение для барбершопа S-WEBS</a>
        </div>

        <div class="flex justify-center items-center h-full container mx-auto px-4 mt-4">
            <div
                class="bg-[var(--color-halftone)] w-full h-[400px] flex items-center justify-center max-w-3xl rounded-[15px] p-4 flex-col overflow-hidden">
                <a href="{{ route('home.index') }}" class="text-xl bg-green-700 text-white px-[15px] py-[7px] rounded-[7px] text-center">Вернуться на главную страницу</a>
                <div class="text-xl font-semibold mt-[70px]">Ваша запись успешно создана.</div>
                <a href="https://t.me/s_barbershop_test_bot" class="text-xl mt-[70px] bg-blue-900 text-white px-[15px] py-[7px] rounded-[7px] text-center">Проверить записи вы можете в Telegram боте <i class="ph ph-telegram-logo"></i></a>
            </div>
        </div>
    </div>
@endsection
