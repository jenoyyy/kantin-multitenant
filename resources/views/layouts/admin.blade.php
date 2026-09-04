<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dasbor Pengelola')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white min-h-screen">
    <header class="p-4 bg-gray-900 text-white flex justify-between">
        <h1 class="font-bold">Pengelola Kantin</h1>
        <nav class="space-x-4">
            <a href="#">Tenant</a>
            <a href="#">Meja & QR</a>
            <a href="#">Pencairan Dana</a>
        </nav>
    </header>

    <main class="p-6">
        @yield('content')
    </main>
</body>
</html>