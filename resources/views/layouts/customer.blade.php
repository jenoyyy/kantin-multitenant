<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kantin Multi-Tenant')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <header class="p-4 border-b border-gray-700">
        <h1 class="text-lg font-bold">Kantin Multi-Tenant</h1>
    </header>

    <main class="p-4">
        @yield('content')
    </main>
</body>
</html>