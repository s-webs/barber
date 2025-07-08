<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BARBERSHOP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-main: #BB8C4B;
            --color-secondary: #222227;
            --color-halftone: #fcf9f5;
            --color-gray: #ffffff4d;
        }
    </style>
</head>
<body>
<header class="bg-[var(--color-secondary)]">
    <div class="container mx-auto px-4 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="##">
                    <img src="/assets/images/logo-demo.png" alt="">
                </a>
                <div class="ml-[20px] px-[7px] border-l-2 border-l-[var(--color-main)]">
                    <select name="" id=""
                            class="text-[var(--color-halftone)] hover:text-[var(--color-main)] font-semibold transition-colors duration-300">
                        <option value="">KZ</option>
                        <option value="" selected>RU</option>
                        <option value="">EN</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center">
                <ul class="flex items-center text-md">
                    <li>
                        <a href="##"
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
        </div>
    </div>
</header>
@yield('content')
<footer>
    FOOTER
</footer>
</body>
</html>
