<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" type="image/x-icon" href="/assets/images/favicon.png">
    <title>BARBERSHOP</title>
    <link
        rel="stylesheet"
        type="text/css"
        href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"
    />
    @vite(['resources/css/app.css', 'resources/js/booking.js'])
    <style>
        :root {
            --color-main: {{ $color_main->value ?? '#BB8C4B' }};
            --color-secondary: {{ $color_secondary->value ?? '#222227' }};
            --color-halftone: {{ $color_halftone->value ?? '#fcf9f5' }};
            --color-dark: {{ $color_dark->value ?? '#807f7f' }};
        }
    </style>
</head>
<body class="bg-[var(--color-secondary)] overflow-hidden h-screen">
@yield('content')
</body>
</html>
