<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dasbor Tenant')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex">
    <aside class="w-56 bg-gray-800 text-white p-4">
        <h2 class="font-bold mb-4">Menu Tenant</h2>
        <nav class="space-y-2">
            <a href="#" class="block">Dashboard</a>
            <a href="#" class="block">Menu & Stok</a>
            <a href="#" class="block">Antrean Dapur</a>
        </nav>
    </aside>

    <main class="flex-1 p-6">
        @yield('content')
    </main>
</body>
</html>