@extends('layouts.customer')

@section('title', $canteen->name)

@section('content')
    <div class="max-w-md mx-auto">
        <div class="rounded-xl border border-amber-700 bg-amber-950/40 p-4" data-test="canteen-closed">
            <h2 class="text-lg font-bold">Kantin sedang tutup</h2>
            <p class="mt-1 text-sm text-gray-300">QR {{ $table->label }} valid, tetapi pemesanan dibuka kembali saat tenant beroperasi. Jam buka hari ini:</p>
        </div>
        <ul class="mt-4 divide-y divide-gray-700 rounded-xl border border-gray-700 bg-gray-800">
            @foreach ($tenants as $tenant)
                <li class="flex min-h-12 items-center justify-between px-4 py-3 text-sm">
                    <span class="font-medium">{{ $tenant->display_name }}</span>
                    <span class="text-gray-400">{{ $hours->todayLabel($tenant) ?? 'Libur hari ini' }}</span>
                </li>
            @endforeach
        </ul>
    </div>
@endsection