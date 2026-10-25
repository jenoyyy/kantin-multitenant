@extends('layouts.admin')

@section('title', 'QR Meja '.$table->code)

@section('content')
    <h2 class="text-xl font-semibold print:hidden">QR — {{ $table->label }} ({{ $table->code }})</h2>

    @if ($url)
        <p class="mt-4 p-3 bg-amber-100 text-amber-900 rounded text-sm print:hidden">
            QR baru ditampilkan <strong>satu kali</strong>. Unduh atau cetak sekarang — token mentah tidak
            disimpan dan tidak dapat ditampilkan lagi.
        </p>

        <div class="mt-4 inline-flex flex-col items-center rounded-xl border bg-white p-6 text-gray-900" data-test="qr-card">
            <div class="text-sm font-bold uppercase tracking-widest">{{ config('app.name') }}</div>
            <div class="mt-1 text-2xl font-extrabold">{{ $table->label }}</div>
            @if ($table->zone)
                <div class="text-sm text-gray-500">{{ $table->zone }}</div>
            @endif
            <div class="mt-4 w-64 [&>svg]:h-auto [&>svg]:w-full">{!! $svg !!}</div>
            <div class="mt-3 text-xs text-gray-500">Pindai untuk memesan</div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2 print:hidden">
            <a download="qr-{{ $table->code }}.svg" href="data:image/svg+xml;base64,{{ base64_encode($svg) }}"
               class="px-3 py-2 bg-gray-900 text-white rounded text-sm">Unduh QR (SVG)</a>
            <button type="button" onclick="window.print()" class="px-3 py-2 border rounded text-sm">Cetak</button>
        </div>

        <p class="mt-3 text-xs text-gray-500 print:hidden">
            SVG adalah gambar vektor: tetap tajam pada ukuran cetak berapa pun (≥300 dpi).
            URL: <span class="break-all font-mono">{{ $url }}</span>
        </p>
    @else
        <p class="mt-4 p-3 bg-gray-100 rounded text-gray-600">
            Tidak ada QR baru untuk ditampilkan. Kembali ke daftar meja dan klik Terbitkan/Regenerasi untuk memperoleh QR sekali-tampil.
        </p>
    @endif

    <div class="mt-6 print:hidden">
        <a class="text-sm font-medium underline" href="{{ route('admin.tables.index') }}">← Kembali ke daftar meja</a>
    </div>
@endsection