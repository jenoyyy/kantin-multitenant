@extends('layouts.admin')

@section('title', 'Tenant')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Tenant &amp; Skema Komisi — {{ $canteen->name ?? 'Kantin' }}</h2>
        @can('create', App\Models\Tenant::class)
            <a href="{{ route('admin.tenants.create') }}"
               class="px-3 py-2 bg-gray-900 text-white rounded text-sm">+ Tambah tenant</a>
        @endcan
    </div>

    @if (session('status'))
        <p class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</p>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full min-w-[48rem] text-left border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2">Tenant</th>
                    <th class="p-2">Penanggung jawab</th>
                    <th class="p-2">Komisi</th>
                    <th class="p-2">Status</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tenants as $tenant)
                    @php
                        $owner = $tenant->tenantRoles->first()?->user;
                        $bank = $tenant->bankAccounts->first();
                        $commission = $tenant->commissionSchemes->first();
                        $isActive = $tenant->status === 'active';
                    @endphp
                    <tr @class(['border-t', 'text-gray-400' => ! $isActive])>
                        <td class="p-2">
                            <span class="font-medium">{{ $tenant->display_name }}</span>
                            <span class="ms-1 font-mono text-xs text-gray-500">{{ $tenant->code }}</span>
                        </td>
                        <td class="p-2">
                            {{ $owner?->name ?? '—' }}
                            @if ($bank)
                                · {{ $bank->bank_name }} ••{{ $bank->account_number_last4 }}
                            @endif
                        </td>
                        <td class="p-2 font-semibold">
                            {{ $commission ? rtrim(rtrim(number_format((float) $commission->commission_rate * 100, 2, ',', '.'), '0'), ',').'%' : '—' }}
                        </td>
                        <td class="p-2">{{ $isActive ? 'AKTIF' : 'NONAKTIF' }}</td>
                        <td class="p-2">
                            <div class="flex items-center gap-3">
                                @can('update', $tenant)
                                    <a class="text-blue-700 underline" href="{{ route('admin.tenants.edit', $tenant) }}">Kelola</a>

                                    <form method="POST" action="{{ route('admin.tenants.status', $tenant) }}"
                                          onsubmit="return confirm('{{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }} {{ $tenant->display_name }}?')">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $isActive ? 'inactive' : 'active' }}">
                                        <button type="submit" class="text-sm font-medium text-red-700 hover:underline">
                                            {{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-2 text-gray-500" colspan="5">Belum ada tenant.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="mt-3 text-xs text-gray-500">Tenant nonaktif disembunyikan dari katalog pelanggan tanpa menghapus data historis.</p>
@endsection