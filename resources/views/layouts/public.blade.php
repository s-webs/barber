<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" type="image/x-icon" href="/assets/images/favicon.png">
    <title>BARBERSHOP</title>
    <link
        rel="stylesheet"
        type="text/css"
        href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"
    />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-main: {{ $color_main->value ?? '#BB8C4B' }};
            --color-secondary: {{ $color_secondary->value ?? '#222227' }};
            --color-halftone: {{ $color_halftone->value ?? '#fcf9f5' }};
            --color-dark: {{ $color_dark->value ?? '#807f7f' }};
        }
    </style>
</head>
<body x-data="menuState" class="bg-[var(--color-halftone)]">
<div x-data="stickyHeader">
    <header
        class="fixed top-0 left-0 w-full bg-[var(--color-secondary)] z-[8] transition-all duration-300"
        :class="{ 'shadow-md': isSticky, 'py-2': isSticky, 'py-4': !isSticky }"
    >
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="/">
                        <img src="/assets/images/logo-demo.png" alt="">
                    </a>
{{--                    <div class="hidden lg:block ml-[20px] px-[7px] border-l-2 border-l-[var(--color-main)]">--}}
{{--                        <select name="" id=""--}}
{{--                                class="text-[var(--color-halftone)] hover:text-[var(--color-main)] font-semibold transition-colors duration-300">--}}
{{--                            <option value="">KZ</option>--}}
{{--                            <option value="" selected>RU</option>--}}
{{--                            <option value="">EN</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}
                </div>
                <div class="items-center hidden lg:flex">
                    <ul class="flex items-center text-md">
{{--                        <li>--}}
{{--                            <a href="{{ route('home.index') }}"--}}
{{--                               class="text-[var(--color-halftone)] hover:text-[var(--color-main)] font-semibold transition-colors duration-300 mr-[20px]">Главная</a>--}}
{{--                        </li>--}}
{{--                        <li>--}}
{{--                            <a href="{{ route('about.index') }}"--}}
{{--                               class="text-[var(--color-halftone)] hover:text-[var(--color-main)] font-semibold transition-colors duration-300 mr-[20px]">О--}}
{{--                                нас</a>--}}
{{--                        </li>--}}
{{--                        <li>--}}
{{--                            <a href="{{ route('services.index') }}"--}}
{{--                               class="text-[var(--color-halftone)] hover:text-[var(--color-main)] font-semibold transition-colors duration-300 mr-[20px]">Услуги</a>--}}
{{--                        </li>--}}
{{--                        <li>--}}
{{--                            <a href="{{ route('contacts.index') }}"--}}
{{--                               class="text-[var(--color-halftone)] hover:text-[var(--color-main)] font-semibold transition-colors duration-300 mr-[50px]">Контакты</a>--}}
{{--                        </li>--}}
                        <li class="">
                            <a href="{{ route('booking.index') }}"
                               class="text-[var(--color-main)] hover:text-[var(--color-halftone)] font-semibold hover:bg-[var(--color-main)] transition-colors duration-300 border-2 border-[var(--color-main)] px-[20px] py-[6px]">Записаться</a>
                        </li>
                    </ul>
                </div>
{{--                <div class="block lg:hidden">--}}
{{--                    <i @click="mobileMenuOpen = true" class="ph ph-list text-[var(--color-halftone)] text-3xl"></i>--}}
{{--                </div>--}}
            </div>
        </div>
    </header>
</div>
{{--MOBILE MENU--}}
{{--<div--}}
{{--    x-show="mobileMenuOpen"--}}
{{--    x-transition:enter="transition ease-out duration-300"--}}
{{--    x-transition:enter-start="opacity-0 -translate-x-full"--}}
{{--    x-transition:enter-end="opacity-100 translate-x-0"--}}
{{--    x-transition:leave="transition ease-in duration-200"--}}
{{--    x-transition:leave-start="opacity-100 translate-x-0"--}}
{{--    x-transition:leave-end="opacity-0 -translate-x-full"--}}
{{--    @click.outside="mobileMenuOpen = false"--}}
{{--    class="fixed z-[10] top-0 left-0 h-screen w-[80%] bg-[var(--color-secondary)]"--}}
{{--    style="display: none;"--}}
{{-->--}}
{{--    <div class="p-4">--}}
{{--        <div class="flex justify-between items-center border-b-2 border-b-[var(--color-main)] pb-[15px]">--}}
{{--            <div class="px-[7px] border-2 border-[var(--color-main)]">--}}
{{--                <select name="" id=""--}}
{{--                        class="text-[var(--color-halftone)] hover:text-[var(--color-main)] font-semibold transition-colors duration-300">--}}
{{--                    <option value="">KZ</option>--}}
{{--                    <option value="" selected>RU</option>--}}
{{--                    <option value="">EN</option>--}}
{{--                </select>--}}
{{--            </div>--}}
{{--            <div class="text-[var(--color-halftone)]">--}}
{{--                <i @click="mobileMenuOpen = false"--}}
{{--                   class="ph ph-x-circle text-4xl cursor-pointer text-[var(--color-halftone)] hover:text-[var(--color-main)] transition-colors duration-300"></i>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="mt-[15px] text-center">--}}
{{--            <a href="{{ route('booking.index') }}"--}}
{{--               class="block border-[2px] border-[var(--color-main)] px-[10px] py-[5px] text-[var(--color-secondary)] hover:text-[var(--color-main)] bg-[var(--color-main)] hover:bg-[var(--color-secondary)] transition-colors duration-300 mb-[20px]">Записаться</a>--}}
{{--        </div>--}}
{{--        <ul class="">--}}
{{--            <li>--}}
{{--                <a href="{{ route('home.index') }}"--}}
{{--                   class="block border-[2px] border-[var(--color-main)] px-[10px] py-[5px] text-[var(--color-main)] hover:bg-[var(--color-main)] hover:text-[var(--color-secondary)] transition-colors duration-300 mb-[20px]">Главная</a>--}}
{{--            </li>--}}
{{--            <li>--}}
{{--                <a href="{{ route('about.index') }}"--}}
{{--                   class="block border-[2px] border-[var(--color-main)] px-[10px] py-[5px] text-[var(--color-main)] hover:bg-[var(--color-main)] hover:text-[var(--color-secondary)] transition-colors duration-300 mb-[20px]">О--}}
{{--                    нас</a>--}}
{{--            </li>--}}
{{--            <li>--}}
{{--                <a href="{{ route('services.index') }}"--}}
{{--                   class="block border-[2px] border-[var(--color-main)] px-[10px] py-[5px] text-[var(--color-main)] hover:bg-[var(--color-main)] hover:text-[var(--color-secondary)] transition-colors duration-300 mb-[20px]">Услуги</a>--}}
{{--            </li>--}}
{{--            <li>--}}
{{--                <a href="{{ route('contacts.index') }}"--}}
{{--                   class="block border-[2px] border-[var(--color-main)] px-[10px] py-[5px] text-[var(--color-main)] hover:bg-[var(--color-main)] hover:text-[var(--color-secondary)] transition-colors duration-300">Контакты</a>--}}
{{--            </li>--}}
{{--        </ul>--}}
{{--    </div>--}}
{{--</div>--}}
@yield('content')
<footer class="bg-[var(--color-secondary)] py-[50px]">
    <div class="container mx-auto p-4">
        <div class="flex items-center justify-between flex-wrap lg:flex-nowrap">
            <div class="mx-auto lg:mx-0">
                <a href="/" class="">
                    <img src="/assets/images/logo-demo.png" alt="" class="w-[250px]">
                </a>
            </div>
            <div class="mt-[30px] lg:mt-0 text-center lg:text-start">
                <div
                    class="text-xl text-[var(--color-main)] font-semibold border-b-[3px] border-b-[var(--color-main)] pb-[10px]">
                    Адреса и время работы
                </div>
                <div class="mt-[20px] text-lg text-[var(--color-halftone)]">Улица Толе би, 164 Алмалинский район,
                    Алматы,
                </div>
            </div>
            <div class="text-xl font-semibold text-[var(--color-main)] mt-[30px] lg:mt-0  mx-auto lg:mx-0">
                <a href="##" target="_blank" class="flex items-center mb-[20px]">
                    <i class="ph ph-instagram-logo"></i>
                    <span class="ml-2">Instagram</span>
                </a>
                <a href="##" target="_blank" class="flex items-center mb-[20px]">
                    <i class="ph ph-tiktok-logo"></i>
                    <span class="ml-2">TikTok</span>
                </a>
                <a href="##" target="_blank" class="flex items-center">
                    <i class="ph ph-youtube-logo"></i>
                    <span class="ml-2">YouTube</span>
                </a>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
