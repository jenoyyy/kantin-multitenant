@extends('layouts.admin')

@section('title', 'Kelola Tenant')

@section('content')
    <h2 class="text-xl font-semibold mb-2">{{ $tenant->display_name }}</h2>

    @if (session('status'))
        <p class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <ul class="mb-4 p-3 bg-red-100 text-red-800 rounded list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @php $isActive = $tenant->status === 'active'; @endphp

    <dl class="mb-6 text-sm space-y-1">
        <div><dt class="inline font-medium">Kode:</dt> <dd class="inline">{{ $tenant->code }}</dd></div>
        <div><dt class="inline font-medium">Slug:</dt> <dd class="inline">{{ $tenant->slug }}</dd></div>
        <div>
            <dt class="inline font-medium">Status:</dt>
            <dd class="inline">{{ $isActive ? 'AKTIF' : 'NONAKTIF' }}</dd>
            @can('update', $tenant)
                <form class="inline ms-2" method="POST" action="{{ route('admin.tenants.status', $tenant) }}"
                      onsubmit="return confirm('{{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }} {{ $tenant->display_name }}?')">
                    @csrf
                    <input type="hidden" name="status" value="{{ $isActive ? 'inactive' : 'active' }}">
                    <button type="submit" class="text-red-700 underline">{{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                </form>
            @endcan
        </div>
        <div><dt class="inline font-medium">Saldo:</dt> <dd class="inline">Rp {{ number_format((int) ($tenant->balance?->amount ?? 0), 0, ',', '.') }}</dd></div>
    </dl>

    <h3 class="font-semibold mb-2">Riwayat komisi</h3>
    <ul class="list-disc list-inside text-sm" data-test="commission-history">
        @foreach ($commissions as $c)
            <li>
                {{ rtrim(rtrim(number_format((float) $c->commission_rate * 100, 2, ',', '.'), '0'), ',') }}%
                — berlaku {{ $c->valid_from->format('d M Y H:i') }}
                s/d {{ $c->valid_to ? $c->valid_to->format('d M Y H:i') : 'sekarang' }}
            </li>
        @endforeach
    </ul>

    @can('scheduleCommission', $tenant)
        <form method="POST" action="{{ route('admin.tenants.commission.store', $tenant) }}" class="mt-3 flex flex-wrap items-end gap-2">
            @csrf
            <div>
                <label class="block text-sm font-medium" for="commission_rate">Komisi baru (%)</label>
                <input class="border rounded p-2" id="commission_rate" name="commission_rate" type="number" step="0.01" min="0" max="100" required>
            </div>
            <div>
                <label class="block text-sm font-medium" for="effective_at">Berlaku sejak (hanya masa mendatang)</label>
                <input class="border rounded p-2" id="effective_at" name="effective_at" type="datetime-local" min="{{ now()->format('Y-m-d\TH:i') }}" required>
            </div>
            <button class="px-4 py-2 bg-gray-900 text-white rounded" type="submit">Jadwalkan</button>
        </form>
        <p class="mt-2 text-xs text-gray-600">Versi baru berlaku mulai tanggal tersebut. Order lama menyimpan snapshot komisi sehingga tidak berubah.</p>
    @endcan

    <h4 class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Pengelola yang mengubah komisi</h4>
    <ul class="mt-1 space-y-1 text-sm" data-test="commission-actors">
        @forelse ($commissionLogs as $log)
            <li>
                {{ $log->created_at?->format('d M Y H:i') }}
                · oleh {{ $actorNames[$log->actor_id] ?? 'sistem' }}
                · {{ $log->action }}
            </li>
        @empty
            <li class="text-gray-500">Belum ada perubahan komisi.</li>
        @endforelse
    </ul>

    <section class="mt-8">
        <h3 class="font-semibold mb-2">Rekening Bank</h3>

        <ul class="space-y-2 text-sm">
            @forelse ($accounts as $a)
                <li class="flex flex-wrap items-center gap-3">
                    <span>
                        {{ $a->bank_name }} •••• {{ $a->account_number_last4 }} a.n. {{ $a->account_holder_name }}
                        — <strong>{{ $a->status }}</strong>
                        @if ($a->is_primary) <em class="text-green-700">(utama)</em> @endif
                    </span>
                    @can('manageBank', $tenant)
                        @if ($a->status !== 'verified')
                            <form method="POST" action="{{ route('admin.tenants.bank.verify', [$tenant, $a]) }}">
                                @csrf
                                <input type="hidden" name="approve" value="1">
                                <button class="text-blue-700 underline" type="submit">Verifikasi</button>
                            </form>
                        @endif
                        @if ($a->status === 'verified' && ! $a->is_primary)
                            <form method="POST" action="{{ route('admin.tenants.bank.primary', [$tenant, $a]) }}">
                                @csrf
                                <button class="text-blue-700 underline" type="submit">Jadikan utama</button>
                            </form>
                        @endif
                    @endcan
                </li>
            @empty
                <li class="text-gray-500">Belum ada rekening.</li>
            @endforelse
        </ul>

        @can('manageBank', $tenant)
            <form method="POST" action="{{ route('admin.tenants.bank.store', $tenant) }}" class="mt-4 flex flex-wrap items-end gap-2">
                @csrf
                <div>
                    <label class="block text-sm font-medium" for="bank_name">Nama bank</label>
                    <input class="border rounded p-2" id="bank_name" name="bank_name" required>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="account_holder_name">Atas nama</label>
                    <input class="border rounded p-2" id="account_holder_name" name="account_holder_name" required>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="account_number">Nomor rekening</label>
                    <input class="border rounded p-2" id="account_number" name="account_number" inputmode="numeric" required>
                </div>
                <button class="px-4 py-2 bg-gray-900 text-white rounded" type="submit">Tambah rekening</button>
            </form>
        @endcan
    </section>

    <section class="mt-8">
        <h3 class="font-semibold mb-2">Operator / Role</h3>

        <ul class="space-y-2 text-sm">
            @forelse ($members as $m)
                <li class="flex items-center gap-3">
                    <span>{{ $m->user?->email }} — <strong>{{ $m->role }}</strong></span>
                    @can('assignRole', $tenant)
                        <form method="POST" action="{{ route('admin.tenants.roles.destroy', [$tenant, $m->user_id]) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-700 underline" type="submit">Cabut</button>
                        </form>
                    @endcan
                </li>
            @empty
                <li class="text-gray-500">Belum ada anggota.</li>
            @endforelse
        </ul>

        @can('assignRole', $tenant)
            <form method="POST" action="{{ route('admin.tenants.roles.store', $tenant) }}" class="mt-4 flex flex-wrap items-end gap-2">
                @csrf
                <div>
                    <label class="block text-sm font-medium" for="role_email">Email user</label>
                    <input class="border rounded p-2" id="role_email" name="email" type="email" required>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="role_name">Role</label>
                    <select class="border rounded p-2" id="role_name" name="role">
                        <option value="owner">owner</option>
                        <option value="staff">staff</option>
                        <option value="cashier">cashier</option>
                    </select>
                </div>
                <button class="px-4 py-2 bg-gray-900 text-white rounded" type="submit">Tugaskan</button>
            </form>
        @endcan
    </section>

    <a class="inline-block mt-6 text-gray-600 underline" href="{{ route('admin.tenants.index') }}">&larr; Kembali ke daftar</a>
@endsection