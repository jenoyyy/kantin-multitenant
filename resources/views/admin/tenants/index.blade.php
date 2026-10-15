@extends('layouts.admin')

@section('title', 'Tenant')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Tenant {{ $canteen->name ?? 'Kantin' }}</h2>
        @can('create', App\Models\Tenant::class)
            <a href="{{ route('admin.tenants.create') }}"
               class="px-3 py-2 bg-gray-900 text-white rounded text-sm">+ Tambah tenant</a>
        @endcan
    </div>

    @if (session('status'))
        <p class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</p>
    @endif

    <table class="w-full text-left border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Kode</th>
                <th class="p-2">Nama</th>
                <th class="p-2">Status</th>
                <th class="p-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tenants as $tenant)
                <tr class="border-t">
                    <td class="p-2">{{ $tenant->code }}</td>
                    <td class="p-2">{{ $tenant->display_name }}</td>
                    <td class="p-2">{{ $tenant->status }}</td>
                    <td class="p-2">
                        @can('update', $tenant)
                            <a class="text-blue-700 underline" href="{{ route('admin.tenants.edit', $tenant) }}">Kelola</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td class="p-2 text-gray-500" colspan="4">Belum ada tenant.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection