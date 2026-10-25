@extends('layouts.customer')

@section('title', $canteen->name)

@section('content')
    <div class="max-w-md mx-auto">
        @if ($table)
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-green-400">
                <span class="size-2 rounded-full bg-green-500"></span> Sesi pemesanan aktif
            </div>
            <h2 class="mt-2 text-2xl font-extrabold leading-tight">
                Selamat datang,<br>{{ $table->label }}@if ($table->zone) — {{ $table->zone }}@endif
            </h2>
            <p class="mt-1 text-sm text-gray-400">QR meja tervalidasi. Pilih tenant untuk mulai memesan — satu keranjang, satu kali bayar.</p>
        @else
            <div class="rounded-xl border border-gray-700 bg-gray-800 p-4 text-center">
                <h2 class="font-semibold">Pindai QR meja untuk memulai</h2>
                <p class="mt-1 text-sm text-gray-400">Sesi pemesanan dibuat otomatis setelah Anda memindai QR Code pada meja.</p>
            </div>
        @endif

        <ul class="mt-5 divide-y divide-gray-700 overflow-hidden rounded-xl border border-gray-700 bg-gray-800" data-test="tenant-list">
            @foreach ($tenants as $tenant)
                @php $open = $hours->isOpen($tenant); @endphp
                <li @class(['flex min-h-14 items-center justify-between px-4 py-3', 'opacity-60' => ! $open])>
                    <div>
                        <div class="font-semibold">{{ $tenant->display_name }}</div>
                        <div class="text-xs text-gray-400">
                            @if ($open)
                                Buka{{ $hours->todayLabel($tenant) ? ' · '.$hours->todayLabel($tenant) : '' }}
                            @else
                                Tutup{{ $hours->opensAtToday($tenant) ? ' · Buka pukul '.$hours->opensAtToday($tenant) : ' hari ini' }}
                            @endif
                        </div>
                    </div>
                    @unless ($open)
                        <span class="rounded border border-gray-500 px-2 py-0.5 text-[10px] font-semibold text-gray-300">TUTUP</span>
                    @endunless
                </li>
            @endforeach
        </ul>

        @if ($table)
            <a href="{{ route('customer.home', ['canteen' => $canteen->code]) }}"
               class="mt-6 flex min-h-12 w-full items-center justify-center rounded-lg bg-red-600 font-semibold text-white hover:bg-red-700">Lihat semua menu →</a>
        @endif
    </div>
@endsection