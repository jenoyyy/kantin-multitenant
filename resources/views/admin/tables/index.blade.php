@extends('layouts.admin')

@section('title', 'Meja & QR')

@section('content')
    <div class="flex items-baseline gap-2 mb-4">
        <h2 class="text-xl font-semibold">Meja &amp; QR Code — {{ $canteen->name }}</h2>
        <span class="text-sm text-gray-500">· {{ $tables->count() }} meja, {{ $tables->where('status', 'active')->count() }} aktif</span>
    </div>

    @if (session('status'))
        <p class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
            <ul class="list-disc ps-4">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @can('create', App\Models\DiningTable::class)
        <form method="POST" action="{{ route('admin.tables.store') }}" class="mb-6 flex flex-wrap items-end gap-3">
            @csrf
            <label class="text-sm">Kode (unik per kantin)
                <input name="code" value="{{ old('code') }}" required maxlength="30" class="block border rounded px-2 py-1">
            </label>
            <label class="text-sm">Label
                <input name="label" value="{{ old('label') }}" required maxlength="60" class="block border rounded px-2 py-1">
            </label>
            <label class="text-sm">Zona (opsional)
                <input name="zone" value="{{ old('zone') }}" maxlength="60" class="block border rounded px-2 py-1">
            </label>
            <button type="submit" class="px-3 py-2 bg-gray-900 text-white rounded text-sm">+ Tambah meja</button>
        </form>
    @endcan

    <div class="overflow-x-auto">
        <table class="w-full min-w-[40rem] text-left border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2">Meja</th>
                    <th class="p-2">Zona</th>
                    <th class="p-2">Status</th>
                    <th class="p-2">QR aktif</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tables as $table)
                    <tr @class(['border-t', 'text-gray-400' => $table->status !== 'active'])>
                        <td class="p-2">
                            <span class="font-medium">{{ $table->label }}</span>
                            <span class="ms-1 font-mono text-xs text-gray-500">{{ $table->code }}</span>
                        </td>
                        <td class="p-2">{{ $table->zone ?? '—' }}</td>
                        <td class="p-2">{{ $table->status === 'active' ? 'AKTIF' : 'NONAKTIF' }}</td>
                        <td class="p-2 text-sm">
                            @if ($table->activeToken)
                                sejak {{ $table->activeToken->issued_at?->timezone(config('app.display_timezone', 'Asia/Jakarta'))->format('j M Y H:i') }}
                            @else
                                belum ada
                            @endif
                        </td>
                        <td class="p-2 text-right">
                            @can('rotateQr', $table)
                                <form method="POST" action="{{ route('admin.tables.rotate', $table) }}"
                                      @if ($table->activeToken) onsubmit="return confirm('Regenerasi QR {{ $table->label }}? QR cetakan lama langsung tidak berlaku.')" @endif>
                                    @csrf
                                    <button type="submit" class="px-3 py-1 border rounded text-sm">
                                        {{ $table->activeToken ? 'Rotate QR' : 'Terbitkan QR' }}
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-2 text-gray-500" colspan="5">Belum ada meja.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="mt-3 text-xs text-gray-500">Token QR berupa token acak ≥128 bit; URL tidak memuat ID meja maupun kantin.</p>
@endsection