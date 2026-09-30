@extends('layouts.tenant')

@section('title', 'Dashboard')

@section('content')
    <div class="flex items-center gap-3">
        <h1 class="text-xl font-semibold">
            Dashboard Tenant
        </h1>
    </div>

    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
        Tenant:
        <span class="font-semibold">
            {{ $tenant->name }}
        </span>
    </p>

    <div class="mt-6 rounded-lg border border-zinc-200 p-6 dark:border-zinc-700">
        <h2 class="text-lg font-semibold">
            Selamat datang
        </h2>

        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
            Dashboard tenant berhasil diakses.
        </p>

        <p class="mt-2 text-sm">
            Status tenant:
            <span class="font-semibold">
                {{ $tenant->status }}
            </span>
        </p>
    </div>
@endsection