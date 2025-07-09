<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BARBERSHOP</title>
    <link
        rel="stylesheet"
        type="text/css"
        href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"
    />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-main: #BB8C4B;
            --color-secondary: #222227;
            --color-halftone: #fcf9f5;
            --color-dark: #807f7f;
        }
    </style>
</head>
<body x-data="menuState">
<header class="bg-[var(--color-secondary)]">
    <div class="container mx-auto px-4 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="/">
                    <img src="/assets/images/logo-demo.png" alt="">
                </a>
                <div class="hidden lg:block ml-[20px] px-[7px] border-l-2 border-l-[var(--color-main)]">
                    <select name="" id=""
                            class="text-[var(--color-halftone)] hover:text-[var(--color-main)] font-semibold transition-colors duration-300">
                        <option value="">KZ</option>
                        <option value="" selected>RU</option>
                        <option value="">EN</option>
                    </select>
                </div>
            </div>
            <div class="items-center hidden lg:flex">
                <ul class="flex items-center text-md">
                    <li>
                        <a href="/"
                           class="text-[var(--color-halftone)] hover:text-[var(--color-main)] font-semibold transition-colors duration-300 mr-[20px]">Главная</a>
                    </li>
                    <li>
                        <a href="##"
                           class="text-[var(--color-halftone)] hover:text-[var(--color-main)] font-semibold transition-colors duration-300 mr-[20px]">О
                            нас</a>
                    </li>
                    <li>
                        <a href="##"
                           class="text-[var(--color-halftone)] hover:text-[var(--color-main)] font-semibold transition-colors duration-300 mr-[20px]">Прайс</a>
                    </li>
                    <li>
                        <a href="##"
                           class="text-[var(--color-halftone)] hover:text-[var(--color-main)] font-semibold transition-colors duration-300 mr-[50px]">Контакты</a>
                    </li>
                    <li class="">
                        <a href="##"
                           class="text-[var(--color-main)] hover:text-[var(--color-halftone)] font-semibold hover:bg-[var(--color-main)] transition-colors duration-300 border-2 border-[var(--color-main)] px-[20px] py-[6px]">Записаться</a>
                    </li>
                </ul>
            </div>
            <div class="block lg:hidden">
                <i @click="mobileMenuOpen = true" class="ph ph-list text-[var(--color-halftone)] text-3xl"></i>
            </div>
        </div>
    </div>
</header>
{{--MOBILE MENU--}}
<div
    x-show="mobileMenuOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-x-full"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 -translate-x-full"
    @click.outside="mobileMenuOpen = false"
    class="fixed z-[10] top-0 left-0 h-screen w-[80%] bg-[var(--color-secondary)]"
    style="display: none;"
>
    <div class="p-4">
        <div class="flex justify-between items-center border-b-2 border-b-[var(--color-main)] pb-[15px]">
            <div class="px-[7px] border-2 border-[var(--color-main)]">
                <select name="" id=""
                        class="text-[var(--color-halftone)] hover:text-[var(--color-main)] font-semibold transition-colors duration-300">
                    <option value="">KZ</option>
                    <option value="" selected>RU</option>
                    <option value="">EN</option>
                </select>
            </div>
            <div class="text-[var(--color-halftone)]">
                <i @click="mobileMenuOpen = false"
                   class="ph ph-x-circle text-4xl cursor-pointer text-[var(--color-halftone)] hover:text-[var(--color-main)] transition-colors duration-300"></i>
            </div>
        </div>
        <div class="mt-[15px] text-center">
            <a href="/"
               class="block border-[2px] border-[var(--color-main)] px-[10px] py-[5px] text-[var(--color-secondary)] hover:text-[var(--color-main)] bg-[var(--color-main)] hover:bg-[var(--color-secondary)] transition-colors duration-300 mb-[20px]">Записаться</a>
        </div>
        <ul class="">
            <li>
                <a href="/"
                   class="block border-[2px] border-[var(--color-main)] px-[10px] py-[5px] text-[var(--color-main)] hover:bg-[var(--color-main)] hover:text-[var(--color-secondary)] transition-colors duration-300 mb-[20px]">Главная</a>
            </li>
            <li>
                <a href="/"
                   class="block border-[2px] border-[var(--color-main)] px-[10px] py-[5px] text-[var(--color-main)] hover:bg-[var(--color-main)] hover:text-[var(--color-secondary)] transition-colors duration-300 mb-[20px]">О
                    нас</a>
            </li>
            <li>
                <a href="/"
                   class="block border-[2px] border-[var(--color-main)] px-[10px] py-[5px] text-[var(--color-main)] hover:bg-[var(--color-main)] hover:text-[var(--color-secondary)] transition-colors duration-300 mb-[20px]">Прайс</a>
            </li>
            <li>
                <a href="/"
                   class="block border-[2px] border-[var(--color-main)] px-[10px] py-[5px] text-[var(--color-main)] hover:bg-[var(--color-main)] hover:text-[var(--color-secondary)] transition-colors duration-300">Контакты</a>
            </li>
        </ul>
    </div>
</div>
@yield('content')
<footer>
    FOOTER
</footer>
</body>
</html>
