<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Yönetici Girişi' }}</title>
    @livewireStyles
    <link rel="stylesheet" href="{{ asset('css/admin-login.css') }}?v=3">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    {{ $slot }}
    @livewireScripts
    <script src="{{ asset('js/admin-login.js') }}"></script>
</body>
</html>
